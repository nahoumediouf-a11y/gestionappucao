<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\CoursEnLigne;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoursEnLigneTest extends TestCase
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

    private function makeEtudiant(string $login, string $filiere, string $niveau): User
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

    private function makeSeance(User $prof, array $extra = []): CoursEnLigne
    {
        return CoursEnLigne::create(array_merge([
            'professeur_id' => $prof->id,
            'titre' => 'Séance test',
            'filiere' => 'Informatique',
            'niveau' => 'L3',
            'room_name' => CoursEnLigne::genererRoomName('Séance test'),
            'debut_prevu' => now()->subMinutes(5),
            'statut' => 'en_cours',
        ], $extra));
    }

    public function test_les_pages_index_saffichent(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'admin');
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etudiant = $this->makeEtudiant('etu', 'Informatique', 'L3');
        $this->makeSeance($prof);

        $this->actingAs($prof)->get('/professeur/cours-en-ligne')->assertStatus(200);
        $this->actingAs($prof)->get('/professeur/cours-en-ligne/creer')->assertStatus(200);
        $this->actingAs($etudiant)->get('/etudiant/cours-en-ligne')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/cours-en-ligne')->assertStatus(200);
    }

    public function test_professeur_peut_planifier_une_seance(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');

        $response = $this->actingAs($prof)->post('/professeur/cours-en-ligne', [
            'titre' => 'Cours de test',
            'filiere' => 'Informatique',
            'niveau' => 'L3',
            'debut_prevu' => now()->addDay()->format('Y-m-d\TH:i'),
        ]);

        $response->assertRedirect(route('professeur.cours.index'));

        $cours = CoursEnLigne::first();
        $this->assertNotNull($cours);
        $this->assertSame('planifie', $cours->statut);
        $this->assertNotEmpty($cours->room_name);
        $this->assertSame($prof->id, $cours->professeur_id);
    }

    public function test_demarrer_et_terminer_changent_le_statut(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $cours = $this->makeSeance($prof, ['statut' => 'planifie', 'debut_prevu' => now()->addHour()]);

        $this->actingAs($prof)->post("/professeur/cours-en-ligne/{$cours->id}/demarrer");
        $this->assertSame('en_cours', $cours->fresh()->statut);

        $this->actingAs($prof)->post("/professeur/cours-en-ligne/{$cours->id}/terminer");
        $this->assertSame('termine', $cours->fresh()->statut);
    }

    public function test_un_prof_ne_peut_pas_gerer_la_seance_dun_autre(): void
    {
        $prof1 = $this->makeUser(Role::Professeur, 'prof1');
        $prof2 = $this->makeUser(Role::Professeur, 'prof2');
        $cours = $this->makeSeance($prof1);

        $this->actingAs($prof2)->get("/professeur/cours-en-ligne/{$cours->id}/salle")->assertStatus(403);
    }

    public function test_etudiant_de_la_classe_peut_rejoindre_une_seance_en_cours(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etudiant = $this->makeEtudiant('etu1', 'Informatique', 'L3');
        $cours = $this->makeSeance($prof);

        $this->actingAs($etudiant)->get("/etudiant/cours-en-ligne/{$cours->id}/salle")->assertStatus(200);
    }

    public function test_etudiant_dune_autre_classe_ne_peut_pas_rejoindre(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etudiant = $this->makeEtudiant('etu2', 'Gestion', 'L3');
        $cours = $this->makeSeance($prof);

        $this->actingAs($etudiant)->get("/etudiant/cours-en-ligne/{$cours->id}/salle")->assertStatus(403);
    }

    public function test_seance_planifiee_hors_fenetre_nest_pas_rejoignable(): void
    {
        $prof = $this->makeUser(Role::Professeur, 'prof');
        $etudiant = $this->makeEtudiant('etu3', 'Informatique', 'L3');
        $cours = $this->makeSeance($prof, ['statut' => 'planifie', 'debut_prevu' => now()->addHours(3)]);

        $this->assertFalse($cours->estRejoignable());

        $this->actingAs($etudiant)->get("/etudiant/cours-en-ligne/{$cours->id}/salle")
            ->assertRedirect(route('etudiant.cours.index'));
    }
}
