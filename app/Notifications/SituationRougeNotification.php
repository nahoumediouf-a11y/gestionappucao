<?php

namespace App\Notifications;

use App\Models\Absence;
use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SituationRougeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Etudiant $etudiant,
        protected Absence $absence,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $this->etudiant->loadMissing('user');

        return [
            'titre' => 'Situation rouge — absences',
            'message' => sprintf(
                '%s (%s, %s %s) a atteint %d absences non justifiées (dernière en %s le %s). Accès aux examens bloqué.',
                $this->etudiant->user->nom_complet,
                $this->etudiant->matricule,
                $this->etudiant->filiere,
                $this->etudiant->niveau,
                $this->etudiant->absencesNonJustifieesCount(),
                $this->absence->matiere,
                $this->absence->date->format('d/m/Y'),
            ),
            'etudiant_id' => $this->etudiant->id,
        ];
    }
}
