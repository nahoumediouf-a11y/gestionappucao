<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $stats = [
            'nb_debiteurs' => Etudiant::where('solde', '>', 0)->count(),
            'total_impayes' => Etudiant::sum('solde'),
            'nb_engagements' => EngagementPaiement::count(),
            'total_engagements' => EngagementPaiement::sum('montant'),
        ];

        $parStatut = EngagementPaiement::query()
            ->selectRaw('statut, count(*) as total')
            ->groupBy('statut')
            ->pluck('total', 'statut');

        return view('recouvrement.statistiques.index', [
            'stats' => $stats,
            'parStatut' => $parStatut,
        ]);
    }
}
