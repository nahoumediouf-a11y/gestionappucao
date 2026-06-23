<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Projet;
use App\Models\Soumission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EspaceEnseignantTest extends TestCase
{
    use RefreshDatabase;

    private function makeProf(string $login = 'prof'): User
    {
        return User::create([
            'nom' => 'Test', 'prenom' => 'Prof', 'login' => $login,
            'password' => 'password', 'role' => Role::Professeur, 'statut' => 'actif',
        ]);
    }

    private function makeEtudiant(string $login, string $filiere = 'Informatique', string $niveau = 'L3'): Etudiant
    {
        $user = User::create([
            'nom' => 'Test', 'prenom' => 'Etu', 'login' => $login, 'email' => $login.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);

        return Etudiant::create([
            'user_id' => $user->id,
            'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => $niveau, 'filiere' => $filiere, 'solde' => 0,
        ]);
    }

    private function creneau(User $prof, string $filiere = 'Informatique', string $niveau = 'L3', string $matiere = 'Algorithmique'): void
    {
        EmploiDuTemps::create([
            'filiere' => $filiere, 'niveau' => $niveau, 'jour' => 'Lundi',
            'heure_debut' => '08:00', 'heure_fin' => '10:00',
            'matiere' => $matiere, 'type' => 'CM', 'salle' => '1-1', 'professeur_id' => $prof->id,
        ]);
    }

    public function test_espace_affiche_les_compteurs(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof);
        $etu = $this->makeEtudiant('etu1');

        $projet = Projet::create([
            'professeur_id' => $prof->id, 'type' => 'devoir', 'titre' => 'D1',
            'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Algorithmique',
            'bareme' => 20, 'date_limite' => now()->addWeek()->toDateString(),
        ]);
        Soumission::create([
            'projet_id' => $projet->id, 'etudiant_id' => $etu->id,
            'texte' => 'x', 'rendu_a' => now(), 'en_retard' => false,
        ]);

        $this->actingAs($prof)->get('/professeur/espace')
            ->assertStatus(200)
            ->assertSee('Mon espace enseignant')
            ->assertSee('Informatique L3');
    }

    public function test_fiche_classe_accessible_si_enseignee(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof);
        $this->makeEtudiant('etu1');

        $this->actingAs($prof)->get('/professeur/classe?filiere=Informatique&niveau=L3')
            ->assertStatus(200)
            ->assertSee('Classe Informatique L3');
    }

    public function test_fiche_classe_refusee_si_non_enseignee(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof, 'Informatique', 'L3');

        $this->actingAs($prof)->get('/professeur/classe?filiere=Gestion&niveau=L3')
            ->assertStatus(403);
    }

    public function test_carnet_saisie_rapide_cree_la_note(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof);
        $etu = $this->makeEtudiant('etu1');

        $this->actingAs($prof)->post('/professeur/carnet/note', [
            'filiere' => 'Informatique', 'niveau' => 'L3', 'etudiant_id' => $etu->id,
            'matiere' => 'Algorithmique', 'session' => 'Contrôle n°1', 'valeur' => 14,
        ])->assertRedirect();

        $this->assertDatabaseHas('notes', [
            'etudiant_id' => $etu->id, 'matiere' => 'Algorithmique',
            'session' => 'Contrôle n°1', 'valeur' => 14,
        ]);
    }

    public function test_carnet_refuse_une_valeur_hors_bornes(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof);
        $etu = $this->makeEtudiant('etu1');

        $this->actingAs($prof)->post('/professeur/carnet/note', [
            'filiere' => 'Informatique', 'niveau' => 'L3', 'etudiant_id' => $etu->id,
            'matiere' => 'Algorithmique', 'session' => 'C1', 'valeur' => 25,
        ])->assertSessionHasErrors('valeur');

        $this->assertDatabaseCount('notes', 0);
    }

    public function test_carnet_vider_une_case_supprime_la_note(): void
    {
        $prof = $this->makeProf();
        $this->creneau($prof);
        $etu = $this->makeEtudiant('etu1');
        Note::create(['etudiant_id' => $etu->id, 'professeur_id' => $prof->id, 'matiere' => 'Algorithmique', 'session' => 'C1', 'valeur' => 12]);

        $this->actingAs($prof)->post('/professeur/carnet/note', [
            'filiere' => 'Informatique', 'niveau' => 'L3', 'etudiant_id' => $etu->id,
            'matiere' => 'Algorithmique', 'session' => 'C1', 'valeur' => '',
        ])->assertRedirect();

        $this->assertDatabaseCount('notes', 0);
    }
}
