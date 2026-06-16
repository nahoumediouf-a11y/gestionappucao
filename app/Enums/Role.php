<?php

namespace App\Enums;

enum Role: string
{
    case Administrateur = 'administrateur';
    case AgentComptable = 'agent_comptable';
    case AgentRecouvrement = 'agent_recouvrement';
    case ResponsableFinancier = 'responsable_financier';
    case Etudiant = 'etudiant';
    case Professeur = 'professeur';

    public function label(): string
    {
        return match ($this) {
            self::Administrateur => 'Administrateur',
            self::AgentComptable => 'Agent comptable',
            self::AgentRecouvrement => 'Agent de recouvrement',
            self::ResponsableFinancier => 'Responsable financier',
            self::Etudiant => 'Étudiant',
            self::Professeur => 'Professeur',
        };
    }

    /** Cas d'utilisation autorisés selon le diagramme UML */
    public function permissions(): array
    {
        return match ($this) {
            self::Administrateur => [
                'gerer_utilisateurs',
                'ajouter_etudiant',
                'modifier_etudiant',
                'supprimer_etudiant',
                'gerer_roles',
                'consulter_toutes_infos',
                'corriger_donnees',
                'consulter_statistiques',
            ],
            self::AgentComptable => [
                'enregistrer_paiement',
                'modifier_paiement',
                'generer_recu',
                'consulter_historique_paiements',
                'consulter_debiteurs',
            ],
            self::AgentRecouvrement => [
                'consulter_impayes',
                'rechercher_etudiant',
                'generer_engagement',
                'suivre_paiements',
                'envoyer_relances',
                'consulter_statistiques_recouvrement',
            ],
            self::ResponsableFinancier => [
                'consulter_tous_paiements',
                'valider_operations',
                'consulter_rapports_financiers',
                'superviser_recouvrement',
                'consulter_statistiques',
            ],
            self::Etudiant => [
                'consulter_profil',
                'consulter_notes',
                'consulter_moyenne',
                'consulter_bulletin',
                'consulter_edt',
                'consulter_absences',
                'consulter_suivi_paiement',
                'consulter_solde',
                'declarer_paiement',
                'choisir_mode_paiement',
                'telecharger_recus',
            ],
            self::Professeur => [
                'consulter_edt',
                'saisir_notes',
                'modifier_notes',
                'enregistrer_absences',
                'consulter_liste_etudiants',
            ],
        };
    }
}
