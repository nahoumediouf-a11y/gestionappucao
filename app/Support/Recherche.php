<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Utilitaires partagés par la recherche globale et l'autocomplétion :
 * normalisation (sans accents, minuscules) et échappement des jokers LIKE.
 */
class Recherche
{
    /** Longueur minimale d'une requête pour déclencher des suggestions. */
    public const LONGUEUR_MIN = 2;

    /** Nombre maximal de suggestions renvoyées par l'autocomplétion. */
    public const LIMITE_SUGGESTIONS = 8;

    /**
     * Normalise un texte pour la recherche : retire les accents et passe en
     * minuscules. « Néné » et « nene » deviennent identiques.
     */
    public static function normaliser(?string $texte): string
    {
        return mb_strtolower(Str::ascii(trim((string) $texte)));
    }

    /**
     * Échappe les jokers LIKE (`%`, `_`) et l'antislash saisis par l'utilisateur,
     * afin qu'ils soient recherchés littéralement. Utiliser avec « ESCAPE '\' ».
     */
    public static function echapperLike(string $valeur): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $valeur);
    }

    /**
     * Découpe une requête en mots (tokens) normalisés et échappés, sans doublon.
     * Permet la recherche « en quelques mots » (ex. « nahoume info »).
     *
     * @return array<int, string>
     */
    public static function tokens(string $q): array
    {
        $mots = preg_split('/\s+/', self::normaliser($q), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return array_values(array_unique(array_map(self::echapperLike(...), $mots)));
    }
}
