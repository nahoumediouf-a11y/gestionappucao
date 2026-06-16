<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class PaiementController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        return view('etudiant.paiements.index', [
            'etudiant' => $etudiant,
            'paiements' => $etudiant->paiements()->orderByDesc('date_paiement')->get(),
            'engagements' => $etudiant->engagements()->orderByDesc('echeance')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $etudiant = auth()->user()->etudiant;

        $validated = $request->validate([
            'montant'        => ['required', 'numeric', 'min:1', 'max:'.(float) $etudiant->solde],
            'mode_paiement'  => ['required', 'string', 'in:'.implode(',', array_keys(Paiement::MODES))],
            'reference'      => ['required', 'string', 'unique:paiements,reference'],
            'numero_mobile'  => ['nullable', 'string', 'max:20'],
            'note_etudiant'  => ['nullable', 'string', 'max:500'],
        ], [
            'montant.max' => 'Le montant ne peut pas dépasser votre solde restant (:max FCFA).',
        ]);

        Paiement::create([
            'etudiant_id'    => $etudiant->id,
            'date_paiement'  => now()->toDateString(),
            'montant'        => $validated['montant'],
            'mode_paiement'  => $validated['mode_paiement'],
            'reference'      => $validated['reference'],
            'numero_mobile'  => $validated['numero_mobile'] ?? null,
            'note_etudiant'  => $validated['note_etudiant'] ?? null,
            'statut'         => 'en_attente_validation',
        ]);

        return back()->with('success', 'Déclaration envoyée avec succès. Elle sera validée par le service comptable.');
    }

    public function recu(Paiement $paiement): View
    {
        abort_unless($paiement->etudiant_id === auth()->user()->etudiant->id, Response::HTTP_FORBIDDEN);

        $paiement->load(['etudiant.user', 'agent']);

        return view('comptabilite.paiements.recu', [
            'paiement' => $paiement,
        ]);
    }
}
