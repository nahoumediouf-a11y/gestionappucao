<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Models\EmploiDuTemps;
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
}
