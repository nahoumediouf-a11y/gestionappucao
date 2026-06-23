<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Projet;
use App\Models\Soumission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EvaluationTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(Role $role, string $login, array $extra = []): User
    {
        return User::create(array_merge([
            'nom' => 'Test',
            'prenom' => 'Utilisateur',
            'login' => $login,
            'password' => 'password',
            'role' => $role,
            'statut' => 'actif',
        ], $extra));
    }

    private function makeEtudiant(string $login, string $filiere = 'Informatique', string $niveau = 'L3'): User
    {
        $user = $this->makeUser(Role::Etudiant, $login, ['email' => $login.'@example.com']);
        Etudiant::create([
            'user_id' => $user->id,
            'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => $niveau,
            'filiere' => $filiere,
            'solde' => 0,
        ]);

        return $user;
    }

    private function makeProjet(User $prof, array $extra = []): Projet
    {
        return Projet::create(array_merge([
            'professeur_id' => $prof->id,
            'type' => 'devoir',
            'titre' => 'Devoir test',
            'filiere' => 'Informatique',
            'niveau' => 'L3',
            'matiere' => 'Algorithmique',
            'bareme' => 20,
            'rendu_en_ligne' => true,
            'date_limite' => now()->addWeek()->toDateString(),
        ], $extra));
    }

    public function test_etudiant_rend_un_travail_avant_echeance(): void
    {
        Storage::fake('local');
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof);

        $this->actingAs($etu)->post(route('etudiant.projets.soumettre', $projet), [
            'texte' => 'Ma réponse',
            'fichier' => UploadedFile::fake()->create('copie.pdf', 100, 'application/pdf'),
        ])->assertRedirect(route('etudiant.projets.show', $projet));

        $s = Soumission::first();
        $this->assertNotNull($s);
        $this->assertFalse($s->en_retard);
        Storage::disk('local')->assertExists($s->fichier_path);
    }

    public function test_rendu_apres_echeance_est_marque_en_retard(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof, ['date_limite' => now()->subDays(2)->toDateString()]);

        $this->actingAs($etu)->post(route('etudiant.projets.soumettre', $projet), ['texte' => 'En retard']);

        $this->assertTrue(Soumission::first()->en_retard);
    }

    public function test_copie_unique_bloque_la_resoumission(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof, ['copie_unique' => true]);

        $this->actingAs($etu)->post(route('etudiant.projets.soumettre', $projet), ['texte' => 'Copie 1']);
        $this->actingAs($etu)->post(route('etudiant.projets.soumettre', $projet), ['texte' => 'Copie 2'])
            ->assertSessionHas('error');

        $this->assertSame(1, Soumission::count());
        $this->assertSame('Copie 1', Soumission::first()->texte);
    }

    public function test_correction_publie_une_note_sur_20(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof, ['bareme' => 40]);
        $etudiant = $etu->etudiant;
        $s = Soumission::create([
            'projet_id' => $projet->id, 'etudiant_id' => $etudiant->id,
            'texte' => 'x', 'rendu_a' => now(), 'en_retard' => false,
        ]);

        $this->actingAs($prof)->post(route('professeur.projets.corriger', [$projet, $s]), [
            'note' => 30,
            'commentaire_correction' => 'Bien',
        ])->assertSessionHas('success');

        $this->assertEqualsWithDelta(30, (float) $s->fresh()->note, 0.01);
        // 30/40 ramené sur 20 = 15.
        $note = Note::where('etudiant_id', $etudiant->id)->where('session', 'Contrôle continu')->first();
        $this->assertNotNull($note);
        $this->assertEqualsWithDelta(15, (float) $note->valeur, 0.01);
    }

    public function test_etudiant_dune_autre_classe_ne_peut_pas_voir_ni_rendre(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $autre = $this->makeEtudiant('etuX', 'Gestion', 'L3');
        $projet = $this->makeProjet($prof);

        $this->actingAs($autre)->get(route('etudiant.projets.show', $projet))->assertStatus(403);
        $this->actingAs($autre)->post(route('etudiant.projets.soumettre', $projet), ['texte' => 'non'])->assertStatus(403);
    }

    public function test_un_prof_ne_corrige_pas_le_projet_dun_autre(): void
    {
        $prof1 = $this->makeUser(Role::Professeur, 'prof1');
        $prof2 = $this->makeUser(Role::Professeur, 'prof2');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof1);
        $s = Soumission::create([
            'projet_id' => $projet->id, 'etudiant_id' => $etu->etudiant->id,
            'texte' => 'x', 'rendu_a' => now(), 'en_retard' => false,
        ]);

        $this->actingAs($prof2)->post(route('professeur.projets.corriger', [$projet, $s]), ['note' => 10])
            ->assertStatus(403);
    }

    public function test_telechargement_fichier_protege_pour_un_autre_prof(): void
    {
        Storage::fake('local');
        $prof1 = $this->makeUser(Role::Professeur, 'prof1');
        $prof2 = $this->makeUser(Role::Professeur, 'prof2');
        $etu = $this->makeEtudiant('etu1');
        $projet = $this->makeProjet($prof1);
        $s = Soumission::create([
            'projet_id' => $projet->id, 'etudiant_id' => $etu->etudiant->id,
            'fichier_path' => UploadedFile::fake()->create('c.pdf', 10)->store('soumissions', 'local'),
            'fichier_nom' => 'c.pdf', 'rendu_a' => now(), 'en_retard' => false,
        ]);

        $this->actingAs($prof2)->get(route('professeur.projets.copie.fichier', [$projet, $s]))->assertStatus(403);
        $this->actingAs($prof1)->get(route('professeur.projets.copie.fichier', [$projet, $s]))->assertOk();
    }
}
