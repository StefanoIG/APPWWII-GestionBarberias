<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Barberia;

class StoreServicioRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario autenticado debe ser el dueño de la barbería a la que pertenece el servicio.
        $barberia = Barberia::findOrFail($this->input('barberia_id'));
        return $this->user()->id === $barberia->owner_id;
    }

    public function rules(): array
    {
        return [
            'barberia_id' => 'required|exists:barberias,id',
            'nombre' => [
                'required',
                'string',
                'max:255',
                // Regla de negocio: nombre único por barbería
                Rule::unique('servicios')->where(function ($query) {
                    return $query->where('barberia_id', $this->input('barberia_id'));
                }),
            ],
            'descripcion' => 'nullable|string',
            'duracion' => 'required|integer|min:5',
            'precio' => 'required|numeric|min:0',
        ];
    }
}
