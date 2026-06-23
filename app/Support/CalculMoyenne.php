<?php

namespace App\Support;

use App\Models\Etudiant;
use App\Models\Ponderation;
use Illuminate\Support\Collection;

/**
 * Calcul des moyennes pondérées par catégorie (Examen / TP / TD / CC).
 *
 * Règle de re-normalisation : seuls les poids des catégories qui (a) sont actives
 * (poids > 0) et (b) possèdent au moins une note sont retenus, puis re-normalisés
 * pour totaliser 100 %. Ainsi une catégorie pondérée mais sans note ne pénalise
 * pas l'étudiant, et les notes existantes restent toujours prises en compte.
 */
class CalculMoyenne
{
    /**
     * Moyenne pondérée d'une matière à partir de ses notes (chacune ayant une
     * `categorie` et une `valeur`) et des poids configurés.
     *
     * @param  Collection  $notes  notes d'une seule matière
     * @param  array<string, int>  $poids  poids par catégorie
     */
    public static function moyenneMatiere(Collection $notes, array $poids): ?float
    {
        if ($notes->isEmpty()) {
            return null;
        }

        $moyParCat = $notes->groupBy('categorie')->map(fn ($g) => (float) $g->avg('valeur'));

        // Poids retenus : catégorie active (poids>0) ET ayant des notes.
        $retenus = [];
        foreach ($poids as $cat => $p) {
            if ($p > 0 && $moyParCat->has($cat)) {
                $retenus[$cat] = $p;
            }
        }

        $total = array_sum($retenus);

        // Aucune catégorie pondérée n'a de note : repli sur la moyenne simple.
        if ($total === 0) {
            return round((float) $notes->avg('valeur'), 2);
        }

        $somme = 0.0;
        foreach ($retenus as $cat => $p) {
            $somme += $moyParCat[$cat] * $p;
        }

        return round($somme / $total, 2);
    }

    /** Moyenne générale pondérée d'un étudiant (moyenne des moyennes de matières). */
    public static function moyenneGenerale(Etudiant $etudiant): float
    {
        $notes = $etudiant->notes()->get(['matiere', 'categorie', 'valeur']);

        if ($notes->isEmpty()) {
            return 0.0;
        }

        $moyennes = $notes->groupBy('matiere')->map(function ($notesMatiere) use ($etudiant) {
            $poids = Ponderation::pour($etudiant->filiere, $etudiant->niveau, $notesMatiere->first()->matiere)->poids();

            return self::moyenneMatiere($notesMatiere, $poids);
        })->filter(fn ($m) => $m !== null);

        return $moyennes->isEmpty() ? 0.0 : round((float) $moyennes->avg(), 2);
    }

    /**
     * Détail par matière pour le bulletin : moyenne par catégorie, poids configuré
     * et moyenne pondérée de la matière.
     */
    public static function detailParMatiere(Etudiant $etudiant): Collection
    {
        $notes = $etudiant->notes()->get(['matiere', 'categorie', 'valeur']);

        return $notes->groupBy('matiere')->map(function ($notesMatiere) use ($etudiant) {
            $poids = Ponderation::pour($etudiant->filiere, $etudiant->niveau, $notesMatiere->first()->matiere)->poids();
            $moyParCat = $notesMatiere->groupBy('categorie')->map(fn ($g) => round((float) $g->avg('valeur'), 2));

            $categories = [];
            foreach ($moyParCat as $cat => $moy) {
                $categories[$cat] = ['moyenne' => $moy, 'poids' => $poids[$cat] ?? 0];
            }

            return [
                'categories' => $categories,
                'moyenne' => self::moyenneMatiere($notesMatiere, $poids),
            ];
        });
    }
}
