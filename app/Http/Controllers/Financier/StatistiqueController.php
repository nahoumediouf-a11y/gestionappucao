<?php

namespace App\Http\Controllers\Financier;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use App\Models\Paiement;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $stats = [
            'total_encaisse' => Paiement::sum('montant'),
            'nb_paiements' => Paiement::count(),
            'nb_paiements_valides' => Paiement::whereNotNull('valide_par')->count(),
            'total_impayes' => Etudiant::sum('solde'),
            'nb_debiteurs' => Etudiant::where('solde', '>', 0)->count(),
            'nb_engagements_en_cours' => EngagementPaiement::whereIn('statut', ['en_attente', 'relance'])->count(),
        ];

        return view('financier.statistiques.index', [
            'stats' => $stats,
        ]);
    }
}
