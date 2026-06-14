<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use Illuminate\View\View;

class EtudiantController extends Controller
{
    use InteractsWithEtudiants;

    public function index(): View
    {
        return view('professeur.etudiants.index', [
            'etudiants' => $this->etudiantsDuProfesseur(),
        ]);
    }
}
