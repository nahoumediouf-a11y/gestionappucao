<?php

namespace App\Notifications;

use App\Models\EngagementPaiement;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RelancePaiementNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected EngagementPaiement $engagement,
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return $notifiable->email ? ['mail', 'database'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[SIGE UCAO] Relance de paiement — Engagement échu')
            ->greeting('Bonjour '.$notifiable->prenom.',')
            ->line($this->message())
            ->line('Nous vous invitons à régulariser votre situation dans les plus brefs délais auprès du service de recouvrement.');
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Relance de paiement',
            'message' => $this->message(),
        ];
    }

    private function message(): string
    {
        return sprintf(
            'Votre engagement de paiement de %s FCFA, dont l\'échéance était fixée au %s, n\'a pas encore été honoré. Solde restant : %s FCFA.',
            number_format($this->engagement->montant, 0, ',', ' '),
            $this->engagement->echeance->format('d/m/Y'),
            number_format($this->engagement->etudiant->solde, 0, ',', ' '),
        );
    }
}
