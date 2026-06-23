<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Gestion fiable de la photo de profil (portrait réel) : validation, MIME réel,
 * nom de fichier aléatoire, remplacement (suppression de l'ancien), retrait et
 * tolérance aux erreurs de stockage.
 */
class PhotoUtilisateur
{
    /** Extensions autorisées dérivées du MIME réel (jamais l'extension cliente). */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    /** Règles de validation réutilisables pour le champ `photo` (formulaires Compte / Admin). */
    public static function regles(): array
    {
        return [
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:min_width=100,min_height=100'],
            'supprimer_photo' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'Formats acceptés : JPG, PNG ou WebP (les fichiers HEIC ne sont pas supportés — convertissez-les).',
            'photo.max' => 'La photo ne doit pas dépasser 2 Mo.',
            'photo.dimensions' => 'La photo doit faire au moins 100 × 100 pixels.',
        ];
    }

    /** Applique le changement de photo demandé (champ `photo`, case `supprimer_photo`). */
    public static function appliquer(User $user, Request $request): void
    {
        if ($request->boolean('supprimer_photo')) {
            self::supprimer($user);

            return;
        }

        if (! $request->hasFile('photo')) {
            return;
        }

        $fichier = $request->file('photo');

        // Défense en profondeur : on se fie au MIME réel détecté, pas à l'extension du nom.
        $mime = $fichier->getMimeType();
        $extension = self::EXTENSIONS[$mime] ?? null;
        if ($extension === null) {
            throw ValidationException::withMessages(['photo' => 'Type d\'image non supporté (JPG, PNG ou WebP uniquement).']);
        }

        // Nom aléatoire basé sur l'id (pas de nom client → ni exécution, ni collision, ni traversal).
        $nom = 'photos/u'.$user->id.'-'.Str::lower(Str::random(10)).'.'.$extension;

        try {
            $ancien = $user->photo;
            Storage::disk('public')->put($nom, file_get_contents($fichier->getRealPath()));
            $user->update(['photo' => $nom]);
            self::supprimerFichier($ancien);
        } catch (\Throwable $e) {
            throw ValidationException::withMessages(['photo' => 'Impossible d\'enregistrer la photo. Réessayez.']);
        }
    }

    public static function supprimer(User $user): void
    {
        $ancien = $user->photo;
        $user->update(['photo' => null]);
        self::supprimerFichier($ancien);
    }

    /** Supprime uniquement le fichier (utilisé aussi à la suppression d'un utilisateur). */
    public static function supprimerFichier(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}
