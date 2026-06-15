<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\Projet;
use App\Models\User;
use App\Notifications\EcheanceRappelNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RappelEcheanceTest extends TestCase
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

    private function makeEtudiant(string $login, array $extra = []): User
    {
        $etudiantUser = $this->makeUser(Role::Etudiant, $login, $extra);

        Etudiant::create([
            'user_id' => $etudiantUser->id,
            'matricule' => '100'.$etudiantUser->id,
            'niveau' => 'L2',
            'filiere' => 'Informatique',
            'solde' => 0,
        ]);

        return $etudiantUser;
    }

    public function test_command_sends_reminder_three_days_before_deadline_and_marks_as_sent(): void
    {
        Notification::fake();

        $professeur = $this->makeUser(Role::Professeur, 'testprof');
        $etudiant = $this->makeEtudiant('testetudiant', ['email' => 'etudiant@example.com']);

        $projet = Projet::create([
            'professeur_id' => $professeur->id,
            'type' => 'examen',
            'titre' => 'Examen de Réseaux',
            'description' => 'Chapitres 1 à 5',
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'matiere' => 'Réseau Informatique',
            'date_limite' => today()->addDays(3),
        ]);

        $this->artisan('rappels:echeances')->assertExitCode(0);

        Notification::assertSentTo($etudiant, EcheanceRappelNotification::class);
        $this->assertTrue($projet->refresh()->rappel_envoye);
    }

    public function test_command_does_not_resend_reminder_already_sent(): void
    {
        Notification::fake();

        $professeur = $this->makeUser(Role::Professeur, 'testprof');
        $this->makeEtudiant('testetudiant', ['email' => 'etudiant@example.com']);

        Projet::create([
            'professeur_id' => $professeur->id,
            'type' => 'devoir',
            'titre' => 'Devoir de Réseaux',
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'matiere' => 'Réseau Informatique',
            'date_limite' => today()->addDays(3),
            'rappel_envoye' => true,
        ]);

        $this->artisan('rappels:echeances')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_command_does_not_notify_for_deadlines_not_matching_reminder_window(): void
    {
        Notification::fake();

        $professeur = $this->makeUser(Role::Professeur, 'testprof');
        $this->makeEtudiant('testetudiant', ['email' => 'etudiant@example.com']);

        Projet::create([
            'professeur_id' => $professeur->id,
            'type' => 'projet',
            'titre' => 'Projet final',
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'matiere' => 'Réseau Informatique',
            'date_limite' => today()->addDays(5),
        ]);

        $this->artisan('rappels:echeances')->assertExitCode(0);

        Notification::assertNothingSent();
    }

    public function test_professeur_can_create_devoir_with_type(): void
    {
        $professeur = $this->makeUser(Role::Professeur, 'testprof');

        $response = $this->actingAs($professeur)->post('/professeur/projets', [
            'type' => 'devoir',
            'titre' => 'Devoir maison',
            'description' => 'Exercices 1 à 3',
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'matiere' => 'Réseau Informatique',
            'date_limite' => today()->addDays(7)->toDateString(),
        ]);

        $response->assertRedirect(route('professeur.projets.index'));

        $this->assertDatabaseHas('projets', [
            'titre' => 'Devoir maison',
            'type' => 'devoir',
        ]);
    }

    public function test_etudiant_sees_reminder_notification_in_inbox(): void
    {
        $professeur = $this->makeUser(Role::Professeur, 'testprof');
        $etudiant = $this->makeEtudiant('testetudiant', ['email' => 'etudiant@example.com']);

        $projet = Projet::create([
            'professeur_id' => $professeur->id,
            'type' => 'examen',
            'titre' => 'Examen de Réseaux',
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'matiere' => 'Réseau Informatique',
            'date_limite' => today()->addDays(3),
        ]);

        $etudiant->notify(new EcheanceRappelNotification($projet));

        $response = $this->actingAs($etudiant)->get('/etudiant/notifications');

        $response->assertStatus(200);
        $response->assertSee('Examen de Réseaux');
    }
}
