<?php

namespace App\Http\Controllers\Financier;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RapportController extends Controller
{
    public function index(): View
    {
        return view('financier.rapports.index', $this->donnees());
    }

    public function telecharger(): Response
    {
        $pdf = Pdf::loadView('financier.rapports.pdf', $this->donnees());

        return $pdf->download('rapport-financier-'.now()->format('Y-m-d').'.pdf');
    }

    /**
     * @return array{parMode: \Illuminate\Support\Collection, parMois: \Illuminate\Support\Collection, total: float}
     */
    private function donnees(): array
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

        return [
            'parMode' => $parMode,
            'parMois' => $parMois,
            'total' => Paiement::sum('montant'),
        ];
    }
}
