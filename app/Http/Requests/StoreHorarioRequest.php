<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Barbero;
use Illuminate\Validation\Rule;

class StoreHorarioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario autenticado debe ser el dueño de la barbería del barbero.
        $barbero = Barbero::findOrFail($this->input('barbero_id'));
        return $this->user()->id === $barbero->barberia->owner_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'barbero_id' => 'required|exists:barberos,id',
            'dia_semana' => [
                'required',
                Rule::in(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo']),
                // Un barbero solo puede tener un registro de horario por día
                Rule::unique('horarios_barbero')->where(function ($query) {
                    return $query->where('barbero_id', $this->input('barbero_id'));
                })
            ],
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'hora_fin.after' => 'La hora de fin debe ser posterior a la hora de inicio.',
            'dia_semana.unique' => 'Ya existe un horario definido para este barbero en el día seleccionado.',
        ];
    }
}
