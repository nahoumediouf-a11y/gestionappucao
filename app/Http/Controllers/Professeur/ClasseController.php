<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Projet;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ClasseController extends Controller
{
    use InteractsWithEtudiants;

    public function show(Request $request): View
    {
        $filiere = (string) $request->query('filiere');
        $niveau = (string) $request->query('niveau');

        abort_unless($this->enseigneClasse($filiere, $niveau), Response::HTTP_FORBIDDEN);

        $etudiants = Etudiant::with('user')
            ->where('filiere', $filiere)
            ->where('niveau', $niveau)
            ->orderBy('matricule')
            ->get();

        $ids = $etudiants->pluck('id');
        $profId = auth()->id();

        // Moyennes par étudiant (sur les notes saisies par ce professeur), sans N+1.
        $moyennes = Note::whereIn('etudiant_id', $ids)
            ->where('professeur_id', $profId)
            ->get(['etudiant_id', 'valeur'])
            ->groupBy('etudiant_id')
            ->map(fn ($notes) => round($notes->avg('valeur'), 2));

        // Absences non justifiées par étudiant.
        $absencesNonJustifiees = Absence::whereIn('etudiant_id', $ids)
            ->where('justifiee', false)
            ->get(['etudiant_id'])
            ->groupBy('etudiant_id')
            ->map->count();

        // Dernier travail assigné à cette classe par ce professeur (taux de rendu).
        $dernierTravail = Projet::withCount('soumissions')
            ->where('professeur_id', $profId)
            ->where('filiere', $filiere)
            ->where('niveau', $niveau)
            ->orderByDesc('date_limite')
            ->first();

        $effectif = $etudiants->count();
        $seuil = Etudiant::SEUIL_ABSENCES_SITUATION_ROUGE;

        // Étudiants à risque : trop d'absences non justifiées ou moyenne < 10.
        $aRisque = $etudiants->filter(function ($e) use ($moyennes, $absencesNonJustifiees, $seuil) {
            $moy = $moyennes[$e->id] ?? null;

            return ($absencesNonJustifiees[$e->id] ?? 0) >= $seuil || ($moy !== null && $moy < 10);
        });

        $moyenneClasse = $moyennes->isNotEmpty() ? round($moyennes->avg(), 2) : null;

        return view('professeur.classes.show', [
            'filiere' => $filiere,
            'niveau' => $niveau,
            'etudiants' => $etudiants,
            'moyennes' => $moyennes,
            'absencesNonJustifiees' => $absencesNonJustifiees,
            'effectif' => $effectif,
            'moyenneClasse' => $moyenneClasse,
            'aRisque' => $aRisque,
            'seuilAbsences' => $seuil,
            'dernierTravail' => $dernierTravail,
            'tauxRendu' => $dernierTravail && $effectif > 0
                ? round($dernierTravail->soumissions_count / $effectif * 100)
                : null,
            'matieres' => $this->creneauxDuProfesseur()
                ->where('filiere', $filiere)->where('niveau', $niveau)->pluck('matiere')->unique()->values(),
        ]);
    }
}
