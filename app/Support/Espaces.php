<?php

namespace App\Support;

use App\Enums\Role;

/**
 * Source de vérité unique des « espaces » (parties prenantes) affichés sur la
 * page d'accueil et la page de connexion. L'espace est purement cosmétique /
 * de guidage : l'autorisation réelle reste fondée sur le rôle de l'utilisateur.
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
                'famille' => 'Pédagogie',
                'icone' => 'bi-person-badge',
                'couleur' => 'primary',
                'baseline' => 'Consultez vos notes, votre bulletin, votre emploi du temps, vos absences, vos cours en ligne et le suivi de vos paiements.',
                'fonctionnalites' => ['Notes et bulletin', 'Emploi du temps & cours en ligne', 'Absences', 'Paiements et reçus'],
                'roles' => [Role::Etudiant],
            ],
            'professeur' => [
                'cle' => 'professeur',
                'label' => 'Espace Professeur',
                'famille' => 'Pédagogie',
                'icone' => 'bi-easel',
                'couleur' => 'success',
                'baseline' => 'Gérez vos classes, vos notes, les absences, vos cours en ligne et la correction des travaux.',
                'fonctionnalites' => ['Mon espace & mes classes', 'Carnet de notes', 'Cours en ligne', 'Correction des copies'],
                'roles' => [Role::Professeur],
            ],
            'administration' => [
                'cle' => 'administration',
                'label' => 'Administration',
                'famille' => 'Gestion',
                'icone' => 'bi-shield-lock',
                'couleur' => 'dark',
                'baseline' => 'Gestion des utilisateurs, des emplois du temps, des statistiques et supervision générale.',
                'fonctionnalites' => ['Utilisateurs et rôles', 'Emplois du temps & salles', 'Statistiques', 'Journal des activités'],
                'roles' => [Role::Administrateur],
            ],
            'comptabilite' => [
                'cle' => 'comptabilite',
                'label' => 'Comptabilité',
                'famille' => 'Gestion',
                'icone' => 'bi-cash-coin',
                'couleur' => 'info',
                'baseline' => 'Enregistrement des paiements, reçus et suivi des étudiants débiteurs.',
                'fonctionnalites' => ['Enregistrer un paiement', 'Générer un reçu', 'Étudiants débiteurs'],
                'roles' => [Role::AgentComptable],
            ],
            'recouvrement' => [
                'cle' => 'recouvrement',
                'label' => 'Recouvrement',
                'famille' => 'Gestion',
                'icone' => 'bi-journal-text',
                'couleur' => 'warning',
                'baseline' => 'Suivi des impayés, engagements de paiement et relances des étudiants.',
                'fonctionnalites' => ['Impayés', 'Engagements de paiement', 'Relances', 'Statistiques'],
                'roles' => [Role::AgentRecouvrement],
            ],
            'finances' => [
                'cle' => 'finances',
                'label' => 'Finances',
                'famille' => 'Gestion',
                'icone' => 'bi-graph-up-arrow',
                'couleur' => 'secondary',
                'baseline' => 'Supervision financière, validation des opérations et rapports.',
                'fonctionnalites' => ['Tous les paiements', 'Rapports financiers', 'Statistiques'],
                'roles' => [Role::ResponsableFinancier],
            ],
        ];
    }

    /** Récupère un espace par sa clé, avec repli sur un espace générique. */
    public static function get(?string $cle): array
    {
        return self::all()[$cle] ?? self::generique();
    }

    /** Espaces regroupés par famille (Pédagogie, Gestion) pour l'affichage. */
    public static function parFamille(): array
    {
        $groupes = [];
        foreach (self::all() as $espace) {
            $groupes[$espace['famille']][] = $espace;
        }

        return $groupes;
    }

    /** Espace générique (clé absente ou invalide). */
    public static function generique(): array
    {
        return [
            'cle' => 'generique',
            'label' => 'Connexion',
            'famille' => 'Gestion',
            'icone' => 'bi-box-arrow-in-right',
            'couleur' => 'primary',
            'baseline' => 'Accédez à votre espace avec vos identifiants.',
            'fonctionnalites' => [],
            'roles' => [],
        ];
    }
}
