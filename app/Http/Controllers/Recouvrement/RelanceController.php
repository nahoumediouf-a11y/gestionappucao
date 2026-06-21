<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use App\Notifications\RelancePaiementNotification;
use App\Support\ActivityLogger;
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
        $engagement->load('etudiant.user');
        $engagement->update(['statut' => 'relance']);

        $engagement->etudiant->user->notify(new RelancePaiementNotification($engagement));

        ActivityLogger::log(
            'relance_paiement',
            'Relance envoyée à '.$engagement->etudiant->user->nom_complet.' pour l\'engagement échu du '.$engagement->echeance->format('d/m/Y').'.'
        );

        return back()->with('success', 'Relance envoyée à '.$engagement->etudiant->user->nom_complet.'.');
    }
}
