<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EngagementController extends Controller
{
    public function index(): View
    {
        $engagements = EngagementPaiement::with(['etudiant.user', 'agent'])
            ->orderByDesc('echeance')
            ->paginate(20);

        return view('recouvrement.engagements.index', [
            'engagements' => $engagements,
        ]);
    }

    public function show(EngagementPaiement $engagement): View
    {
        $engagement->load(['etudiant.user', 'agent']);

        return view('recouvrement.engagements.show', [
            'engagement' => $engagement,
        ]);
    }

    public function create(): View
    {
        return view('recouvrement.engagements.create', [
            'etudiants' => Etudiant::with('user')->where('solde', '>', 0)->orderBy('matricule')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'date' => ['required', 'date'],
            'echeance' => ['required', 'date', 'after_or_equal:date'],
        ]);

        EngagementPaiement::create([
            ...$validated,
            'agent_id' => auth()->id(),
            'statut' => 'en_attente',
        ]);

        return redirect()->route('recouvrement.engagements.index')->with('success', 'Engagement de paiement créé avec succès.');
    }

    public function edit(EngagementPaiement $engagement): View
    {
        $engagement->load('etudiant.user');

        return view('recouvrement.engagements.edit', [
            'engagement' => $engagement,
        ]);
    }

    public function update(Request $request, EngagementPaiement $engagement): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:1'],
            'echeance' => ['required', 'date'],
            'statut' => ['required', 'string', 'in:en_attente,relance,honore,annule'],
        ]);

        $engagement->update($validated);

        return redirect()->route('recouvrement.engagements.index')->with('success', 'Engagement modifié avec succès.');
    }
}
