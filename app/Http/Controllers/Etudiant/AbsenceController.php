<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AbsenceController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;
        $absences = $etudiant->absences()->orderByDesc('date')->get();

        return view('etudiant.absences.index', [
            'absences' => $absences,
        ]);
    }
}
