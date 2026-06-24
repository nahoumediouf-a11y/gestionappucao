<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagerieGroupeTest extends TestCase
{
    use RefreshDatabase;

    private function user(Role $role, string $login): User
    {
        return User::create([
            'nom' => ucfirst($login), 'prenom' => 'Test', 'login' => $login,
            'email' => $login.'@ex.com', 'password' => 'password', 'role' => $role, 'statut' => 'actif',
        ]);
    }

    private function etudiant(string $login, string $filiere = 'LIG', string $niveau = 'L3'): Etudiant
    {
        $u = User::create([
            'nom' => ucfirst($login), 'prenom' => 'E', 'login' => $login, 'email' => $login.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);

        return Etudiant::create([
            'user_id' => $u->id, 'matricule' => 'M'.fake()->unique()->numberBetween(100000, 999999),
            'niveau' => $niveau, 'filiere' => $filiere, 'solde' => 0,
        ]);
    }

    public function test_envoi_a_une_classe_atteint_tous_les_etudiants(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $a = $this->etudiant('e1', 'LIG', 'L3');
        $b = $this->etudiant('e2', 'LIG', 'L3');
        $this->etudiant('e3', 'LSG', 'L3'); // autre classe : ne doit PAS recevoir

        $this->actingAs($admin)->post('/messagerie', [
            'classes' => ['LIG|L3'], 'sujet' => 'Réunion', 'corps' => 'Bonjour la classe',
        ])->assertRedirect(route('messagerie.envoyes'));

        $this->assertSame(2, Message::where('expediteur_id', $admin->id)->count());
        $this->assertDatabaseHas('messages', ['destinataire_id' => $a->user_id, 'sujet' => 'Réunion']);
        $this->assertDatabaseHas('messages', ['destinataire_id' => $b->user_id, 'sujet' => 'Réunion']);

        // Même diffusion pour tout l'envoi groupé.
        $this->assertSame(1, Message::whereNotNull('diffusion_id')->distinct('diffusion_id')->count('diffusion_id'));
    }

    public function test_envoi_a_un_role_entier(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $p1 = $this->user(Role::Professeur, 'prof1');
        $p2 = $this->user(Role::Professeur, 'prof2');

        $this->actingAs($admin)->post('/messagerie', [
            'roles' => [Role::Professeur->value], 'sujet' => 'Note de service', 'corps' => 'À tous les profs',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', ['destinataire_id' => $p1->id, 'sujet' => 'Note de service']);
        $this->assertDatabaseHas('messages', ['destinataire_id' => $p2->id, 'sujet' => 'Note de service']);
    }

    public function test_les_doublons_sont_dedupliques(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $u1 = $this->user(Role::AgentComptable, 'c1');
        $u2 = $this->user(Role::AgentComptable, 'c2');

        $this->actingAs($admin)->post('/messagerie', [
            'users' => [$u1->id, $u1->id, $u2->id], 'sujet' => 'Hello', 'corps' => 'x',
        ])->assertRedirect();

        $this->assertSame(2, Message::where('expediteur_id', $admin->id)->count());
    }

    public function test_professeur_ne_peut_pas_cibler_un_etudiant_hors_de_ses_classes(): void
    {
        $prof = $this->user(Role::Professeur, 'prof');
        EmploiDuTemps::create([
            'filiere' => 'LIG', 'niveau' => 'L3', 'jour' => 'Lundi',
            'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Algo', 'type' => 'CM',
            'salle' => '1-1', 'professeur_id' => $prof->id,
        ]);
        $sien = $this->etudiant('mien', 'LIG', 'L3');
        $autre = $this->etudiant('autre', 'LSG', 'L3');

        $this->actingAs($prof)->post('/messagerie', [
            'etudiants' => [$sien->user_id, $autre->user_id], 'sujet' => 'Cours', 'corps' => 'info',
        ])->assertRedirect();

        $this->assertDatabaseHas('messages', ['destinataire_id' => $sien->user_id]);
        $this->assertDatabaseMissing('messages', ['destinataire_id' => $autre->user_id]);
    }

    public function test_un_etudiant_ne_peut_pas_diffuser_a_d_autres_etudiants(): void
    {
        $expediteur = $this->etudiant('moi');
        $cible = $this->etudiant('cible');

        $this->actingAs($expediteur->user)->post('/messagerie', [
            'etudiants' => [$cible->user_id], 'sujet' => 'Coucou', 'corps' => 'x',
        ])->assertSessionHasErrors('destinataires');

        $this->assertDatabaseCount('messages', 0);
    }

    public function test_envoyes_regroupe_la_diffusion(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $this->etudiant('e1', 'LIG', 'L3');
        $this->etudiant('e2', 'LIG', 'L3');

        $this->actingAs($admin)->post('/messagerie', [
            'classes' => ['LIG|L3'], 'sujet' => 'Diffusion test', 'corps' => 'y',
        ]);

        $this->actingAs($admin)->get('/messagerie/envoyes')
            ->assertStatus(200)
            ->assertSee('2 destinataires')
            ->assertSee('Diffusion test');
    }

    public function test_etat_lu_independant_par_destinataire(): void
    {
        $admin = $this->user(Role::Administrateur, 'admin');
        $a = $this->etudiant('e1', 'LIG', 'L3');
        $b = $this->etudiant('e2', 'LIG', 'L3');

        $this->actingAs($admin)->post('/messagerie', [
            'classes' => ['LIG|L3'], 'sujet' => 'Test', 'corps' => 'z',
        ]);

        $msgA = Message::where('destinataire_id', $a->user_id)->first();
        $this->actingAs($a->user)->get(route('messagerie.show', $msgA))->assertStatus(200);

        $this->assertNotNull($msgA->fresh()->lu_a);
        $this->assertNull(Message::where('destinataire_id', $b->user_id)->first()->lu_a);
    }
}
