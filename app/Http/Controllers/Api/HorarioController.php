<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HorarioBarbero;
use App\Http\Requests\StoreHorarioRequest; // Reutilizamos el mismo request

class HorarioController extends Controller
{
    /**
     * Store or update a new schedule in storage.
     *
     * Este método es más eficiente que tener un 'store' y un 'update' separados.
     * Busca un horario para un barbero en un día específico y lo actualiza,
     * o crea uno nuevo si no existe.
     */
    public function store(StoreHorarioRequest $request)
    {
        $validated = $request->validated();

        $horario = HorarioBarbero::updateOrCreate(
            [
                'barbero_id' => $validated['barbero_id'],
                'dia_semana' => $validated['dia_semana'],
            ],
            [
                'hora_inicio' => $validated['hora_inicio'],
                'hora_fin' => $validated['hora_fin'],
            ]
        );

        return response()->json([
            'message' => 'Horario guardado exitosamente.',
            'data' => $horario
        ], 200);
    }
}
