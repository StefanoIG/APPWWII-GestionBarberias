<?php

namespace App\Events;

use App\Models\Cita;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CitaAgendada
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public Cita $cita;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Cita $cita
     * @return void
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }
}
