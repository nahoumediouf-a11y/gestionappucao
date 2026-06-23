<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PhotoEtudiantTest extends TestCase
{
    use RefreshDatabase;

    private function etudiant(string $login = 'etu'): User
    {
        $u = User::create([
            'nom' => 'Faye', 'prenom' => 'Aminata', 'login' => $login, 'email' => $login.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);
        Etudiant::create(['user_id' => $u->id, 'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 0]);

        return $u;
    }

    public function test_etudiant_televerse_une_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->actingAs($u)->put('/mon-compte', [
            'nom' => 'Faye', 'prenom' => 'Aminata',
            'photo' => UploadedFile::fake()->image('portrait.jpg', 400, 400),
        ])->assertRedirect();

        $u->refresh();
        $this->assertNotNull($u->photo);
        Storage::disk('public')->assertExists($u->photo);
    }

    public function test_fichier_non_image_refuse(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->actingAs($u)->put('/mon-compte', [
            'nom' => 'Faye', 'prenom' => 'Aminata',
            'photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('photo');

        $this->assertNull($u->fresh()->photo);
    }

    public function test_remplacement_supprime_lancienne_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->actingAs($u)->put('/mon-compte', ['nom' => 'Faye', 'prenom' => 'Aminata',
            'photo' => UploadedFile::fake()->image('p1.jpg')]);
        $ancienne = $u->fresh()->photo;

        $this->actingAs($u)->put('/mon-compte', ['nom' => 'Faye', 'prenom' => 'Aminata',
            'photo' => UploadedFile::fake()->image('p2.jpg')]);

        Storage::disk('public')->assertMissing($ancienne);
        Storage::disk('public')->assertExists($u->fresh()->photo);
    }

    public function test_retirer_la_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();
        $this->actingAs($u)->put('/mon-compte', ['nom' => 'Faye', 'prenom' => 'Aminata',
            'photo' => UploadedFile::fake()->image('p.jpg')]);

        $this->actingAs($u)->put('/mon-compte', ['nom' => 'Faye', 'prenom' => 'Aminata', 'supprimer_photo' => '1']);

        $this->assertNull($u->fresh()->photo);
    }

    public function test_identite_affiche_la_photo_ou_les_initiales(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        // Sans photo : initiales (admin voit la liste).
        $admin = User::create(['nom' => 'D', 'prenom' => 'A', 'login' => 'admin', 'password' => 'password', 'role' => Role::Administrateur, 'statut' => 'actif']);
        $this->actingAs($admin)->get('/admin/utilisateurs')->assertStatus(200)->assertSee('AF'); // initiales Aminata Faye

        // Avec photo : balise img présente sur Mon compte.
        $this->actingAs($u)->put('/mon-compte', ['nom' => 'Faye', 'prenom' => 'Aminata', 'photo' => UploadedFile::fake()->image('p.jpg')]);
        $this->actingAs($u)->get('/mon-compte')->assertStatus(200)->assertSee('<img', false);
    }
}
