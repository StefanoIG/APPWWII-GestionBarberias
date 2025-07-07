<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barbero;
use App\Models\Barberia;
use App\Models\User;
use App\Models\Role;
use App\Http\Requests\StoreBarberoRequest;
use App\Http\Requests\UpdateBarberoRequest; // Asegúrate de crear este request
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BarberoController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        // El dueño solo puede ver los barberos de su barbería
        if ($user->role->nombre === 'dueño') {
            $barberia = $user->barberia;
            if (!$barberia) {
                return response()->json(['data' => []]);
            }
            $barberos = $barberia->barberos()->with('user', 'servicios')->get();
        } else {
            // Un cliente o admin puede ver barberos de una barbería específica (requiere query param)
            // Opcional: Implementar lógica para admin vea todos
            $barberos = Barbero::with('user', 'servicios')->get();
        }

        return response()->json($barberos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBarberoRequest $request)
    {
        $validated = $request->validated();

        $barbero = DB::transaction(function () use ($validated) {
            $roleBarbero = Role::where('nombre', 'barbero')->firstOrFail();
            $user = User::create([
                'nombre' => $validated['nombre'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'telefono' => $validated['telefono'],
                'role_id' => $roleBarbero->id,
            ]);

            $barbero = $user->barbero()->create([
                'barberia_id' => $validated['barberia_id'],
                'foto_url' => $validated['foto_url'] ?? null,
                'biografia' => $validated['biografia'] ?? null,
            ]);

            if (!empty($validated['servicios'])) {
                $barbero->servicios()->sync($validated['servicios']);
            }
            
            return $barbero;
        });

        return response()->json([
            'message' => 'Barbero registrado exitosamente.',
            'data' => $barbero->load('user', 'servicios')
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Barbero $barbero)
    {
        // Cualquiera puede ver el perfil de un barbero
        return response()->json($barbero->load('user', 'servicios', 'horarios'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBarberoRequest $request, Barbero $barbero)
    {
        // Autorización: solo el dueño de la barbería puede editar al barbero
        $this->authorize('update', $barbero); 

        $validated = $request->validated();
        
        DB::transaction(function () use ($validated, $barbero) {
            // Actualizar datos del usuario
            $barbero->user->update($validated['user_data']);
            
            // Actualizar datos del barbero
            $barbero->update($validated['barbero_data']);

            // Sincronizar servicios
            if (isset($validated['servicios'])) {
                $barbero->servicios()->sync($validated['servicios']);
            }
        });

        return response()->json([
            'message' => 'Barbero actualizado exitosamente.',
            'data' => $barbero->fresh()->load('user', 'servicios')
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Barbero $barbero)
    {
        // Autorización: solo el dueño de la barbería puede eliminar
        $this->authorize('delete', $barbero);

        // El registro del usuario se elimina en cascada gracias al onDelete('cascade')
        $barbero->delete();

        return response()->json(['message' => 'Barbero eliminado exitosamente.']);
    }
}
