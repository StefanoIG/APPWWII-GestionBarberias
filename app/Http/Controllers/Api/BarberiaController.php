<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barberia;
use App\Http\Requests\StoreBarberiaRequest;
use App\Http\Requests\UpdateBarberiaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BarberiaController extends Controller
{
    use AuthorizesRequests;
    // Listar barberías. Admin ve todas, dueño ve solo la suya.
    public function index()
    {
        $user = Auth::user();
        $userRole = $user->role ? $user->role->nombre : null;
        
        if ($userRole === 'admin') {
            $barberias = Barberia::with('owner')->latest()->get();
        } else {
            $barberias = Barberia::where('owner_id', $user->id)->get();
        }
        return response()->json($barberias);
    }

    /**
     * Listar todas las barberías del sistema (solo para administradores)
     */
    public function adminIndex()
    {
        $barberias = Barberia::with(['owner', 'barberos.user', 'servicios'])
            ->latest()
            ->get();
            
        return response()->json($barberias);
    }

    // El dueño registra su barbería.
    public function store(StoreBarberiaRequest $request)
    {
        // Asignamos el owner_id con el usuario autenticado.
        $barberia = Barberia::create(array_merge($request->validated(), [
            'owner_id' => Auth::id()
        ]));

        return response()->json([
            'message' => 'Barbería registrada. Esperando aprobación del administrador.',
            'data' => $barberia
        ], 201);
    }

    // Muestra detalles de una barbería.
    public function show(Barberia $barberia)
    {
        // Autorización: El admin puede ver cualquiera, el dueño solo la suya.
        $this->authorize('view', $barberia);
        $barberia->load('owner', 'servicios', 'barberos');
        return response()->json($barberia);
    }

    // El dueño actualiza su barbería.
    public function update(UpdateBarberiaRequest $request, Barberia $barberia)
    {
        $this->authorize('update', $barberia);
        $barberia->update($request->validated());
        return response()->json([
            'message' => 'Barbería actualizada exitosamente.',
            'data' => $barberia
        ]);
    }

    // Admin o dueño pueden eliminar (con precaución).
    public function destroy(Barberia $barberia)
    {
        $this->authorize('delete', $barberia);
        $barberia->delete();
        return response()->json(['message' => 'Barbería eliminada.'], 200);
    }
}
