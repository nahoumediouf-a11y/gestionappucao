<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\Paiement;
use App\Support\ActivityLogger;
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
            ->paginate(15)
            ->withQueryString();

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
            'mode_paiement' => ['required', 'string', 'in:'.implode(',', array_keys(\App\Models\Paiement::MODES))],
            'date_paiement' => ['required', 'date'],
            'statut' => ['required', 'string', 'in:'.implode(',', array_keys(\App\Models\Paiement::STATUTS))],
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

    public function valider(Paiement $paiement): RedirectResponse
    {
        abort_unless($paiement->statut === 'en_attente_validation', 422);

        $paiement->update([
            'statut'     => 'valide',
            'agent_id'   => auth()->id(),
            'valide_par' => auth()->id(),
            'valide_le'  => now(),
        ]);

        $etudiant = $paiement->etudiant;
        $etudiant->decrement('solde', $paiement->montant);
        $etudiant->refresh();

        // Notifier l'étudiant
        $msgSolde = $etudiant->solde <= 0
            ? 'Votre scolarité est entièrement réglée. Félicitations !'
            : 'Solde restant : '.number_format($etudiant->solde, 0, ',', ' ').' FCFA.';

        $etudiant->user->notifications()->create([
            'id'   => \Illuminate\Support\Str::uuid(),
            'type' => 'App\\Notifications\\PaiementValide',
            'data' => [
                'titre'   => 'Paiement validé — '.$paiement->reference,
                'message' => 'Votre paiement de '.number_format($paiement->montant, 0, ',', ' ').' FCFA a été validé par la comptabilité. '.$msgSolde,
                'recu_url' => route('etudiant.paiements.recu', $paiement),
            ],
            'read_at' => null,
        ]);

        return redirect()->route('comptabilite.paiements.recu', $paiement)
            ->with('success', 'Paiement '.$paiement->reference.' validé. Reçu généré.');
    }

    public function rejeter(Paiement $paiement): RedirectResponse
    {
        abort_unless($paiement->statut === 'en_attente_validation', 422);

        $paiement->update(['statut' => 'annule']);

        return back()->with('success', 'Déclaration '.$paiement->reference.' rejetée.');
    }

    private function validatePaiement(Request $request): array
    {
        return $request->validate([
            'etudiant_id' => ['required', 'exists:etudiants,id'],
            'montant' => ['required', 'numeric', 'min:1'],
            'mode_paiement' => ['required', 'string', 'in:'.implode(',', array_keys(\App\Models\Paiement::MODES))],
            'date_paiement' => ['required', 'date'],
            'reference' => ['required', 'string', 'unique:paiements,reference'],
        ]);
    }
}
