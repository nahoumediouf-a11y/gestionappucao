<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();

        $modules = match ($user->role) {
            Role::Administrateur => [
                ['label' => 'Gérer les utilisateurs', 'icon' => 'bi-people-fill', 'color' => 'success', 'route' => 'admin.utilisateurs.index'],
                ['label' => 'Gestion des salles et EDT', 'icon' => 'bi-door-open', 'color' => 'primary', 'route' => 'admin.emploi-du-temps.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'color' => 'dark', 'route' => 'admin.statistiques'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'admin.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
            Role::AgentComptable => [
                ['label' => 'Paiements', 'icon' => 'bi-cash-coin', 'color' => 'primary', 'route' => 'comptabilite.paiements.index'],
                ['label' => 'Enregistrer un paiement', 'icon' => 'bi-plus-circle', 'color' => 'primary', 'route' => 'comptabilite.paiements.create'],
                ['label' => 'Étudiants débiteurs', 'icon' => 'bi-exclamation-triangle', 'color' => 'danger', 'route' => 'comptabilite.debiteurs.index'],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
            Role::AgentRecouvrement => [
                ['label' => 'Impayés', 'icon' => 'bi-journal-text', 'color' => 'warning', 'route' => 'recouvrement.impayes.index'],
                ['label' => 'Rechercher un étudiant', 'icon' => 'bi-search', 'color' => 'info', 'route' => 'recouvrement.recherche.index'],
                ['label' => 'Engagements de paiement', 'icon' => 'bi-file-earmark-text', 'color' => 'primary', 'route' => 'recouvrement.engagements.index'],
                ['label' => 'Relances', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'recouvrement.relances.index'],
                ['label' => 'Statistiques de recouvrement', 'icon' => 'bi-graph-up-arrow', 'color' => 'dark', 'route' => 'recouvrement.statistiques'],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
            Role::ResponsableFinancier => [
                ['label' => 'Tous les paiements', 'icon' => 'bi-receipt', 'color' => 'info', 'route' => 'financier.paiements.index'],
                ['label' => 'Rapports financiers', 'icon' => 'bi-file-earmark-bar-graph', 'color' => 'secondary', 'route' => 'financier.rapports.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'color' => 'dark', 'route' => 'financier.statistiques'],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
            Role::Etudiant => [
                ['label' => 'Mon profil', 'icon' => 'bi-person-circle', 'color' => 'primary', 'route' => 'etudiant.profil.index'],
                ['label' => 'Mes notes', 'icon' => 'bi-journal-check', 'color' => 'success', 'route' => 'etudiant.notes.index'],
                ['label' => 'Mon bulletin', 'icon' => 'bi-file-earmark-text', 'color' => 'secondary', 'route' => 'etudiant.bulletin.index'],
                ['label' => 'Mes absences', 'icon' => 'bi-calendar-x', 'color' => 'warning', 'route' => 'etudiant.absences.index'],
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'color' => 'info', 'route' => 'etudiant.edt.index'],
                ['label' => 'Projets de classe', 'icon' => 'bi-kanban', 'color' => 'primary', 'route' => 'etudiant.projets.index'],
                ['label' => 'Documents de cours', 'icon' => 'bi-file-earmark-arrow-down', 'color' => 'secondary', 'route' => 'etudiant.documents.index'],
                ['label' => 'Suivi de paiement', 'icon' => 'bi-cash-coin', 'color' => 'dark', 'route' => 'etudiant.paiements.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'etudiant.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
            Role::Professeur => [
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'color' => 'info', 'route' => 'professeur.edt.index'],
                ['label' => 'Liste des étudiants', 'icon' => 'bi-people', 'color' => 'primary', 'route' => 'professeur.etudiants.index'],
                ['label' => 'Notes', 'icon' => 'bi-journal-check', 'color' => 'success', 'route' => 'professeur.notes.index'],
                ['label' => 'Absences', 'icon' => 'bi-calendar-x', 'color' => 'warning', 'route' => 'professeur.absences.index'],
                ['label' => 'Projets de classe', 'icon' => 'bi-kanban', 'color' => 'primary', 'route' => 'professeur.projets.index'],
                ['label' => 'Documents de cours', 'icon' => 'bi-cloud-upload', 'color' => 'secondary', 'route' => 'professeur.documents.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'professeur.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
        };

        return view('dashboard.index', [
            'user' => $user,
            'modules' => $modules,
        ]);
    }
}
