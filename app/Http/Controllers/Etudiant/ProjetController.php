<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use Illuminate\View\View;

class ProjetController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        $projets = Projet::with('professeur')
            ->where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderBy('date_limite')
            ->get();

        return view('etudiant.projets.index', [
            'projets' => $projets,
        ]);
    }
}
