<?php

namespace App\Http\Controllers\Financier;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use App\Models\Paiement;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        // KPIs globaux
        $totalEncaisse    = Paiement::where('statut', 'valide')->sum('montant');
        $totalImpayes     = Etudiant::where('solde', '>', 0)->sum('solde');
        $nbPaiements      = Paiement::count();
        $nbValides        = Paiement::where('statut', 'valide')->count();
        $nbEnAttente      = Paiement::where('statut', 'en_attente_validation')->count();
        $nbDebiteurs      = Etudiant::where('solde', '>', 0)->count();
        $nbAJour          = Etudiant::where('solde', '<=', 0)->count();
        $tauxRecouvrement = ($totalEncaisse + $totalImpayes) > 0
            ? round($totalEncaisse / ($totalEncaisse + $totalImpayes) * 100, 1)
            : 0;

        // Encaissements par mois (6 derniers mois)
        $parMois = Paiement::where('statut', 'valide')
            ->selectRaw("strftime('%Y-%m', date_paiement) as mois, SUM(montant) as total, COUNT(*) as nb")
            ->groupBy('mois')
            ->orderBy('mois')
            ->get();

        // Par mode de paiement
        $parMode = Paiement::where('statut', 'valide')
            ->selectRaw('mode_paiement, SUM(montant) as total, COUNT(*) as nb')
            ->groupBy('mode_paiement')
            ->orderByDesc('total')
            ->get();

        // Impayés par filière
        $parFiliere = Etudiant::selectRaw('filiere, COUNT(*) as nb_etudiants, SUM(solde) as total_impayes, SUM(CASE WHEN solde <= 0 THEN 1 ELSE 0 END) as nb_a_jour')
            ->groupBy('filiere')
            ->orderByDesc('total_impayes')
            ->get();

        // Top 5 débiteurs
        $topDebiteurs = Etudiant::with('user')
            ->where('solde', '>', 0)
            ->orderByDesc('solde')
            ->limit(5)
            ->get();

        // Évolution statuts paiements
        $statuts = Paiement::selectRaw('statut, COUNT(*) as nb, SUM(montant) as total')
            ->groupBy('statut')
            ->get()
            ->keyBy('statut');

        return view('financier.statistiques.index', compact(
            'totalEncaisse', 'totalImpayes', 'nbPaiements', 'nbValides',
            'nbEnAttente', 'nbDebiteurs', 'nbAJour', 'tauxRecouvrement',
            'parMois', 'parMode', 'parFiliere', 'topDebiteurs', 'statuts'
        ));
    }
}
