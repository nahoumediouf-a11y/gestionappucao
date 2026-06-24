<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Etudiant;
use App\Models\Message;
use App\Models\MessagePieceJointe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MessageriePiecesJointesTest extends TestCase
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

    public function test_envoi_avec_piece_jointe_stocke_le_fichier(): void
    {
        Storage::fake('local');
        $admin = $this->user(Role::Administrateur, 'admin');
        $dest = $this->user(Role::AgentComptable, 'comptable');

        $this->actingAs($admin)->post('/messagerie', [
            'users' => [$dest->id], 'sujet' => 'Document', 'corps' => 'voir pièce jointe',
            'pieces' => [UploadedFile::fake()->image('photo.jpg', 300, 300)],
        ])->assertRedirect(route('messagerie.envoyes'));

        $piece = MessagePieceJointe::first();
        $this->assertNotNull($piece);
        $this->assertTrue($piece->estImage());
        Storage::disk('local')->assertExists($piece->chemin);

        $message = Message::where('destinataire_id', $dest->id)->first();
        $this->assertTrue($message->piecesJointes->contains('id', $piece->id));
    }

    public function test_telechargement_reserve_a_l_expediteur_et_aux_destinataires(): void
    {
        Storage::fake('local');
        $admin = $this->user(Role::Administrateur, 'admin');
        $dest = $this->user(Role::AgentComptable, 'comptable');
        $tiers = $this->user(Role::Professeur, 'prof');

        $this->actingAs($admin)->post('/messagerie', [
            'users' => [$dest->id], 'sujet' => 'Doc', 'corps' => 'x',
            'pieces' => [UploadedFile::fake()->create('rapport.pdf', 120, 'application/pdf')],
        ]);

        $piece = MessagePieceJointe::firstOrFail();

        $this->actingAs($dest)->get(route('messagerie.piece-jointe', $piece))->assertStatus(200);
        $this->actingAs($admin)->get(route('messagerie.piece-jointe', $piece))->assertStatus(200);
        $this->actingAs($tiers)->get(route('messagerie.piece-jointe', $piece))->assertStatus(403);
    }

    public function test_piece_jointe_stockee_une_fois_pour_toute_la_diffusion(): void
    {
        Storage::fake('local');
        $admin = $this->user(Role::Administrateur, 'admin');
        $a = $this->etudiant('e1', 'LIG', 'L3');
        $b = $this->etudiant('e2', 'LIG', 'L3');

        $this->actingAs($admin)->post('/messagerie', [
            'classes' => ['LIG|L3'], 'sujet' => 'Cours', 'corps' => 'support en pj',
            'pieces' => [UploadedFile::fake()->image('support.png', 200, 200)],
        ]);

        // Le fichier n'est stocké qu'une seule fois, partagé par les 2 destinataires.
        $this->assertSame(1, MessagePieceJointe::count());
        $this->assertSame(2, Message::whereNotNull('diffusion_id')->count());

        $piece = MessagePieceJointe::first();
        foreach ([$a->user_id, $b->user_id] as $uid) {
            $msg = Message::where('destinataire_id', $uid)->first();
            $this->assertTrue($msg->piecesJointes->contains('id', $piece->id));
        }
    }

    public function test_type_de_fichier_refuse(): void
    {
        Storage::fake('local');
        $admin = $this->user(Role::Administrateur, 'admin');
        $dest = $this->user(Role::AgentComptable, 'comptable');

        $this->actingAs($admin)->post('/messagerie', [
            'users' => [$dest->id], 'sujet' => 'x', 'corps' => 'y',
            'pieces' => [UploadedFile::fake()->create('script.exe', 10, 'application/octet-stream')],
        ])->assertSessionHasErrors('pieces.0');

        $this->assertSame(0, MessagePieceJointe::count());
    }
}
