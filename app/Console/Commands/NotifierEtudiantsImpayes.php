<?php

namespace App\Console\Commands;

use App\Models\Etudiant;
use App\Notifications\SoldeImpayeNotification;
use Illuminate\Console\Command;

class NotifierEtudiantsImpayes extends Command
{
    protected $signature = 'sige:notifier-impayes {--force : Notifie même si une notification similaire existe déjà}';
    protected $description = 'Envoie une notification aux étudiants ayant un solde impayé';

    public function handle(): void
    {
        $etudiants = Etudiant::with('user')->where('solde', '>', 0)->get();

        $count = 0;
        foreach ($etudiants as $etudiant) {
            if (! $etudiant->user) {
                continue;
            }

            if (! $this->option('force')) {
                $alreadyNotified = $etudiant->user->notifications()
                    ->where('type', SoldeImpayeNotification::class)
                    ->whereNull('read_at')
                    ->exists();

                if ($alreadyNotified) {
                    continue;
                }
            }

            $etudiant->user->notify(new SoldeImpayeNotification($etudiant->solde));
            $count++;
        }

        $this->info("{$count} notification(s) envoyée(s).");
    }
}
