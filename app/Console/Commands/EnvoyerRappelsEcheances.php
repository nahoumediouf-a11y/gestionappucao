<?php

namespace App\Console\Commands;

use App\Enums\Role;
use App\Models\Projet;
use App\Models\User;
use App\Notifications\EcheanceRappelNotification;
use Illuminate\Console\Command;

class EnvoyerRappelsEcheances extends Command
{
    /** Nombre de jours avant l'échéance auquel le rappel est envoyé. */
    private const JOURS_AVANT = 3;

    protected $signature = 'rappels:echeances';

    protected $description = "Envoie un rappel aux étudiants pour les projets, devoirs et examens dont l'échéance approche";

    public function handle(): int
    {
        $dateCible = today()->addDays(self::JOURS_AVANT)->toDateString();

        $projets = Projet::whereDate('date_limite', $dateCible)
            ->where('rappel_envoye', false)
            ->get();

        foreach ($projets as $projet) {
            $etudiants = User::where('role', Role::Etudiant)
                ->whereHas('etudiant', function ($q) use ($projet) {
                    $q->where('filiere', $projet->filiere)->where('niveau', $projet->niveau);
                })
                ->get();

            foreach ($etudiants as $etudiant) {
                $etudiant->notify(new EcheanceRappelNotification($projet));
            }

            $projet->update(['rappel_envoye' => true]);

            $this->line("Rappel envoyé pour : {$projet->titre} ({$projet->filiere} {$projet->niveau}) — {$etudiants->count()} étudiant(s).");
        }

        if ($projets->isEmpty()) {
            $this->line('Aucune échéance à rappeler aujourd\'hui.');
        }

        return self::SUCCESS;
    }
}
