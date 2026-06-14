<?php

namespace App\Http\Controllers\Financier;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\View\View;

class RapportController extends Controller
{
    public function index(): View
    {
        $parMode = Paiement::query()
            ->selectRaw('mode_paiement, count(*) as nb, sum(montant) as total')
            ->groupBy('mode_paiement')
            ->get();

        $parMois = Paiement::query()
            ->selectRaw("strftime('%Y-%m', date_paiement) as mois, count(*) as nb, sum(montant) as total")
            ->groupBy('mois')
            ->orderByDesc('mois')
            ->get();

        return view('financier.rapports.index', [
            'parMode' => $parMode,
            'parMois' => $parMois,
            'total' => Paiement::sum('montant'),
        ]);
    }
}
