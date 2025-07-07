<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use App\Models\Barbero;
use App\Models\Servicio;
use App\Models\Cita;
use Carbon\Carbon;

class HorarioDisponible implements ValidationRule
{
    protected $barberoId;
    protected $servicioId;
    protected $fecha;
    protected $diaSemana;

    public function __construct($barberoId, $servicioId, $fecha)
    {
        $this->barberoId = $barberoId;
        $this->servicioId = $servicioId;
        $this->fecha = $fecha;

        // Mapeo de nombres de días de Carbon (inglés) a tu base de datos (español)
        $diasMapa = [
            'sunday' => 'domingo',
            'monday' => 'lunes',
            'tuesday' => 'martes',
            'wednesday' => 'miercoles',
            'thursday' => 'jueves',
            'friday' => 'viernes',
            'saturday' => 'sabado',
        ];
        $nombreDiaIngles = strtolower(Carbon::parse($this->fecha)->format('l'));
        $this->diaSemana = $diasMapa[$nombreDiaIngles];
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $barbero = Barbero::find($this->barberoId);
        $servicio = Servicio::find($this->servicioId);

        if (!$barbero || !$servicio) {
            $fail('El barbero o servicio seleccionado no es válido.');
            return;
        }

        // 1. Verificar si el barbero trabaja ese día
        $horarioLaboral = $barbero->horarios()->where('dia_semana', $this->diaSemana)->first();
        if (!$horarioLaboral) {
            $fail('El barbero no trabaja en la fecha seleccionada.');
            return;
        }

        // 2. Verificar si la hora de la cita está dentro del horario laboral
        $horaCita = Carbon::parse($this->fecha . ' ' . $value);
        $horaInicioLaboral = Carbon::parse($this->fecha . ' ' . $horarioLaboral->hora_inicio);
        $horaFinLaboral = Carbon::parse($this->fecha . ' ' . $horarioLaboral->hora_fin);
        $horaFinCita = $horaCita->copy()->addMinutes($servicio->duracion);

        if ($horaCita->lt($horaInicioLaboral) || $horaFinCita->gt($horaFinLaboral)) {
            $fail('La hora seleccionada está fuera del horario laboral del barbero.'); 
            return;
        }

        // 3. Verificar si se solapa con otras citas existentes
        $citasExistentes = Cita::where('barbero_id', $this->barberoId)
            ->where('fecha', $this->fecha)
            ->whereIn('estado', ['confirmada', 'pendiente']) // Solo contra citas válidas
            ->get();   

        foreach ($citasExistentes as $citaExistente) {
            $inicioExistente = Carbon::parse($citaExistente->fecha . ' ' . $citaExistente->hora);
            $finExistente = $inicioExistente->copy()->addMinutes($citaExistente->servicio->duracion);

            // Lógica de solapamiento:
            // Una nueva cita (N) se solapa con una existente (E) si:
            // (InicioN < FinE) y (FinN > InicioE)
            if ($horaCita->lt($finExistente) && $horaFinCita->gt($inicioExistente)) {
                $fail('El horario seleccionado ya no está disponible. Por favor, elige otro.');
                return;
            }
        }
    }
}
