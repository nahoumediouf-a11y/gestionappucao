<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Tri de liste sécurisé : seules les colonnes explicitement autorisées peuvent
 * servir au tri (évite l'injection via le paramètre `tri`).
 */
trait TrieListe
{
    /**
     * Résout la colonne et la direction de tri depuis la requête.
     *
     * @param  array<int, string>  $colonnesAutorisees
     * @return array{0: string, 1: string}
     */
    protected function resoudreTri(Request $request, array $colonnesAutorisees, string $defaut): array
    {
        $colonne = (string) $request->query('tri');
        if (! in_array($colonne, $colonnesAutorisees, true)) {
            $colonne = $defaut;
        }

        $direction = $request->query('dir') === 'desc' ? 'desc' : 'asc';

        return [$colonne, $direction];
    }

    protected function appliquerTri(Builder $query, string $colonne, string $direction): Builder
    {
        return $query->orderBy($colonne, $direction);
    }
}
