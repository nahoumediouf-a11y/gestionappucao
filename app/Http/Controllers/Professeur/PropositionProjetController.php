<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Models\PropositionProjet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropositionProjetController extends Controller
{
    public function index(): View
    {
        $propositions = PropositionProjet::with('etudiant.user')
            ->orderByRaw("CASE statut WHEN 'en_attente' THEN 0 ELSE 1 END")
            ->orderByDesc('created_at')
            ->get();

        return view('professeur.propositions.index', [
            'propositions' => $propositions,
        ]);
    }

    public function traiter(Request $request, PropositionProjet $proposition): RedirectResponse
    {
        $validated = $request->validate([
            'statut' => ['required', 'in:accepte,refuse'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ]);

        $proposition->update([
            'statut' => $validated['statut'],
            'commentaire' => $validated['commentaire'] ?? null,
            'traite_par' => auth()->id(),
        ]);

        $label = $validated['statut'] === 'accepte' ? 'acceptée' : 'refusée';

        return back()->with('success', "La proposition a été {$label}.");
    }
}
