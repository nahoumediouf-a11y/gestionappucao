<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        return view('etudiant.profil.index', [
            'etudiant' => $etudiant,
            'moyenne' => $etudiant->moyenne(),
        ]);
    }
}
