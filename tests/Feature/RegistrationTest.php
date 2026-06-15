<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_student_account_is_created_with_pending_status(): void
    {
        $response = $this->withSession(['captcha_answer' => 7])->post('/inscription', [
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'nouvel_etudiant',
            'email' => 'nouvel.etudiant@example.com',
            'password' => 'password1',
            'password_confirmation' => 'password1',
            'niveau' => 'L1-1',
            'filiere' => 'LIG',
            'captcha' => '7',
        ]);

        $response->assertRedirect(route('login'));
        $this->assertGuest();

        $user = User::where('login', 'nouvel_etudiant')->first();
        $this->assertNotNull($user);
        $this->assertSame('en_attente', $user->statut);
        $this->assertNotNull($user->etudiant);
    }

    public function test_pending_account_cannot_login_and_gets_pending_message(): void
    {
        $user = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'enattente',
            'password' => 'password',
            'role' => Role::Etudiant,
            'statut' => 'en_attente',
        ]);

        $response = $this->withSession(['captcha_answer' => 7])->post('/login', [
            'login' => $user->login,
            'password' => 'password',
            'captcha' => '7',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_admin_can_activate_pending_account(): void
    {
        Notification::fake();

        $admin = User::create([
            'nom' => 'Admin',
            'prenom' => 'Test',
            'login' => 'testadmin',
            'password' => 'password',
            'role' => Role::Administrateur,
            'statut' => 'actif',
        ]);

        $pending = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'enattente',
            'email' => 'enattente@example.com',
            'password' => 'password',
            'role' => Role::Etudiant,
            'statut' => 'en_attente',
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.utilisateurs.activer', $pending));

        $response->assertRedirect();
        $this->assertSame('actif', $pending->refresh()->statut);

        Notification::assertSentTo($pending, \App\Notifications\CompteActiveNotification::class);
    }
}
