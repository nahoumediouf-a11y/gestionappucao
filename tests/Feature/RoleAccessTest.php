<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(Role $role, string $login): User
    {
        return User::create([
            'nom' => 'Test',
            'prenom' => 'Utilisateur',
            'login' => $login,
            'password' => 'password',
            'role' => $role,
            'statut' => 'actif',
        ]);
    }

    public function test_admin_can_access_user_management(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'testadmin');

        $response = $this->actingAs($admin)->get('/admin/utilisateurs');

        $response->assertStatus(200);
    }

    public function test_student_cannot_access_admin_user_management(): void
    {
        $etudiant = $this->makeUser(Role::Etudiant, 'testetudiant');

        $response = $this->actingAs($etudiant)->get('/admin/utilisateurs');

        $response->assertStatus(403);
    }

    public function test_professeur_cannot_access_admin_statistiques(): void
    {
        $professeur = $this->makeUser(Role::Professeur, 'testprof');

        $response = $this->actingAs($professeur)->get('/admin/statistiques');

        $response->assertStatus(403);
    }

    public function test_admin_can_access_statistiques(): void
    {
        $admin = $this->makeUser(Role::Administrateur, 'testadmin');

        $response = $this->actingAs($admin)->get('/admin/statistiques');

        $response->assertStatus(200);
    }
}
