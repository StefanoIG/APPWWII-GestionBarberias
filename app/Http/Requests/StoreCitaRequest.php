<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Rules\HorarioDisponible;

class StoreCitaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */

    public function authorize(): bool
    {
        // Solo clientes pueden crear citas.
        return $this->user()->role->nombre === 'cliente';
    }

    public function rules(): array
    {
        return [
            'barberia_id' => 'required|exists:barberias,id,estado,aprobada',
            'barbero_id' => 'required|exists:barberos,id,estado,activo',
            'servicio_id' => 'required|exists:servicios,id',
            'fecha' => 'required|date|after_or_equal:today',
            'hora' => [
                'required',
                'date_format:H:i',
                // Regla de negocio personalizada para validar disponibilidad
                new HorarioDisponible($this->input('barbero_id'), $this->input('servicio_id'), $this->input('fecha'))
            ],
            'metodo_pago' => 'required|in:en_local,transferencia,payphone',
        ];
    }
}
