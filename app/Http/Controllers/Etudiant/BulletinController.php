<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(): View
    {
        return view('etudiant.bulletin.index', $this->donnees());
    }

    public function telecharger(): Response
    {
        $donnees = $this->donnees();

        $pdf = Pdf::loadView('etudiant.bulletin.pdf', $donnees);

        return $pdf->download("bulletin-{$donnees['etudiant']->matricule}.pdf");
    }

    /**
     * @return array{etudiant: \App\Models\Etudiant, parSession: \Illuminate\Support\Collection, moyenneGenerale: float}
     */
    private function donnees(): array
    {
        $etudiant = auth()->user()->etudiant;
        $notes = $etudiant->notes()->orderBy('session')->orderBy('matiere')->get();

        $parSession = $notes->groupBy('session')->map(function ($notesSession) {
            return [
                'notes' => $notesSession,
                'moyenne' => round((float) $notesSession->avg('valeur'), 2),
            ];
        });

        return [
            'etudiant' => $etudiant,
            'parSession' => $parSession,
            'moyenneGenerale' => $etudiant->moyenne(),
        ];
    }
}
