<?php

namespace App\Listeners;

use App\Events\CitaAgendada;
use Illuminate\Support\Facades\Mail;
use App\Mail\ConfirmacionCitaMail;

class EnviarEmailConfirmacionCita
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(CitaAgendada $event): void
    {
        Mail::to($event->cita->cliente->email)->send(new ConfirmacionCitaMail($event->cita));
    }
}
