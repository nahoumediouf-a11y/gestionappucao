<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\CoursEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\Projet;
use App\Models\PropositionProjet;
use App\Models\Soumission;
use Illuminate\View\View;

class EspaceController extends Controller
{
    use InteractsWithEtudiants;

    /** Correspondance jour Carbon (0=dimanche) → libellé utilisé par l'emploi du temps. */
    private const JOURS = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi', 6 => 'Samedi'];

    public function index(): View
    {
        $profId = auth()->id();
        $jour = self::JOURS[now()->dayOfWeek] ?? null;

        // Séances de l'emploi du temps prévues aujourd'hui.
        $seancesDuJour = $jour
            ? EmploiDuTemps::where('professeur_id', $profId)->where('jour', $jour)
                ->orderBy('heure_debut')->get()
            : collect();

        // Cours en ligne en cours ou à venir.
        $coursEnLigne = CoursEnLigne::where('professeur_id', $profId)
            ->aVenir()
            ->orderByRaw("CASE statut WHEN 'en_cours' THEN 0 ELSE 1 END")
            ->orderBy('debut_prevu')
            ->limit(5)
            ->get();

        // Copies à corriger (soumissions non corrigées sur ses travaux).
        $copiesACorriger = Soumission::whereNull('corrige_a')
            ->whereHas('projet', fn ($q) => $q->where('professeur_id', $profId))
            ->count();

        // Échéances de travaux à venir.
        $echeances = Projet::where('professeur_id', $profId)
            ->whereDate('date_limite', '>=', now()->toDateString())
            ->orderBy('date_limite')
            ->limit(5)
            ->get();

        $propositionsEnAttente = PropositionProjet::where('statut', 'en_attente')->count();

        return view('professeur.espace.index', [
            'classes' => $this->classesDuProfesseur(),
            'seancesDuJour' => $seancesDuJour,
            'coursEnLigne' => $coursEnLigne,
            'copiesACorriger' => $copiesACorriger,
            'echeances' => $echeances,
            'propositionsEnAttente' => $propositionsEnAttente,
        ]);
    }
}
