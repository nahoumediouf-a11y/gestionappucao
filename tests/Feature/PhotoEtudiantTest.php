<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
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

    private function maj(User $u, array $extra): TestResponse
    {
        return $this->actingAs($u)->put('/mon-compte', array_merge(['nom' => 'Faye', 'prenom' => 'Aminata'], $extra));
    }

    public function test_upload_valide(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->maj($u, ['photo' => UploadedFile::fake()->image('portrait.jpg', 400, 400)])->assertRedirect();

        $u->refresh();
        $this->assertNotNull($u->photo);
        Storage::disk('public')->assertExists($u->photo);
        $this->assertStringEndsWith('.jpg', $u->photo);
    }

    public function test_fichier_non_image_refuse(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->maj($u, ['photo' => UploadedFile::fake()->create('document.pdf', 100, 'application/pdf')])
            ->assertSessionHasErrors('photo');

        $this->assertNull($u->fresh()->photo);
    }

    public function test_mime_usurpe_refuse(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        // Un fichier non-image avec une extension .jpg ne passe pas la validation image.
        $this->maj($u, ['photo' => UploadedFile::fake()->create('evil.jpg', 50, 'text/plain')])
            ->assertSessionHasErrors('photo');

        $this->assertNull($u->fresh()->photo);
    }

    public function test_toute_image_acceptee_petite_et_gif(): void
    {
        Storage::fake('public');

        // Petite image : acceptée.
        $u1 = $this->etudiant('etu_p');
        $this->maj($u1, ['photo' => UploadedFile::fake()->image('petite.jpg', 40, 40)])->assertRedirect();
        $this->assertNotNull($u1->fresh()->photo);

        // GIF : accepté et stocké avec l'extension .gif.
        $u2 = $this->etudiant('etu_g');
        $this->maj($u2, ['photo' => UploadedFile::fake()->image('anim.gif', 200, 200)])->assertRedirect();
        $this->assertStringEndsWith('.gif', $u2->fresh()->photo);
    }

    public function test_remplacement_supprime_lancienne_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();

        $this->maj($u, ['photo' => UploadedFile::fake()->image('p1.jpg', 200, 200)]);
        $ancienne = $u->fresh()->photo;

        $this->maj($u, ['photo' => UploadedFile::fake()->image('p2.jpg', 200, 200)]);

        Storage::disk('public')->assertMissing($ancienne);
        Storage::disk('public')->assertExists($u->fresh()->photo);
    }

    public function test_retirer_la_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();
        $this->maj($u, ['photo' => UploadedFile::fake()->image('p.jpg', 200, 200)]);
        $chemin = $u->fresh()->photo;

        $this->maj($u, ['supprimer_photo' => '1']);

        $this->assertNull($u->fresh()->photo);
        Storage::disk('public')->assertMissing($chemin);
    }

    public function test_suppression_utilisateur_supprime_la_photo(): void
    {
        Storage::fake('public');
        $u = $this->etudiant();
        $this->maj($u, ['photo' => UploadedFile::fake()->image('p.jpg', 200, 200)]);
        $chemin = $u->fresh()->photo;

        $u->etudiant->delete();
        $u->delete();

        Storage::disk('public')->assertMissing($chemin);
    }
}
