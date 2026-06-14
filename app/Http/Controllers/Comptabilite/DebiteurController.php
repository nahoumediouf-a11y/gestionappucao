<?php

namespace App\Http\Controllers\Comptabilite;

use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use Illuminate\View\View;

class DebiteurController extends Controller
{
    public function index(): View
    {
        $debiteurs = Etudiant::with('user')
            ->where('solde', '>', 0)
            ->orderByDesc('solde')
            ->get();

        return view('comptabilite.debiteurs.index', [
            'debiteurs' => $debiteurs,
        ]);
    }
}
