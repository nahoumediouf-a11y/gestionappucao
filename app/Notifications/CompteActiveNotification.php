<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompteActiveNotification extends Notification
{
    use Queueable;

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
            ->subject('[SIGE UCAO] Votre compte a été validé')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line("Votre compte sur la plateforme SIGE UCAO a été vérifié et activé par l'administration.")
            ->line('Vous pouvez désormais vous connecter et accéder à toutes les fonctionnalités de votre espace étudiant.')
            ->action('Se connecter', route('login'))
            ->line("Bienvenue à l'UCAO Saint Michel !");
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Compte activé',
            'message' => "Votre compte a été vérifié et activé par l'administration. Vous pouvez désormais vous connecter.",
        ];
    }
}
