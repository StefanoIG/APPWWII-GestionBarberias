<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Barberia;


class StoreBarberoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Solo el dueño puede registrar barberos en su barbería.
        $barberia = Barberia::findOrFail($this->input('barberia_id'));
        return $this->user()->id === $barberia->owner_id;
    }

    public function rules(): array
    {
        return [
            // Datos para la tabla 'users'
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'telefono' => 'required|string|min:10',

            // Datos para la tabla 'barberos'
            'barberia_id' => 'required|exists:barberias,id',
            'foto_url' => 'nullable|url',
            'biografia' => 'nullable|string',
            'servicios' => 'required|array', // Array de IDs de servicios que ofrece
            'servicios.*' => 'exists:servicios,id',
        ];
    }
}
