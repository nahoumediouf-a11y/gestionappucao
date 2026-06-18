<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BulletinController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $donnees = $this->donnees();

        if (! $donnees['etudiant']->estEnRegleAvecRecouvrement()) {
            return redirect()
                ->route('etudiant.paiements.index')
                ->with('error', 'Votre bulletin est bloqué : vous avez un solde impayé de ' . number_format($donnees['etudiant']->solde, 0, ',', ' ') . ' FCFA. Régularisez votre situation pour y accéder.');
        }

        return view('etudiant.bulletin.index', $donnees);
    }

    public function telecharger(): Response|RedirectResponse
    {
        $donnees = $this->donnees();

        if (! $donnees['etudiant']->estEnRegleAvecRecouvrement()) {
            return redirect()
                ->route('etudiant.bulletin.index')
                ->with('error', 'Le téléchargement du bulletin est bloqué : vous avez un solde impayé. Veuillez régulariser votre situation auprès du service de recouvrement.');
        }

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
