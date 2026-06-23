<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConnexionEspacesTest extends TestCase
{
    use RefreshDatabase;

    public function test_accueil_liste_toutes_les_parties_prenantes(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Espace Étudiant')
            ->assertSee('Espace Professeur')
            ->assertSee('Administration')
            ->assertSee('Comptabilité')
            ->assertSee('Recouvrement')
            ->assertSee('Finances')
            ->assertSee('Pédagogie')
            ->assertSee('Gestion');
    }

    public function test_login_affiche_la_marque_de_lespace_professeur(): void
    {
        $this->get('/login?espace=professeur')
            ->assertStatus(200)
            ->assertSee('Espace Professeur')
            ->assertSee('Carnet de notes');
    }

    public function test_login_affiche_la_marque_de_la_comptabilite(): void
    {
        $this->get('/login?espace=comptabilite')
            ->assertStatus(200)
            ->assertSee('Comptabilité');
    }

    public function test_login_avec_espace_invalide_retombe_sur_le_generique(): void
    {
        $this->get('/login?espace=nimporte_quoi')
            ->assertStatus(200)
            ->assertSee('Connexion')
            ->assertSee('combien font');
    }
}
