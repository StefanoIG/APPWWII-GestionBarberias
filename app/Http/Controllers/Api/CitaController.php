<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Servicio;
use App\Http\Requests\StoreCitaRequest;
use App\Http\Requests\UploadComprobanteRequest;
use App\Events\ComprobanteSubido;
use App\Events\CitaAgendada; 
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;


class CitaController extends Controller
{
    use AuthorizesRequests;

    /**
     * Marcar una cita como completada (solo barbero asignado puede hacerlo)
     */
    public function completar(Cita $cita)
    {
        $this->authorize('update', $cita); // Solo el barbero asignado puede completar

        if ($cita->estado !== 'confirmada') {
            return response()->json(['message' => 'Solo se pueden completar citas confirmadas.'], 400);
        }

        $cita->update(['estado' => 'completada']);

        // Aquí podrías disparar un evento de CitaCompletada si lo deseas
        // \App\Events\CitaCompletada::dispatch($cita);

        return response()->json(['message' => 'Cita marcada como completada.']);
    }
    
    /**
     * Listar citas según el rol del usuario
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $userRole = $user->role ? $user->role->nombre : null;
        
        if ($userRole === 'cliente') {
            // Clientes ven solo sus citas
            $citas = Cita::where('cliente_id', $user->id)
                ->with(['barberia', 'barbero.user', 'servicio', 'calificacion'])
                ->orderBy('fecha', 'desc')
                ->get();
        } elseif ($userRole === 'barbero') {
            // Barberos ven solo las citas asignadas a ellos
            $citas = Cita::where('barbero_id', $user->barbero->id)
                ->with(['cliente', 'barberia', 'servicio', 'calificacion'])
                ->orderBy('fecha', 'desc')
                ->get();
        } elseif ($userRole === 'dueño') {
            // Dueños ven las citas de su barbería
            $citas = Cita::whereHas('barberia', function($query) use ($user) {
                $query->where('owner_id', $user->id);
            })
            ->with(['cliente', 'barbero.user', 'servicio', 'calificacion'])
            ->orderBy('fecha', 'desc')
            ->get();
        } elseif ($userRole === 'admin') {
            // Admins ven todas las citas
            $citas = Cita::with(['cliente', 'barberia', 'barbero.user', 'servicio', 'calificacion'])
                ->orderBy('fecha', 'desc')
                ->get();
        } else {
            // Para otros roles, devuelve array vacío
            $citas = collect([]);
        }
        return response()->json($citas);
    }

    /**
     * Mostrar una cita específica con su calificación si existe
     */
    public function show(Cita $cita)
    {
        $cita->load(['cliente', 'barberia', 'barbero.user', 'servicio', 'calificacion']);
        return response()->json($cita);
    }
    
    /**
     * Listar todas las citas del sistema (solo para administradores)
     */
    public function adminIndex(Request $request)
    {
        $citas = Cita::with(['cliente', 'barberia', 'barbero.user', 'servicio'])
            ->orderBy('fecha', 'desc')
            ->get();
            
        return response()->json($citas);
    }
    
    public function store(StoreCitaRequest $request)
    {
        $validated = $request->validated();
        $servicio = Servicio::findOrFail($validated['servicio_id']);

        $cita = Cita::create([
            'cliente_id' => Auth::id(),
            'barberia_id' => $validated['barberia_id'],
            'barbero_id' => $validated['barbero_id'],
            'servicio_id' => $validated['servicio_id'],
            'fecha' => $validated['fecha'],
            'hora' => $validated['hora'],
            'metodo_pago' => $validated['metodo_pago'],
            'total' => $servicio->precio,
            'estado' => 'pendiente',
            'estado_pago' => $validated['metodo_pago'] === 'en_local' ? 'pagado_en_local' : 'pendiente',
        ]);

        if ($cita->metodo_pago === 'en_local') {
            $cita->estado = 'confirmada';
            $cita->save();
        }

        // 2. Disparar el evento con la cita recién creada
        CitaAgendada::dispatch($cita);

        return response()->json(['message' => 'Cita creada exitosamente.', 'data' => $cita], 201);
    }

    public function cancelar(Cita $cita)
    {
        $this->authorize('update', $cita);

        $horaCita = Carbon::parse($cita->fecha . ' ' . $cita->hora);

        if (now()->diffInHours($horaCita) < 1) {
            return response()->json(['message' => 'No puedes cancelar una cita con menos de 1 hora de antelación.'], 403);
        }

        $cita->update(['estado' => 'cancelada']);

        return response()->json(['message' => 'Cita cancelada.']);
    }

    public function uploadComprobante(UploadComprobanteRequest $request, Cita $cita)
    {
        $this->authorize('update', $cita);

        $path = $request->file('comprobante')->store('comprobantes', 'public');

        $cita->update([
            'comprobante_url' => $path,
            'codigo_transaccion' => $request->input('codigo_transaccion'),
            'estado_pago' => 'pendiente_verificacion'
        ]);
        
        ComprobanteSubido::dispatch($cita);

        return response()->json(['message' => 'Comprobante subido. Esperando verificación.']);
    }

    public function listPendingVerification()
    {
        $dueño = Auth::user();
        $barberiaId = $dueño->barberia->id;

        $citas = Cita::where('barberia_id', $barberiaId)
            ->where('estado_pago', 'pendiente_verificacion')
            ->with('cliente', 'barbero', 'servicio')
            ->get();
        
        $citas->each(function ($cita) {
            if ($cita->comprobante_url) {
                $cita->comprobante_temp_url = Storage::url($cita->comprobante_url);
            }
        });

        return response()->json($citas);
    }
    
    public function verifyPayment(Cita $cita)
    {
        $this->authorize('managePayment', $cita);

        $cita->update([
            'estado_pago' => 'verificado',
            'estado' => 'confirmada',
        ]);

        // Aquí deberías disparar un evento de CitaConfirmada para notificar al cliente
        // \App\Events\CitaConfirmada::dispatch($cita);

        return response()->json(['message' => 'Pago verificado y cita confirmada.']);
    }
    
    public function rejectPayment(Request $request, Cita $cita)
    {
        $this->authorize('managePayment', $cita);

        $request->validate(['motivo' => 'required|string|max:255']);

        $cita->update([
            'estado_pago' => 'rechazado',
            'estado' => 'cancelada',
        ]);

        // Aquí deberías disparar un evento de PagoRechazado para notificar al cliente
        // \App\Events\PagoRechazado::dispatch($cita, $request->motivo);

        return response()->json(['message' => 'Pago rechazado.']);
    }


}
