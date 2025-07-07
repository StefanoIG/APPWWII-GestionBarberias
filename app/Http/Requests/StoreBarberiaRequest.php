<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBarberiaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo un usuario con rol 'dueño' puede intentar crear una barbería.
        return $this->user()->role->nombre === 'dueño';
    }

    public function rules(): array
    {
        return [
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'direccion' => 'required|string|max:255',
            'telefono' => 'required|string|min:10|max:15',
            'email' => 'required|email|unique:barberias,email',
            'logo_url' => 'nullable|url',
        ];
    }
}
