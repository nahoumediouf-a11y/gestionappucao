<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class EmploiDuTempsController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        return view('etudiant.edt.index', [
            'etudiant' => $etudiant,
            'creneaux' => $etudiant->emploiDuTemps(),
        ]);
    }
}
