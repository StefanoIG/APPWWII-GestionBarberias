<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Cita;

class EnviarRecordatoriosCita extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'citas:enviar-recordatorios';
    protected $description = 'Envía recordatorios por email para las citas del día siguiente.';

    public function handle()
    {
        $citas = Cita::where('fecha', now()->addDay()->toDateString())
            ->where('estado', 'confirmada')
            ->get();

        foreach ($citas as $cita) {
            // Lógica para enviar email de recordatorio
            $this->info("Enviando recordatorio para la cita #{$cita->id}");
        }

        return 0;
    }
}
