<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_is_accessible(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_user_can_login_with_correct_credentials(): void
    {
        $user = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testadmin',
            'password' => 'password',
            'role' => Role::Administrateur,
            'statut' => 'actif',
        ]);

        $response = $this->withSession(['captcha_answer' => 7])->post('/login', [
            'login' => $user->login,
            'password' => 'password',
            'captcha' => '7',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_login_with_wrong_password(): void
    {
        User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testadmin',
            'password' => 'password',
            'role' => Role::Administrateur,
            'statut' => 'actif',
        ]);

        $response = $this->withSession(['captcha_answer' => 7])->post('/login', [
            'login' => 'testadmin',
            'password' => 'mauvais-mot-de-passe',
            'captcha' => '7',
        ]);

        $response->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_user_cannot_login_with_wrong_captcha(): void
    {
        User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testadmin',
            'password' => 'password',
            'role' => Role::Administrateur,
            'statut' => 'actif',
        ]);

        $response = $this->withSession(['captcha_answer' => 7])->post('/login', [
            'login' => 'testadmin',
            'password' => 'password',
            'captcha' => '1',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_when_accessing_dashboard(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/login');
    }
}
