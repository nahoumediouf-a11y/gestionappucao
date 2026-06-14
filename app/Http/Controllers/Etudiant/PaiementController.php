<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Paiement;
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

    public function recu(Paiement $paiement): View
    {
        abort_unless($paiement->etudiant_id === auth()->user()->etudiant->id, Response::HTTP_FORBIDDEN);

        $paiement->load(['etudiant.user', 'agent']);

        return view('comptabilite.paiements.recu', [
            'paiement' => $paiement,
        ]);
    }
}
