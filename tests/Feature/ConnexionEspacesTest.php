<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnexionEspacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_accueil_propose_deux_espaces(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Espace Étudiant')
            ->assertSee('Administration / Personnel');
    }

    public function test_login_affiche_la_marque_etudiant(): void
    {
        $this->get('/login?espace=etudiant')
            ->assertStatus(200)
            ->assertSee('Espace Étudiant')
            ->assertSee('Notes et bulletin');
    }

    public function test_login_affiche_la_marque_personnel(): void
    {
        $this->get('/login?espace=personnel')
            ->assertStatus(200)
            ->assertSee('Administration / Personnel')
            ->assertSee('Professeur');
    }

    public function test_login_avec_espace_invalide_retombe_sur_le_generique(): void
    {
        $this->get('/login?espace=nimporte_quoi')
            ->assertStatus(200)
            ->assertSee('Connexion')
            ->assertSee('combien font');
    }
}
