<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RechercheGlobaleTest extends TestCase
{
    use RefreshDatabase;

    private function user(Role $role, string $login): User
    {
        return User::create([
            'nom' => ucfirst($login), 'prenom' => 'Test', 'login' => $login,
            'email' => $login.'@ex.com', 'password' => 'password', 'role' => $role, 'statut' => 'actif',
        ]);
    }

    private function etudiant(string $login, string $nom, string $filiere = 'Informatique', string $niveau = 'L3'): Etudiant
    {
        $u = User::create([
            'nom' => $nom, 'prenom' => 'E', 'login' => $login, 'email' => $login.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);

        return Etudiant::create([
            'user_id' => $u->id, 'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => $niveau, 'filiere' => $filiere, 'solde' => 0,
        ]);
    }

    public function test_personnel_trouve_un_etudiant(): void
    {
        $comptable = $this->user(Role::AgentComptable, 'comptable');
        $this->etudiant('etu1', 'Tournesol');

        $this->actingAs($comptable)->get('/recherche?q=Tournesol')
            ->assertStatus(200)
            ->assertSee('Tournesol');
    }

    public function test_etudiant_na_pas_acces_a_la_recherche_globale(): void
    {
        $u = User::create([
            'nom' => 'X', 'prenom' => 'Y', 'login' => 'etu', 'email' => 'etu@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);
        Etudiant::create(['user_id' => $u->id, 'matricule' => '1000001', 'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 0]);

        $this->actingAs($u)->get('/recherche?q=test')->assertStatus(403);
    }

    public function test_professeur_ne_voit_que_ses_classes(): void
    {
        $prof = $this->user(Role::Professeur, 'prof');
        EmploiDuTemps::create([
            'filiere' => 'Informatique', 'niveau' => 'L3', 'jour' => 'Lundi',
            'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Algo', 'type' => 'CM',
            'salle' => '1-1', 'professeur_id' => $prof->id,
        ]);
        $this->etudiant('inf', 'EtudiantInfo', 'Informatique', 'L3');
        $this->etudiant('ges', 'EtudiantGestion', 'Gestion', 'L3');

        $reponse = $this->actingAs($prof)->get('/recherche?q=Etudiant');
        $reponse->assertStatus(200)->assertSee('EtudiantInfo')->assertDontSee('EtudiantGestion');
    }

    public function test_admin_recherche_dans_le_personnel(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $this->user(Role::Professeur, 'professeur_x');

        $this->actingAs($admin)->get('/recherche?q=professeur_x&type=personnel')
            ->assertStatus(200)
            ->assertSee('professeur_x');
    }

    public function test_requete_vide_affiche_invite(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');

        $this->actingAs($admin)->get('/recherche')
            ->assertStatus(200)
            ->assertSee('Saisissez un terme');
    }
}
