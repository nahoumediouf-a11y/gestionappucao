<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Models\User;
use Illuminate\View\View;

class StatistiqueController extends Controller
{
    public function index(): View
    {
        $stats = [
            'nb_utilisateurs' => User::count(),
            'nb_etudiants' => Etudiant::count(),
            'total_paiements' => Paiement::sum('montant'),
            'total_impayes' => Etudiant::sum('solde'),
            'nb_debiteurs' => Etudiant::where('solde', '>', 0)->count(),
        ];

        $parRole = User::query()
            ->selectRaw('role, count(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.statistiques.index', [
            'stats' => $stats,
            'parRole' => $parRole,
        ]);
    }
}
