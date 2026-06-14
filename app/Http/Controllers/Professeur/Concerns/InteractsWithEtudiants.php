<?php

namespace App\Http\Controllers\Professeur\Concerns;

use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use Illuminate\Support\Collection;

trait InteractsWithEtudiants
{
    /** Étudiants des filières/niveaux enseignés par le professeur connecté. */
    protected function etudiantsDuProfesseur(): Collection
    {
        $filieresNiveaux = EmploiDuTemps::where('professeur_id', auth()->id())
            ->get(['filiere', 'niveau'])
            ->unique(fn ($c) => $c->filiere.'|'.$c->niveau);

        if ($filieresNiveaux->isEmpty()) {
            return collect();
        }

        return Etudiant::with('user')
            ->where(function ($query) use ($filieresNiveaux) {
                foreach ($filieresNiveaux as $fn) {
                    $query->orWhere(function ($q) use ($fn) {
                        $q->where('filiere', $fn->filiere)->where('niveau', $fn->niveau);
                    });
                }
            })
            ->orderBy('matricule')
            ->get();
    }

    /** Combinaisons filière/niveau/matière enseignées par le professeur connecté. */
    protected function creneauxDuProfesseur(): Collection
    {
        return EmploiDuTemps::where('professeur_id', auth()->id())
            ->get(['filiere', 'niveau', 'matiere'])
            ->unique(fn ($c) => $c->filiere.'|'.$c->niveau.'|'.$c->matiere)
            ->values();
    }
}
