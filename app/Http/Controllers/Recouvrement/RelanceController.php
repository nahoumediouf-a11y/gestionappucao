<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RelanceController extends Controller
{
    public function index(): View
    {
        $engagements = EngagementPaiement::with(['etudiant.user'])
            ->whereIn('statut', ['en_attente', 'relance'])
            ->orderBy('echeance')
            ->get();

        return view('recouvrement.relances.index', [
            'engagements' => $engagements,
        ]);
    }

    public function relancer(EngagementPaiement $engagement): RedirectResponse
    {
        $engagement->update(['statut' => 'relance']);

        return back()->with('success', 'Relance enregistrée pour '.$engagement->etudiant->user->nom_complet.'.');
    }
}
