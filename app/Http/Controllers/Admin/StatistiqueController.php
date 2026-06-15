<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    /** Effectif total de l'université (toutes filières), à afficher indépendamment du nombre de comptes créés dans l'application. */
    private const EFFECTIF_TOTAL_ETUDIANTS = 2070;

    public function index(): View
    {
        $stats = [
            'nb_utilisateurs' => User::count(),
            'nb_etudiants' => self::EFFECTIF_TOTAL_ETUDIANTS,
            'total_paiements' => Paiement::sum('montant'),
            'total_impayes' => Etudiant::sum('solde'),
            'nb_debiteurs' => Etudiant::where('solde', '>', 0)->count(),
        ];

        $parRole = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        $parFiliere = Etudiant::query()
            ->selectRaw('filiere, count(*) as total')
            ->groupBy('filiere')
            ->orderByDesc('total')
            ->pluck('total', 'filiere');

        return view('admin.statistiques.index', [
            'stats' => $stats,
            'parRole' => $parRole,
            'parFiliere' => $parFiliere,
        ]);
    }
}
