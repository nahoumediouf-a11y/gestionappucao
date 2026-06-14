<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;
        $notes = $etudiant->notes()->orderBy('session')->orderBy('matiere')->get();

        $parSession = $notes->groupBy('session')->map(function ($notesSession) {
            return [
                'notes' => $notesSession,
                'moyenne' => round((float) $notesSession->avg('valeur'), 2),
            ];
        });

        return view('etudiant.bulletin.index', [
            'etudiant' => $etudiant,
            'parSession' => $parSession,
            'moyenneGenerale' => $etudiant->moyenne(),
        ]);
    }
}
