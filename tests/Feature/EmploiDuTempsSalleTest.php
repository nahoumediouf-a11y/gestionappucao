<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\User;
use App\Notifications\SalleModifieeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class EmploiDuTempsSalleTest extends TestCase
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

    public function test_admin_can_view_emploi_du_temps_management(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'testadmin');

        $response = $this->actingAs($admin)->get('/admin/emploi-du-temps');

        $response->assertStatus(200);
    }

    public function test_changing_salle_notifies_students_and_professeur(): void
    {
        Notification::fake();

        $admin = $this->makeUser(Role::Administrateur, 'testadmin');

        $professeur = $this->makeUser(Role::Professeur, 'testprof', [
            'email' => 'prof@example.com',
        ]);

        $etudiantUser = $this->makeUser(Role::Etudiant, 'testetudiant', [
            'email' => 'etudiant@example.com',
        ]);

        Etudiant::create([
            'user_id' => $etudiantUser->id,
            'matricule' => '1000700',
            'niveau' => 'L2',
            'filiere' => 'Informatique',
            'solde' => 0,
        ]);

        $creneau = EmploiDuTemps::create([
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'jour' => 'Mardi',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'matiere' => 'Réseau Informatique',
            'salle' => '2.1',
            'professeur_id' => $professeur->id,
        ]);

        $response = $this->actingAs($admin)->put("/admin/emploi-du-temps/{$creneau->id}", [
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'jour' => 'Mardi',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'matiere' => 'Réseau Informatique',
            'type' => 'CM',
            'salle' => '3.4',
            'professeur_id' => $professeur->id,
        ]);

        $response->assertRedirect(route('admin.emploi-du-temps.index'));

        $this->assertSame('3.4', $creneau->refresh()->salle);

        Notification::assertSentTo($etudiantUser, SalleModifieeNotification::class);
        Notification::assertSentTo($professeur, SalleModifieeNotification::class);
    }

    public function test_changing_other_fields_without_salle_does_not_notify(): void
    {
        Notification::fake();

        $admin = $this->makeUser(Role::Administrateur, 'testadmin');

        $creneau = EmploiDuTemps::create([
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'jour' => 'Mardi',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'matiere' => 'Réseau Informatique',
            'salle' => '2.1',
        ]);

        $this->actingAs($admin)->put("/admin/emploi-du-temps/{$creneau->id}", [
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'jour' => 'Mardi',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'matiere' => 'Réseau Informatique avancé',
            'salle' => '2.1',
        ]);

        Notification::assertNothingSent();
    }

    public function test_professeur_sees_salle_notification_in_inbox(): void
    {
        $professeur = $this->makeUser(Role::Professeur, 'testprof', [
            'email' => 'prof@example.com',
        ]);

        $creneau = EmploiDuTemps::create([
            'filiere' => 'Informatique',
            'niveau' => 'L2',
            'jour' => 'Mardi',
            'heure_debut' => '10:00',
            'heure_fin' => '12:00',
            'matiere' => 'Réseau Informatique',
            'type' => 'CM',
            'salle' => '3.4',
            'professeur_id' => $professeur->id,
        ]);

        $professeur->notify(new SalleModifieeNotification($creneau, '2.1'));

        $response = $this->actingAs($professeur)->get('/professeur/notifications');

        $response->assertStatus(200);
        $response->assertSee('Changement de salle');
        $response->assertSee('2.1');
        $response->assertSee('3.4');
    }
}
