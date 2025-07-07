<?php

namespace App\Mail;

use App\Models\Cita; 
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ConfirmacionCitaMail extends Mailable
{
    use Queueable, SerializesModels;

    public Cita $cita;

    /**
     * Create a new message instance.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu Cita ha sido Confirmada',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.citas.confirmacion',
            with: [
                'nombreCliente' => $this->cita->cliente->nombre,
                'nombreServicio' => $this->cita->servicio->nombre,
                'nombreBarbero' => $this->cita->barbero->user->nombre,
                'fecha' => $this->cita->fecha,
                'hora' => $this->cita->hora,
                'url' => url('/mis-citas') // URL del frontend
            ]
        );
    }
}
