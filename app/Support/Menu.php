<?php

namespace App\Support;

use App\Enums\Role;
use App\Models\User;

/**
 * Menu de navigation latéral par rôle. Chaque entrée : label, icône (Bootstrap),
 * nom de route. Les routes inexistantes pour un rôle ne sont pas listées ici.
 */
class Menu
{
    /** @return array<int, array{label: string, icon: string, route: string}> */
    public static function pour(User $user): array
    {
        $tableau = ['label' => 'Tableau de bord', 'icon' => 'bi-grid-1x2', 'route' => 'dashboard'];
        $assistant = ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'route' => 'assistant.index'];
        $compte = ['label' => 'Mon compte', 'icon' => 'bi-person-circle', 'route' => 'compte.show'];

        $items = match ($user->role) {
            Role::Administrateur => [
                $tableau,
                ['label' => 'Utilisateurs', 'icon' => 'bi-people', 'route' => 'admin.utilisateurs.index'],
                ['label' => 'Recherche', 'icon' => 'bi-search', 'route' => 'admin.recherche.index'],
                ['label' => 'Emplois du temps', 'icon' => 'bi-calendar3', 'route' => 'admin.emploi-du-temps.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'route' => 'admin.cours-en-ligne.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'route' => 'admin.statistiques'],
                ['label' => 'Journal d\'activité', 'icon' => 'bi-clock-history', 'route' => 'admin.activity-logs.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'admin.notifications.index'],
            ],
            Role::AgentComptable => [
                $tableau,
                ['label' => 'Paiements', 'icon' => 'bi-cash-coin', 'route' => 'comptabilite.paiements.index'],
                ['label' => 'Enregistrer un paiement', 'icon' => 'bi-plus-circle', 'route' => 'comptabilite.paiements.create'],
                ['label' => 'Débiteurs', 'icon' => 'bi-exclamation-triangle', 'route' => 'comptabilite.debiteurs.index'],
            ],
            Role::AgentRecouvrement => [
                $tableau,
                ['label' => 'Impayés', 'icon' => 'bi-journal-text', 'route' => 'recouvrement.impayes.index'],
                ['label' => 'Étudiants à jour', 'icon' => 'bi-check-circle', 'route' => 'recouvrement.ajour.index'],
                ['label' => 'Recherche', 'icon' => 'bi-search', 'route' => 'recouvrement.recherche.index'],
                ['label' => 'Engagements', 'icon' => 'bi-file-earmark-text', 'route' => 'recouvrement.engagements.index'],
                ['label' => 'Relances', 'icon' => 'bi-megaphone', 'route' => 'recouvrement.relances.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'route' => 'recouvrement.statistiques'],
            ],
            Role::ResponsableFinancier => [
                $tableau,
                ['label' => 'Tous les paiements', 'icon' => 'bi-receipt', 'route' => 'financier.paiements.index'],
                ['label' => 'Rapports', 'icon' => 'bi-file-earmark-bar-graph', 'route' => 'financier.rapports.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'route' => 'financier.statistiques'],
            ],
            Role::Etudiant => [
                $tableau,
                ['label' => 'Mes notes', 'icon' => 'bi-journal-check', 'route' => 'etudiant.notes.index'],
                ['label' => 'Mon bulletin', 'icon' => 'bi-file-earmark-text', 'route' => 'etudiant.bulletin.index'],
                ['label' => 'Mes absences', 'icon' => 'bi-calendar-x', 'route' => 'etudiant.absences.index'],
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'route' => 'etudiant.edt.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'route' => 'etudiant.cours.index'],
                ['label' => 'Projets & examens', 'icon' => 'bi-kanban', 'route' => 'etudiant.projets.index'],
                ['label' => 'Documents', 'icon' => 'bi-folder2-open', 'route' => 'etudiant.documents.index'],
                ['label' => 'Paiements', 'icon' => 'bi-cash-coin', 'route' => 'etudiant.paiements.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'etudiant.notifications.index'],
            ],
            Role::Professeur => [
                $tableau,
                ['label' => 'Mon espace', 'icon' => 'bi-easel', 'route' => 'professeur.espace'],
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'route' => 'professeur.edt.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'route' => 'professeur.cours.index'],
                ['label' => 'Étudiants', 'icon' => 'bi-people', 'route' => 'professeur.etudiants.index'],
                ['label' => 'Notes', 'icon' => 'bi-journal-check', 'route' => 'professeur.notes.index'],
                ['label' => 'Absences', 'icon' => 'bi-calendar-x', 'route' => 'professeur.absences.index'],
                ['label' => 'Projets & examens', 'icon' => 'bi-kanban', 'route' => 'professeur.projets.index'],
                ['label' => 'Documents', 'icon' => 'bi-cloud-upload', 'route' => 'professeur.documents.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'route' => 'professeur.notifications.index'],
            ],
        };

        $items[] = $assistant;
        $items[] = $compte;

        return $items;
    }
}
