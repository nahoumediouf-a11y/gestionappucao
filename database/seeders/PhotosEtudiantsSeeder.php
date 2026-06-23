<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/**
 * Attribue à chaque étudiant un portrait RÉEL (photos libres de volontaires de
 * randomuser.me — aucune image générée par IA). Idempotent (ne re-télécharge pas
 * une photo déjà présente) et tolérant hors-ligne (laisse la photo vide en cas
 * d'échec réseau → repli sur l'avatar à initiales).
 */
class PhotosEtudiantsSeeder extends Seeder
{
    public function run(): void
    {
        $etudiants = User::where('role', Role::Etudiant->value)->orderBy('id')->get();

        $i = 0;
        foreach ($etudiants as $user) {
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                $i++;

                continue;
            }

            $genre = $i % 2 === 0 ? 'women' : 'men';
            $num = $i % 100;
            $url = "https://randomuser.me/api/portraits/{$genre}/{$num}.jpg";

            try {
                $reponse = Http::timeout(10)->get($url);
                if ($reponse->successful()) {
                    $chemin = "photos/etudiant-{$user->id}.jpg";
                    Storage::disk('public')->put($chemin, $reponse->body());
                    $user->update(['photo' => $chemin]);
                }
            } catch (\Throwable $e) {
                // Hors-ligne ou source indisponible : on laisse la photo vide.
            }

            $i++;
        }
    }
}
