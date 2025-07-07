<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cita;
class StoreCalificacionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $cita = Cita::findOrFail($this->input('cita_id'));

        // Reglas de negocio para poder calificar:
        // 1. El usuario debe ser el cliente de la cita.
        // 2. El estado de la cita debe ser 'completada'.
        // 3. La cita no debe tener ya una calificación.
        return $this->user()->id === $cita->cliente_id
            && $cita->estado === 'completada'
            && is_null($cita->calificacion);
    }

    public function rules(): array
    {
        return [
            'cita_id' => 'required|exists:citas,id',
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string',
        ];
    }
}
