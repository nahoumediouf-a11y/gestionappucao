<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MessagerieTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $login, Role $role = Role::Professeur): User
    {
        return User::create([
            'nom' => ucfirst($login), 'prenom' => 'Test', 'login' => $login,
            'password' => 'password', 'role' => $role, 'statut' => 'actif',
        ]);
    }

    public function test_un_utilisateur_envoie_un_message(): void
    {
        $a = $this->user('alice');
        $b = $this->user('bob');

        $this->actingAs($a)->post('/messagerie', [
            'destinataire_id' => $b->id, 'sujet' => 'Bonjour', 'corps' => 'Test message',
        ])->assertRedirect(route('messagerie.envoyes'));

        $this->assertDatabaseHas('messages', [
            'expediteur_id' => $a->id, 'destinataire_id' => $b->id, 'sujet' => 'Bonjour',
        ]);
    }

    public function test_on_ne_peut_pas_secrire_a_soi_meme(): void
    {
        $a = $this->user('alice');

        $this->actingAs($a)->post('/messagerie', [
            'destinataire_id' => $a->id, 'sujet' => 'x', 'corps' => 'y',
        ])->assertSessionHasErrors('destinataire_id');
    }

    public function test_ouvrir_un_message_le_marque_comme_lu(): void
    {
        $a = $this->user('alice');
        $b = $this->user('bob');
        $m = Message::create(['expediteur_id' => $a->id, 'destinataire_id' => $b->id, 'sujet' => 's', 'corps' => 'c']);

        $this->assertNull($m->lu_a);
        $this->actingAs($b)->get(route('messagerie.show', $m))->assertStatus(200);
        $this->assertNotNull($m->fresh()->lu_a);
    }

    public function test_un_tiers_ne_peut_pas_lire_le_message(): void
    {
        $a = $this->user('alice');
        $b = $this->user('bob');
        $c = $this->user('charlie');
        $m = Message::create(['expediteur_id' => $a->id, 'destinataire_id' => $b->id, 'sujet' => 's', 'corps' => 'c']);

        $this->actingAs($c)->get(route('messagerie.show', $m))->assertStatus(403);
    }

    public function test_boite_de_reception_accessible(): void
    {
        $a = $this->user('alice');
        $b = $this->user('bob');
        Message::create(['expediteur_id' => $a->id, 'destinataire_id' => $b->id, 'sujet' => 'Coucou', 'corps' => 'c']);

        $this->actingAs($b)->get('/messagerie')->assertStatus(200)->assertSee('Coucou');
    }
}
