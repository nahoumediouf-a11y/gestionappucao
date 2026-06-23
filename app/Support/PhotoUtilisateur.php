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
    /** Taille maximale acceptée (Ko). */
    private const TAILLE_MAX_KO = 8192;

    /** Extension dérivée du MIME réel (toutes les images courantes). */
    private const EXTENSIONS = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'image/tiff' => 'tiff',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    /**
     * Règles de validation réutilisables : on accepte TOUTE image (n'importe quel
     * format/dimension), seule la taille est plafonnée. `image` reste exigé pour
     * refuser les fichiers qui ne sont pas des images (sécurité).
     */
    public static function regles(): array
    {
        return [
            'photo' => ['nullable', 'image', 'max:'.self::TAILLE_MAX_KO],
            'supprimer_photo' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'photo.image' => 'Le fichier doit être une image.',
            'photo.max' => 'La photo ne doit pas dépasser 8 Mo.',
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

        // Extension dérivée du MIME réel (toute image acceptée) ; repli sur
        // l'extension devinée puis « img ». On n'utilise jamais le nom client.
        $mime = $fichier->getMimeType();
        $extension = self::EXTENSIONS[$mime] ?? ($fichier->extension() ?: 'img');

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
