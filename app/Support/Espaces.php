<?php

namespace App\Support;

use App\Enums\Role;

/**
 * Source de vérité unique des « espaces » d'accès affichés sur la page d'accueil
 * et la page de connexion : deux entrées seulement — Étudiant et Personnel
 * (administration, professeurs et services de gestion). L'espace est purement
 * cosmétique / de guidage : l'autorisation réelle reste fondée sur le rôle.
 */
class Espaces
{
    /** @return array<string, array<string, mixed>> */
    public static function all(): array
    {
        return [
            'etudiant' => [
                'cle' => 'etudiant',
                'label' => 'Espace Étudiant',
                'icone' => 'bi-person-badge',
                'couleur' => 'primary',
                'baseline' => 'Consultez vos notes, votre bulletin, votre emploi du temps, vos absences, vos cours en ligne et le suivi de vos paiements.',
                'fonctionnalites' => ['Notes et bulletin', 'Emploi du temps & cours en ligne', 'Absences', 'Paiements et reçus'],
                'roles' => [Role::Etudiant],
            ],
            'personnel' => [
                'cle' => 'personnel',
                'label' => 'Administration / Personnel',
                'icone' => 'bi-shield-lock',
                'couleur' => 'dark',
                'baseline' => 'Accès réservé au personnel : administration, professeurs, comptabilité, recouvrement et finances.',
                'fonctionnalites' => ['Administrateur', 'Professeur', 'Agent comptable', 'Agent de recouvrement', 'Responsable financier'],
                'roles' => [
                    Role::Administrateur,
                    Role::Professeur,
                    Role::AgentComptable,
                    Role::AgentRecouvrement,
                    Role::ResponsableFinancier,
                ],
            ],
        ];
    }

    /** Récupère un espace par sa clé, avec repli sur un espace générique. */
    public static function get(?string $cle): array
    {
        return self::all()[$cle] ?? self::generique();
    }

    /** Espace générique (clé absente ou invalide). */
    public static function generique(): array
    {
        return [
            'cle' => 'generique',
            'label' => 'Connexion',
            'icone' => 'bi-box-arrow-in-right',
            'couleur' => 'primary',
            'baseline' => 'Accédez à votre espace avec vos identifiants.',
            'fonctionnalites' => [],
            'roles' => [],
        ];
    }
}
