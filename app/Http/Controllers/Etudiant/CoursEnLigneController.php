<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\CoursEnLigne;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoursEnLigneController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        $cours = CoursEnLigne::with('professeur')
            ->pourClasse($etudiant->filiere, $etudiant->niveau)
            ->orderByRaw("CASE statut WHEN 'en_cours' THEN 0 WHEN 'planifie' THEN 1 ELSE 2 END")
            ->orderBy('debut_prevu')
            ->get();

        return view('etudiant.cours.index', [
            'etudiant' => $etudiant,
            'cours' => $cours,
        ]);
    }

    public function salle(CoursEnLigne $cours): View|RedirectResponse
    {
        $etudiant = auth()->user()->etudiant;

        // L'étudiant ne peut rejoindre que les séances de sa propre classe.
        abort_unless(
            $cours->filiere === $etudiant->filiere && $cours->niveau === $etudiant->niveau,
            403
        );

        if (! $cours->estRejoignable()) {
            return redirect()->route('etudiant.cours.index')
                ->with('error', 'Cette séance n\'est pas encore ouverte ou est déjà terminée.');
        }

        return view('cours.salle', [
            'cours' => $cours,
            'estModerateur' => false,
            'retourUrl' => route('etudiant.cours.index'),
        ]);
    }
}
