<?php

namespace App\Mail;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class IncidentAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Incident $incident) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Incidencia en ruta: '.$this->incident->type_label);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.incident');
    }
}