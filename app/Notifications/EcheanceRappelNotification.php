<?php

namespace App\Notifications;

use App\Models\Projet;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EcheanceRappelNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Projet $projet,
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
            ->subject('[SIGE UCAO] Rappel — '.$this->projet->typeLabel().' à venir')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->message())
            ->line("Pensez à vous organiser à l'avance.");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Rappel — '.$this->projet->typeLabel(),
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        $verbe = match ($this->projet->type) {
            'examen' => 'aura lieu',
            default => 'est à rendre',
        };

        return sprintf(
            'Rappel : le %s "%s" (%s, %s %s) %s le %s.',
            mb_strtolower($this->projet->typeLabel()),
            $this->projet->titre,
            $this->projet->matiere,
            $this->projet->filiere,
            $this->projet->niveau,
            $verbe,
            $this->projet->date_limite->format('d/m/Y'),
        );
    }
}
