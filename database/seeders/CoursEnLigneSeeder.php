<?php

namespace Database\Seeders;

use App\Models\CoursEnLigne;
use App\Models\EmploiDuTemps;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CoursEnLigneSeeder extends Seeder
{
    public function run(): void
    {
        $prof = User::where('login', 'prof')->first();

        if (! $prof) {
            return;
        }

        // Rattache, si possible, chaque séance à un créneau Informatique L3 du prof.
        $creneau = EmploiDuTemps::where('professeur_id', $prof->id)
            ->where('filiere', 'Informatique')
            ->where('niveau', 'L3')
            ->first();

        $seances = [
            [
                'titre' => 'Algorithmique — séance en direct',
                'description' => 'Révision des algorithmes de tri et séance de questions/réponses.',
                'debut_prevu' => now()->subMinutes(10),
                'fin_prevue' => now()->addHour(),
                'statut' => 'en_cours',
                'demarre_a' => now()->subMinutes(10),
                'termine_a' => null,
            ],
            [
                'titre' => 'Base de données — modélisation MCD',
                'description' => 'Atelier en ligne sur la conception du modèle conceptuel de données.',
                'debut_prevu' => now()->addHours(2),
                'fin_prevue' => now()->addHours(4),
                'statut' => 'planifie',
                'demarre_a' => null,
                'termine_a' => null,
            ],
            [
                'titre' => 'Programmation Orientée Objet — introduction',
                'description' => 'Séance d\'introduction déjà tenue (rediffusion non disponible).',
                'debut_prevu' => now()->subDay(),
                'fin_prevue' => now()->subDay()->addHours(2),
                'statut' => 'termine',
                'demarre_a' => now()->subDay(),
                'termine_a' => now()->subDay()->addHours(2),
            ],
        ];

        foreach ($seances as $seance) {
            CoursEnLigne::updateOrCreate(
                ['professeur_id' => $prof->id, 'titre' => $seance['titre']],
                array_merge($seance, [
                    'emploi_du_temps_id' => $creneau?->id,
                    'filiere' => 'Informatique',
                    'niveau' => 'L3',
                    'room_name' => 'ucao-demo-'.Str::slug($seance['titre']),
                ])
            );
        }
    }
}
