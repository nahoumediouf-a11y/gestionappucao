<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
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

    public function pdf(): Response
    {
        $etudiant = auth()->user()->etudiant;

        $pdf = Pdf::loadView('etudiant.edt.pdf', [
            'titre' => 'Emploi du temps — '.$etudiant->filiere.' '.$etudiant->niveau,
            'sousTitre' => $etudiant->user->nom_complet.' — '.$etudiant->matricule,
            'creneaux' => $etudiant->emploiDuTemps(),
            'contexte' => 'etudiant',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('emploi-du-temps-'.$etudiant->matricule.'.pdf');
    }
}
