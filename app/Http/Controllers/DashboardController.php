<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Absence;
use App\Models\CoursEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\Projet;
use App\Models\Soumission;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Correspondance jour Carbon (0=dimanche) → libellé de l'emploi du temps. */
    private const JOURS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];

    public function index(): View
    {
        $user = auth()->user();

        $modules = match ($user->role) {
            Role::Administrateur => [
                ['label' => 'Gérer les utilisateurs', 'icon' => 'bi-people-fill', 'color' => 'success', 'route' => 'admin.utilisateurs.index'],
                ['label' => 'Rechercher un étudiant', 'icon' => 'bi-search', 'color' => 'info', 'route' => 'admin.recherche.index'],
                ['label' => 'Gestion des salles et EDT', 'icon' => 'bi-door-open', 'color' => 'primary', 'route' => 'admin.emploi-du-temps.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'color' => 'info', 'route' => 'admin.cours-en-ligne.index'],
                ['label' => 'Statistiques', 'icon' => 'bi-graph-up-arrow', 'color' => 'dark', 'route' => 'admin.statistiques'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'admin.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Journal des activités', 'icon' => 'bi-clock-history', 'color' => 'secondary', 'route' => 'admin.activity-logs.index'],
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
                ['label' => 'Étudiants à jour', 'icon' => 'bi-check-circle', 'color' => 'success', 'route' => 'recouvrement.ajour.index'],
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
            Role::Etudiant => array_filter([
                $user->etudiant?->solde > 0 ? [
                    'label' => 'Payer ma scolarité',
                    'icon' => 'bi-credit-card-fill',
                    'color' => 'danger',
                    'url' => route('etudiant.paiements.index').'#payer-scolarite',
                    'badge' => number_format((float) $user->etudiant->solde, 0, ',', ' ').' FCFA',
                    'highlight' => true,
                ] : null,
                ['label' => 'Mon profil', 'icon' => 'bi-person-circle', 'color' => 'primary', 'route' => 'etudiant.profil.index'],
                ['label' => 'Mes notes', 'icon' => 'bi-journal-check', 'color' => 'success', 'route' => 'etudiant.notes.index'],
                ['label' => 'Mon bulletin', 'icon' => 'bi-file-earmark-text', 'color' => 'secondary', 'route' => 'etudiant.bulletin.index'],
                ['label' => 'Mes absences', 'icon' => 'bi-calendar-x', 'color' => 'warning', 'route' => 'etudiant.absences.index'],
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'color' => 'info', 'route' => 'etudiant.edt.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'color' => 'success', 'route' => 'etudiant.cours.index'],
                ['label' => 'Projets, devoirs & examens', 'icon' => 'bi-kanban', 'color' => 'primary', 'route' => 'etudiant.projets.index'],
                ['label' => 'Proposer un projet perso', 'icon' => 'bi-lightbulb', 'color' => 'warning', 'route' => 'etudiant.propositions.index'],
                ['label' => 'Documents de cours', 'icon' => 'bi-file-earmark-arrow-down', 'color' => 'secondary', 'route' => 'etudiant.documents.index'],
                ['label' => 'Cours des professeurs', 'icon' => 'bi-file-earmark-richtext', 'color' => 'primary', 'route' => 'etudiant.documents_cours.index'],
                ['label' => 'Suivi de paiement', 'icon' => 'bi-cash-coin', 'color' => 'dark', 'route' => 'etudiant.paiements.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'etudiant.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ]),
            Role::Professeur => [
                ['label' => 'Mon espace enseignant', 'icon' => 'bi-grid-1x2', 'color' => 'success', 'route' => 'professeur.espace'],
                ['label' => 'Emploi du temps', 'icon' => 'bi-calendar3', 'color' => 'info', 'route' => 'professeur.edt.index'],
                ['label' => 'Cours en ligne', 'icon' => 'bi-camera-video', 'color' => 'success', 'route' => 'professeur.cours.index'],
                ['label' => 'Liste des étudiants', 'icon' => 'bi-people', 'color' => 'primary', 'route' => 'professeur.etudiants.index'],
                ['label' => 'Notes', 'icon' => 'bi-journal-check', 'color' => 'success', 'route' => 'professeur.notes.index'],
                ['label' => 'Absences', 'icon' => 'bi-calendar-x', 'color' => 'warning', 'route' => 'professeur.absences.index'],
                ['label' => 'Projets de classe', 'icon' => 'bi-kanban', 'color' => 'primary', 'route' => 'professeur.projets.index'],
                ['label' => 'Propositions d\'étudiants', 'icon' => 'bi-lightbulb', 'color' => 'warning', 'route' => 'professeur.propositions.index'],
                ['label' => 'Documents de cours', 'icon' => 'bi-cloud-upload', 'color' => 'secondary', 'route' => 'professeur.documents.index'],
                ['label' => 'Publier un cours', 'icon' => 'bi-file-earmark-richtext', 'color' => 'primary', 'route' => 'professeur.documents_cours.index'],
                ['label' => 'Notifications', 'icon' => 'bi-bell', 'color' => 'danger', 'route' => 'professeur.notifications.index', 'badge' => $user->unreadNotifications()->count() ?: null],
                ['label' => 'Assistant IA', 'icon' => 'bi-robot', 'color' => 'info', 'route' => 'assistant.index'],
            ],
        };

        $roleStats = [Role::Administrateur, Role::ResponsableFinancier];

        return view('dashboard.index', [
            'user' => $user,
            'modules' => $modules,
            'apercu' => $user->role === Role::Etudiant ? $this->apercuEtudiant($user) : null,
            'stats' => in_array($user->role, $roleStats, true) ? $this->statsGestion() : null,
        ]);
    }

    /** Indicateurs et données de graphiques pour les tableaux de bord de pilotage. */
    private function statsGestion(): array
    {
        // 6 derniers mois (clé AAAA-MM => libellé court).
        $mois = collect(range(5, 0))->mapWithKeys(function ($i) {
            $d = Carbon::now()->startOfMonth()->subMonths($i);

            return [$d->format('Y-m') => $d->locale('fr')->isoFormat('MMM YY')];
        });

        $paiementsParMois = Paiement::where('statut', 'valide')
            ->where('date_paiement', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->get(['montant', 'date_paiement'])
            ->groupBy(fn ($p) => Carbon::parse($p->date_paiement)->format('Y-m'))
            ->map(fn ($g) => (float) $g->sum('montant'));

        $absencesParMois = Absence::where('date', '>=', Carbon::now()->startOfMonth()->subMonths(5))
            ->get(['date'])
            ->groupBy(fn ($a) => Carbon::parse($a->date)->format('Y-m'))
            ->map(fn ($g) => $g->count());

        $etudiants = Etudiant::get(['filiere', 'niveau']);
        $totalPaye = (float) Paiement::where('statut', 'valide')->sum('montant');
        $totalAttendu = $etudiants->sum(fn ($e) => $e->scolariteTotale());

        $parFiliere = $etudiants->groupBy('filiere')->map->count()->sortDesc();

        $debutMois = Carbon::now()->startOfMonth();

        return [
            'cartes' => [
                'etudiants' => $etudiants->count(),
                'professeurs' => User::where('role', Role::Professeur->value)->count(),
                'paiementsMois' => (float) Paiement::where('statut', 'valide')->where('date_paiement', '>=', $debutMois)->sum('montant'),
                'tauxRecouvrement' => $totalAttendu > 0 ? min(100, round($totalPaye / $totalAttendu * 100)) : 0,
            ],
            'paiements' => [
                'labels' => $mois->values(),
                'valeurs' => $mois->keys()->map(fn ($k) => $paiementsParMois[$k] ?? 0),
            ],
            'absences' => [
                'labels' => $mois->values(),
                'valeurs' => $mois->keys()->map(fn ($k) => $absencesParMois[$k] ?? 0),
            ],
            'filieres' => [
                'labels' => $parFiliere->keys(),
                'valeurs' => $parFiliere->values(),
            ],
        ];
    }

    /** Données de synthèse affichées en tête du tableau de bord étudiant. */
    private function apercuEtudiant($user): ?array
    {
        $etudiant = $user->etudiant;

        if (! $etudiant) {
            return null;
        }

        $jour = self::JOURS[now()->dayOfWeek] ?? null;
        $heure = now()->format('H:i');

        // Prochaine séance d'aujourd'hui encore à venir/en cours.
        $prochaineSeance = $jour
            ? EmploiDuTemps::where('filiere', $etudiant->filiere)
                ->where('niveau', $etudiant->niveau)
                ->where('jour', $jour)
                ->where('heure_fin', '>=', $heure)
                ->orderBy('heure_debut')
                ->first()
            : null;

        // Cours en ligne actuellement en cours pour sa classe.
        $coursEnCours = CoursEnLigne::pourClasse($etudiant->filiere, $etudiant->niveau)
            ->where('statut', 'en_cours')
            ->orderBy('debut_prevu')
            ->first();

        // Prochaine échéance de travail.
        $prochaineEcheance = Projet::where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->whereDate('date_limite', '>=', now()->toDateString())
            ->orderBy('date_limite')
            ->first();

        // Travaux à rendre (en ligne, échéance à venir) sans soumission de l'étudiant.
        $idsRendus = Soumission::where('etudiant_id', $etudiant->id)->pluck('projet_id');
        $aRendre = Projet::where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->where('rendu_en_ligne', true)
            ->whereDate('date_limite', '>=', now()->toDateString())
            ->whereNotIn('id', $idsRendus)
            ->count();

        return [
            'solde' => $etudiant->soldeReel(),
            'moyenne' => $etudiant->moyenne(),
            'prochaineSeance' => $prochaineSeance,
            'coursEnCours' => $coursEnCours,
            'prochaineEcheance' => $prochaineEcheance,
            'aRendre' => $aRendre,
        ];
    }
}
