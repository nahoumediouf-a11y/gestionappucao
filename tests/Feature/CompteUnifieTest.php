<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompteUnifieTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(Role $role, string $login, array $extra = []): User
    {
        return User::create(array_merge([
            'nom' => 'Test', 'prenom' => 'Utilisateur', 'login' => $login,
            'email' => $login.'@ex.com', 'password' => 'password', 'role' => $role, 'statut' => 'actif',
        ], $extra));
    }

    public function test_tout_role_accede_a_mon_compte(): void
    {
        foreach (['comptable' => Role::AgentComptable, 'prof' => Role::Professeur] as $login => $role) {
            $user = $this->makeUser($role, $login);
            $this->actingAs($user)->get('/mon-compte')
                ->assertStatus(200)
                ->assertSee('Mon compte')
                ->assertSee($user->nom_complet);
        }
    }

    public function test_un_utilisateur_met_a_jour_ses_infos(): void
    {
        $user = $this->makeUser(Role::Professeur, 'prof');

        $this->actingAs($user)->put('/mon-compte', [
            'nom' => 'Nouveau', 'prenom' => 'Nom', 'email' => 'nouveau@ex.com', 'telephone' => '770000000',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $user->id, 'nom' => 'Nouveau', 'email' => 'nouveau@ex.com']);
    }

    public function test_email_unique_sauf_soi_meme(): void
    {
        $autre = $this->makeUser(Role::Professeur, 'autre', ['email' => 'pris@ex.com']);
        $user = $this->makeUser(Role::Professeur, 'prof');

        $this->actingAs($user)->put('/mon-compte', [
            'nom' => 'X', 'prenom' => 'Y', 'email' => 'pris@ex.com',
        ])->assertSessionHasErrors('email');
    }

    public function test_admin_cree_un_etudiant_user_et_fiche_lies(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'admin');

        $this->actingAs($admin)->post('/admin/utilisateurs', [
            'nom' => 'Sow', 'prenom' => 'Awa', 'login' => 'etu_new',
            'email' => 'etunew@ex.com', 'password' => 'password',
            'role' => Role::Etudiant->value, 'statut' => 'actif',
            'matricule' => '1000700', 'niveau' => 'L3', 'filiere' => 'Informatique',
        ])->assertRedirect(route('admin.utilisateurs.index'));

        $user = User::where('login', 'etu_new')->first();
        $this->assertNotNull($user);
        $this->assertNotNull($user->etudiant);
        $this->assertSame('1000700', $user->etudiant->matricule);
    }

    public function test_admin_filtre_par_role(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'admin');
        $this->makeUser(Role::Professeur, 'prof');

        $this->actingAs($admin)->get('/admin/utilisateurs?role='.Role::Professeur->value)
            ->assertStatus(200)
            ->assertSee('prof');
    }

    public function test_un_non_admin_nacceede_pas_a_la_gestion(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');

        $this->actingAs($prof)->get('/admin/utilisateurs')->assertStatus(403);
    }
}
