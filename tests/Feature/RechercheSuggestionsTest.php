<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RechercheSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private function personnel(Role $role, string $login): User
    {
        return User::create([
            'nom' => ucfirst($login), 'prenom' => 'Agent', 'login' => $login,
            'email' => $login.'@ex.com', 'password' => 'password', 'role' => $role, 'statut' => 'actif',
        ]);
    }

    private function etudiant(string $matricule, string $nom, string $prenom = 'Test', string $filiere = 'LIG', string $niveau = 'L3'): Etudiant
    {
        $u = User::create([
            'nom' => $nom, 'prenom' => $prenom, 'login' => 'u'.$matricule, 'email' => $matricule.'@ex.com',
            'password' => 'password', 'role' => Role::Etudiant, 'statut' => 'actif',
        ]);

        return Etudiant::create([
            'user_id' => $u->id, 'matricule' => $matricule,
            'niveau' => $niveau, 'filiere' => $filiere, 'solde' => 0,
        ]);
    }

    /** @return array<int, array<string, mixed>> */
    private function suggestions(User $acteur, string $q, ?string $type = null): array
    {
        $url = '/recherche/suggestions?q='.urlencode($q).($type ? '&type='.$type : '');

        return $this->actingAs($acteur)->getJson($url)->json();
    }

    public function test_etudiant_na_pas_acces(): void
    {
        $e = $this->etudiant('M100', 'Sane');

        $this->actingAs($e->user)->getJson('/recherche/suggestions?q=test')->assertStatus(403);
    }

    public function test_professeur_ne_recoit_que_ses_classes(): void
    {
        $prof = $this->personnel(Role::Professeur, 'prof');
        EmploiDuTemps::create([
            'filiere' => 'LIG', 'niveau' => 'L3', 'jour' => 'Lundi',
            'heure_debut' => '08:00', 'heure_fin' => '10:00', 'matiere' => 'Algo', 'type' => 'CM',
            'salle' => '1-1', 'professeur_id' => $prof->id,
        ]);
        $this->etudiant('M201', 'Alphainfo', 'X', 'LIG', 'L3');
        $this->etudiant('M202', 'Alphagestion', 'Y', 'LSG', 'L3');

        $noms = array_column($this->suggestions($prof, 'alpha'), 'label');

        $this->assertcontains_substr($noms, 'Alphainfo');
        $this->assertNotContains_substr($noms, 'Alphagestion');
    }

    public function test_prefixe_de_matricule_passe_en_premier(): void
    {
        $comptable = $this->personnel(Role::AgentComptable, 'comptable');
        $this->etudiant('ZAB1234', 'Milieu', 'A');   // contient AB12 au milieu
        $this->etudiant('AB1234', 'Debut', 'B');      // commence par AB12

        $res = $this->suggestions($comptable, 'ab12');

        $this->assertNotEmpty($res);
        $this->assertSame('AB1234', $res[0]['matricule']);
    }

    public function test_recherche_en_plusieurs_mots(): void
    {
        $comptable = $this->personnel(Role::AgentComptable, 'comptable');
        $this->etudiant('M300', 'Diouf', 'Nahoume');

        $matricules = array_column($this->suggestions($comptable, 'nahoume diouf'), 'matricule');

        $this->assertContains('M300', $matricules);
    }

    public function test_insensible_aux_accents(): void
    {
        $comptable = $this->personnel(Role::AgentComptable, 'comptable');
        $this->etudiant('M400', 'Néné', 'Aïcha');

        $matricules = array_column($this->suggestions($comptable, 'nene'), 'matricule');

        $this->assertContains('M400', $matricules);
    }

    public function test_requete_trop_courte_renvoie_vide(): void
    {
        $comptable = $this->personnel(Role::AgentComptable, 'comptable');
        $this->etudiant('M500', 'Sarr');

        $this->assertSame([], $this->suggestions($comptable, 'a'));
    }

    /** @param array<int, string> $valeurs */
    private function assertContains_substr(array $valeurs, string $attendu): void
    {
        $this->assertTrue(
            collect($valeurs)->contains(fn ($v) => str_contains($v, $attendu)),
            "« {$attendu} » devrait figurer dans les suggestions."
        );
    }

    /** @param array<int, string> $valeurs */
    private function assertNotContains_substr(array $valeurs, string $interdit): void
    {
        $this->assertFalse(
            collect($valeurs)->contains(fn ($v) => str_contains($v, $interdit)),
            "« {$interdit} » ne devrait pas figurer dans les suggestions."
        );
    }
}
