<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Un solo correo reutilizable: cambia el asunto y la vista (resources/views/emails/*). */
class CafeQrMail extends Mailable
{
    public function __construct(
        public string $asunto,
        public string $vistaCorreo,
        public array $datos = [],
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->asunto);
    }

    public function content(): Content
    {
        return new Content(view: $this->vistaCorreo, with: $this->datos);
    }
}
