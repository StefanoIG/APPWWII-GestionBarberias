<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UploadComprobanteRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // El usuario autenticado debe ser el cliente que agendó la cita.
        $cita = $this->route('cita');
        return Auth::check() && Auth::id() === $cita->cliente_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'comprobante' => 'required|file|image|max:2048', // Acepta solo imágenes de hasta 2MB
            'codigo_transaccion' => 'required|string|max:50',
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
            'comprobante.required' => 'Es obligatorio adjuntar el comprobante de pago.',
            'comprobante.image' => 'El archivo debe ser una imagen (jpg, png, etc.).',
            'comprobante.max' => 'La imagen no debe pesar más de 2MB.',
            'codigo_transaccion.required' => 'El código de la transacción es obligatorio.',
        ];
    }
}
