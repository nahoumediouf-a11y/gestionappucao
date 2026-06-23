<?php

namespace Database\Seeders;

use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Projet;
use App\Models\Soumission;
use App\Models\User;
use Illuminate\Database\Seeder;

class SoumissionSeeder extends Seeder
{
    public function run(): void
    {
        // Un travail Informatique L3 servant de démonstration.
        $projet = Projet::where('filiere', 'Informatique')
            ->where('niveau', 'L3')
            ->orderBy('id')
            ->first();

        if (! $projet) {
            return;
        }

        $prof = User::where('login', 'prof')->first();

        $etu1 = Etudiant::whereHas('user', fn ($q) => $q->where('login', 'etudiant1'))->first();
        $etu2 = Etudiant::whereHas('user', fn ($q) => $q->where('login', 'etudiant2'))->first();

        // Copie d'etudiant1 : rendue et déjà corrigée.
        if ($etu1) {
            Soumission::updateOrCreate(
                ['projet_id' => $projet->id, 'etudiant_id' => $etu1->id],
                [
                    'texte' => 'Voici ma proposition de solution avec les algorithmes demandés.',
                    'rendu_a' => now()->subDays(2),
                    'en_retard' => false,
                    'note' => 15,
                    'commentaire_correction' => 'Bon travail, raisonnement clair. Attention à la complexité.',
                    'corrige_a' => now()->subDay(),
                    'corrige_par' => $prof?->id,
                ]
            );

            // Note publiée au bulletin (note ramenée sur 20 ; barème = 20 ici).
            Note::updateOrCreate(
                ['etudiant_id' => $etu1->id, 'matiere' => $projet->matiere, 'session' => 'Contrôle continu'],
                ['professeur_id' => $prof?->id, 'valeur' => 15]
            );
        }

        // Copie d'etudiant2 : rendue, en attente de correction.
        if ($etu2) {
            Soumission::updateOrCreate(
                ['projet_id' => $projet->id, 'etudiant_id' => $etu2->id],
                [
                    'texte' => 'Je rends ma première version, je complèterai si possible.',
                    'rendu_a' => now()->subHours(6),
                    'en_retard' => false,
                ]
            );
        }
    }
}
