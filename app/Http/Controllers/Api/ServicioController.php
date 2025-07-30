<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Servicio;
use App\Models\Barberia;
use App\Http\Requests\StoreServicioRequest;
use App\Http\Requests\UpdateServicioRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServicioController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     * Lista los servicios de una barbería específica.
     */
    public function index(Request $request)
    {
        $request->validate(['barberia_id' => 'required|exists:barberias,id']);
        $servicios = Servicio::where('barberia_id', $request->barberia_id)
            ->with(['barberos.user'])
            ->get();
        return response()->json($servicios);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreServicioRequest $request)
    {
        $servicio = Servicio::create($request->validated());

        return response()->json([
            'message' => 'Servicio creado exitosamente.',
            'data' => $servicio
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Servicio $servicio)
    {
        $servicio->load(['barberos.user']);
        return response()->json($servicio);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateServicioRequest $request, Servicio $servicio)
    {
        $servicio->update($request->validated());

        return response()->json([
            'message' => 'Servicio actualizado exitosamente.',
            'data' => $servicio
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Servicio $servicio)
    {
        // Autorización: solo el dueño puede eliminar
        $this->authorize('delete', $servicio); // Necesitarás una policy

        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado exitosamente.']);
    }
}
