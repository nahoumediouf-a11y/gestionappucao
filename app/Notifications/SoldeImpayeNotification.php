<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SoldeImpayeNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected float $solde,
    ) {
    }

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'titre' => 'Solde impayé — Action requise',
            'message' => sprintf(
                'Vous avez un solde impayé de %s FCFA. Votre bulletin de notes et vos documents officiels sont bloqués. Veuillez régulariser votre situation auprès du service de recouvrement.',
                number_format($this->solde, 0, ',', ' ')
            ),
        ];
    }
}
