<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * Gestion de la photo de profil (portrait réel) d'un utilisateur : enregistrement,
 * remplacement (avec suppression de l'ancienne) et retrait.
 */
class PhotoUtilisateur
{
    /** Applique le changement de photo demandé dans la requête (champ `photo`, case `supprimer_photo`). */
    public static function appliquer(User $user, Request $request): void
    {
        if ($request->boolean('supprimer_photo')) {
            self::supprimer($user);

            return;
        }

        if ($request->hasFile('photo')) {
            self::supprimerFichier($user->photo);
            $chemin = $request->file('photo')->store('photos', 'public');
            $user->update(['photo' => $chemin]);
        }
    }

    public static function supprimer(User $user): void
    {
        self::supprimerFichier($user->photo);
        $user->update(['photo' => null]);
    }

    private static function supprimerFichier(?string $chemin): void
    {
        if ($chemin && Storage::disk('public')->exists($chemin)) {
            Storage::disk('public')->delete($chemin);
        }
    }
}
