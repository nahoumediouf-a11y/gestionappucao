<?php

namespace App\Http\Controllers\Financier;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function index(): View
    {
        $paiements = Paiement::with(['etudiant.user', 'agent', 'validePar'])
            ->orderByDesc('date_paiement')
            ->get();

        return view('financier.paiements.index', [
            'paiements' => $paiements,
        ]);
    }

    public function valider(Paiement $paiement): RedirectResponse
    {
        $paiement->update([
            'valide_par' => auth()->id(),
            'valide_le' => now(),
        ]);

        return back()->with('success', 'Paiement '.$paiement->reference.' validé avec succès.');
    }
}
