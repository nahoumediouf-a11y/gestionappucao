<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaiementController extends Controller
{
    public function index(Request $request): View
    {
        $paiements = Paiement::with(['etudiant.user', 'agent'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->whereHas('etudiant', function ($q) use ($term) {
                    $q->where('matricule', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', "%{$term}%")->orWhere('prenom', 'like', "%{$term}%"));
                });
            })
            ->orderByDesc('date_paiement')
            ->get();

        return view('comptabilite.paiements.index', [
            'paiements' => $paiements,
            'q' => $request->string('q'),
        ]);
    }

    public function create(): View
    {
        return view('comptabilite.paiements.create', [
            'etudiants' => Etudiant::with('user')->orderBy('matricule')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePaiement($request);

        $etudiant = Etudiant::findOrFail($validated['etudiant_id']);

        $paiement = Paiement::create([
            'etudiant_id' => $etudiant->id,
            'agent_id' => auth()->id(),
            'date_paiement' => $validated['date_paiement'],
            'montant' => $validated['montant'],
            'mode_paiement' => $validated['mode_paiement'],
            'reference' => $validated['reference'],
            'statut' => 'valide',
        ]);

        $etudiant->decrement('solde', $validated['montant']);

        return redirect()->route('comptabilite.paiements.recu', $paiement)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function edit(Paiement $paiement): View
    {
        $paiement->load('etudiant.user');

        return view('comptabilite.paiements.edit', [
            'paiement' => $paiement,
        ]);
    }

    public function update(Request $request, Paiement $paiement): RedirectResponse
    {
        $validated = $request->validate([
            'montant' => ['required', 'numeric', 'min:0'],
            'mode_paiement' => ['required', 'string'],
            'date_paiement' => ['required', 'date'],
            'statut' => ['required', 'string', 'in:valide,annule'],
        ]);

        $etudiant = $paiement->etudiant;

        // Réajuster le solde : on annule l'ancien montant puis on applique le nouveau
        $etudiant->increment('solde', $paiement->montant);
        $etudiant->decrement('solde', $validated['montant']);

        $paiement->update($validated);

        return redirect()->route('comptabilite.paiements.index')->with('success', 'Paiement modifié avec succès.');
    }

    public function recu(Paiement $paiement): View
    {
        $paiement->load(['etudiant.user', 'agent']);

        return view('comptabilite.paiements.recu', [
            'paiement' => $paiement,
        ]);
    }

    private function validatePaiement(Request $request): array
    {
        return $request->validate([
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement' => ['required', 'string', 'in:especes,virement,cheque,mobile_money'],
            'date_paiement' => ['required', 'date'],
            'reference' => ['required', 'string', 'unique:paiements,reference'],
        ]);
    }
}
