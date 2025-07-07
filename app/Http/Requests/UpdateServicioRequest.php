<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario debe ser el dueño de la barbería a la que pertenece el servicio.
        $servicio = $this->route('servicio');
        return $this->user()->id === $servicio->barberia->owner_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $servicio = $this->route('servicio');
        $barberiaId = $servicio->barberia_id;

        return [
            'nombre' => [
                'sometimes', // 'sometimes' para que no sea obligatorio en cada update
                'required',
                'string',
                'max:255',
                Rule::unique('servicios')->where(function ($query) use ($barberiaId) {
                    return $query->where('barberia_id', $barberiaId);
                })->ignore($servicio->id), // Ignorar el servicio actual al verificar unicidad
            ],
            'descripcion' => 'sometimes|nullable|string',
            'duracion' => 'sometimes|required|integer|min:5',
            'precio' => 'sometimes|required|numeric|min:0',
        ];
    }
}
