<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Models\EmploiDuTemps;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class EmploiDuTempsController extends Controller
{
    public function index(): View
    {
        $creneaux = EmploiDuTemps::where('professeur_id', auth()->id())
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();

        return view('professeur.edt.index', [
            'creneaux' => $creneaux,
        ]);
    }

    public function pdf(): Response
    {
        $creneaux = EmploiDuTemps::where('professeur_id', auth()->id())
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();

        $pdf = Pdf::loadView('etudiant.edt.pdf', [
            'titre' => 'Mon emploi du temps',
            'sousTitre' => auth()->user()->nom_complet,
            'creneaux' => $creneaux,
            'contexte' => 'professeur',
        ])->setPaper('a4', 'landscape');

        return $pdf->download('mon-emploi-du-temps.pdf');
    }
}
