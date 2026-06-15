<?php

namespace App\Notifications;

use App\Models\EmploiDuTemps;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SalleModifieeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected EmploiDuTemps $creneau,
        protected string $ancienneSalle,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[SIGE UCAO] Changement de salle')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->message())
            ->line("Veuillez consulter votre emploi du temps mis à jour sur la plateforme.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Changement de salle',
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Le cours de %s prévu le %s à %s (%s %s) en salle %s a été déplacé vers la salle %s.',
            $this->creneau->matiere,
            $this->creneau->jour,
            substr((string) $this->creneau->heure_debut, 0, 5),
            $this->creneau->filiere,
            $this->creneau->niveau,
            $this->ancienneSalle,
            $this->creneau->salle,
        );
    }
}
