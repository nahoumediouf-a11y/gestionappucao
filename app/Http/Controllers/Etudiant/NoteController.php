<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class NoteController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;
        $notes = $etudiant->notes()->orderBy('session')->orderBy('matiere')->get();

        return view('etudiant.notes.index', [
            'notes' => $notes,
            'moyenne' => $etudiant->moyenne(),
            'parSession' => $notes->groupBy('session'),
        ]);
    }
}
