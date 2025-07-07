<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use App\Models\Cita;
use App\Http\Requests\StoreCalificacionRequest;

class CalificacionController extends Controller
{
    public function store(StoreCalificacionRequest $request)
    {
        $validated = $request->validated();
        $cita = Cita::findOrFail($validated['cita_id']);

        $calificacion = Calificacion::create([
            'cita_id' => $cita->id,
            'cliente_id' => $cita->cliente_id,
            'barbero_id' => $cita->barbero_id,
            'puntuacion' => $validated['puntuacion'],
            'comentario' => $validated['comentario'] ?? null,
        ]);

        return response()->json(['message' => 'Gracias por tu calificación.', 'data' => $calificacion], 201);
    }
}
