<?php

namespace App\Mail;

use App\Models\Etudiant;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteParentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Etudiant $etudiant,
        public string $titre,
        public string $contenu,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[SIGE UCAO] '.$this->titre);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.alerte-parent');
    }
}
