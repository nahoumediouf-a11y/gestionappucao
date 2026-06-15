<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BulletinPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_bulletin_page(): void
    {
        $user = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testetudiant',
            'password' => 'password',
            'role' => Role::Etudiant,
            'statut' => 'actif',
        ]);

        Etudiant::create([
            'user_id' => $user->id,
            'matricule' => '1000999',
            'niveau' => 'L1',
            'filiere' => 'Informatique',
            'solde' => 0,
        ]);

        $response = $this->actingAs($user)->get('/etudiant/bulletin');

        $response->assertStatus(200);
    }

    public function test_student_can_download_bulletin_pdf(): void
    {
        $user = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testetudiant',
            'password' => 'password',
            'role' => Role::Etudiant,
            'statut' => 'actif',
        ]);

        Etudiant::create([
            'user_id' => $user->id,
            'matricule' => '1000999',
            'niveau' => 'L1',
            'filiere' => 'Informatique',
            'solde' => 0,
        ]);

        $response = $this->actingAs($user)->get('/etudiant/bulletin/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_student_with_outstanding_balance_cannot_download_bulletin_pdf(): void
    {
        $user = User::create([
            'nom' => 'Diallo',
            'prenom' => 'Awa',
            'login' => 'testetudiant',
            'password' => 'password',
            'role' => Role::Etudiant,
            'statut' => 'actif',
        ]);

        Etudiant::create([
            'user_id' => $user->id,
            'matricule' => '1000999',
            'niveau' => 'L1',
            'filiere' => 'Informatique',
            'solde' => 50000,
        ]);

        $response = $this->actingAs($user)->get('/etudiant/bulletin/pdf');

        $response->assertRedirect(route('etudiant.bulletin.index'));
        $response->assertSessionHas('error');
    }
}
