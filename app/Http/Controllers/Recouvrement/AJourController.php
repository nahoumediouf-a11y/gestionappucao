<?php

namespace App\Http\Controllers\Recouvrement;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\View\View;

class AJourController extends Controller
{
    public function index(): View
    {
        $etudiants = Etudiant::with(['user', 'paiements' => fn($q) => $q->where('statut', 'valide')->orderByDesc('date_paiement')])
            ->where('solde', '<=', 0)
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('recouvrement.ajour.index', compact('etudiants'));
    }
}
