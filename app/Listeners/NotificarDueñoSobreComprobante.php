<?php

namespace App\Listeners;

use App\Events\ComprobanteSubido;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class NotificarDueñoSobreComprobante
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
    public function handle(ComprobanteSubido $event): void
    {
        //
    }
}
