<?php

namespace App\Mail;

use App\Models\Shipment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ShipmentDepartedAlert extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Shipment $shipment) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "Embarque {$this->shipment->shipment_number} salió a ruta");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.shipment-departed');
    }
}