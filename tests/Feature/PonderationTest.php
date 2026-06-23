<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Ponderation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PonderationTest extends TestCase
{
    use RefreshDatabase;

    private function etudiant(string $login = 'etu', string $filiere = 'Informatique', string $niveau = 'L3'): Etudiant
    {
        $u = User::create([
            'nom' => 'E', 'prenom' => 'T', 'login' => $login, 'email' => $login.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);

        return Etudiant::create(['user_id' => $u->id, 'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => $niveau, 'filiere' => $filiere, 'solde' => 0]);
    }

    private function note(Etudiant $e, string $cat, float $val, string $matiere = 'Algo'): void
    {
        Note::create(['etudiant_id' => $e->id, 'matiere' => $matiere, 'categorie' => $cat,
            'valeur' => $val, 'session' => $cat.'-'.uniqid()]);
    }

    public function test_defaut_tp30_examen70(): void
    {
        $e = $this->etudiant();
        $this->note($e, 'examen', 10);
        $this->note($e, 'tp', 20);

        // 10*0.7 + 20*0.3 = 7 + 6 = 13
        $this->assertEqualsWithDelta(13, $e->moyenne(), 0.01);
    }

    public function test_tp60_examen40(): void
    {
        $e = $this->etudiant();
        Ponderation::create(['filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Algo',
            'poids_examen' => 40, 'poids_tp' => 60, 'poids_td' => 0, 'poids_cc' => 0]);
        $this->note($e, 'examen', 10);
        $this->note($e, 'tp', 20);

        // 10*0.4 + 20*0.6 = 4 + 12 = 16
        $this->assertEqualsWithDelta(16, $e->moyenne(), 0.01);
    }

    public function test_examen_100(): void
    {
        $e = $this->etudiant();
        Ponderation::create(['filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Algo',
            'poids_examen' => 100, 'poids_tp' => 0, 'poids_td' => 0, 'poids_cc' => 0]);
        $this->note($e, 'examen', 12);
        $this->note($e, 'tp', 20); // ignoré (poids 0)

        $this->assertEqualsWithDelta(12, $e->moyenne(), 0.01);
    }

    public function test_renormalisation_si_categorie_sans_note(): void
    {
        $e = $this->etudiant();
        // Défaut TP30/Examen70 mais seulement une note d'examen → re-normalisé à 100% examen.
        $this->note($e, 'examen', 14);

        $this->assertEqualsWithDelta(14, $e->moyenne(), 0.01);
    }

    public function test_somme_differente_de_100_refusee(): void
    {
        $prof = User::create(['nom' => 'P', 'prenom' => 'T', 'login' => 'prof', 'password' => 'password', 'role' => Role::Professeur, 'statut' => 'actif']);
        EmploiDuTemps::create(['filiere' => 'Informatique', 'niveau' => 'L3', 'jour' => 'Lundi', 'heure_debut' => '08:00',
            'heure_fin' => '10:00', 'matiere' => 'Algo', 'type' => 'CM', 'salle' => '1-1', 'professeur_id' => $prof->id]);

        $this->actingAs($prof)->put('/professeur/ponderation', [
            'filiere' => 'Informatique', 'niveau' => 'L3', 'matiere' => 'Algo',
            'poids_examen' => 50, 'poids_tp' => 30, 'poids_td' => 0, 'poids_cc' => 0,
        ])->assertSessionHasErrors('poids');

        $this->assertDatabaseCount('ponderations', 0);
    }

    public function test_prof_ne_pondere_pas_la_classe_dun_autre(): void
    {
        $prof = User::create(['nom' => 'P', 'prenom' => 'T', 'login' => 'prof', 'password' => 'password', 'role' => Role::Professeur, 'statut' => 'actif']);
        // Pas de créneau pour ce prof sur Gestion L3.
        $this->actingAs($prof)->put('/professeur/ponderation', [
            'filiere' => 'Gestion', 'niveau' => 'L3', 'matiere' => 'Compta',
            'poids_examen' => 70, 'poids_tp' => 30, 'poids_td' => 0, 'poids_cc' => 0,
        ])->assertStatus(403);
    }
}
