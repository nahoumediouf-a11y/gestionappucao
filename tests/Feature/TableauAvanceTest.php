<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TableauAvanceTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::create([
            'nom' => 'Diouf', 'prenom' => 'Admin', 'login' => 'admin',
            'password' => 'password', 'role' => Role::Administrateur, 'statut' => 'actif',
        ]);
    }

    public function test_tri_par_colonne_autorisee(): void
    {
        $admin = $this->admin();
        User::create(['nom' => 'Zebra', 'prenom' => 'A', 'login' => 'zzz', 'password' => 'password', 'role' => Role::Professeur, 'statut' => 'actif']);

        $this->actingAs($admin)->get('/admin/utilisateurs?tri=login&dir=desc')->assertStatus(200);
    }

    public function test_tri_par_colonne_non_autorisee_retombe_sur_defaut(): void
    {
        $admin = $this->admin();

        // Une colonne non autorisée (ex. password) ne doit pas provoquer d'erreur.
        $this->actingAs($admin)->get('/admin/utilisateurs?tri=password&dir=asc')->assertStatus(200);
    }

    public function test_export_csv_telecharge_un_fichier(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->get('/admin/utilisateurs/export');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $contenu = $response->streamedContent();
        $this->assertStringContainsString('Nom complet', $contenu);
        $this->assertStringContainsString('admin', $contenu);
        // Séparateur Excel « ; »
        $this->assertStringContainsString(';', $contenu);
    }

    public function test_export_respecte_le_filtre_role(): void
    {
        $admin = $this->admin();
        User::create(['nom' => 'Prof', 'prenom' => 'X', 'login' => 'prof_x', 'password' => 'password', 'role' => Role::Professeur, 'statut' => 'actif']);

        $contenu = $this->actingAs($admin)->get('/admin/utilisateurs/export?role='.Role::Professeur->value)->streamedContent();

        $this->assertStringContainsString('prof_x', $contenu);
        $this->assertStringNotContainsString('admin', $contenu);
    }
}
