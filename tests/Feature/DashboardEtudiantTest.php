<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardEtudiantTest extends TestCase
{
    use RefreshDatabase;

    public function test_le_dashboard_etudiant_affiche_le_bandeau_apercu(): void
    {
        $user = User::create([
            'nom' => 'Faye', 'prenom' => 'Aminata', 'login' => 'etu1', 'email' => 'etu1@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);
        Etudiant::create([
            'user_id' => $user->id, 'matricule' => '1000999',
            'niveau' => 'L3', 'filiere' => 'Informatique', 'solde' => 0,
        ]);

        $this->actingAs($user)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('Bonjour Aminata')
            ->assertSee('Solde restant')
            ->assertSee('Moyenne générale');
    }

    public function test_le_dashboard_dun_autre_role_na_pas_de_bandeau_etudiant(): void
    {
        $admin = User::create([
            'nom' => 'Diouf', 'prenom' => 'Nahoume', 'login' => 'admin',
            'password' => 'password', 'role' => Role::Administrateur, 'statut' => 'actif',
        ]);

        $this->actingAs($admin)->get('/dashboard')
            ->assertStatus(200)
            ->assertDontSee('Solde restant');
    }
}
