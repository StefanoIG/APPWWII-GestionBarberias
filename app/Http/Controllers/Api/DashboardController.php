<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cita;
use App\Models\Barberia;
use App\Models\Servicio;
use App\Models\Barbero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Obtener estadísticas del dashboard según el rol del usuario
     */
    public function getStats()
    {
        $user = Auth::user();
        $userRole = $user->role ? $user->role->nombre : null;
        
        if ($userRole === 'admin') {
            return $this->getAdminStats();
        } elseif ($userRole === 'dueño') {
            return $this->getDueñoStats();
        }
        
        return response()->json(['message' => 'No autorizado'], 403);
    }
    
    /**
     * Obtener citas próximas según el rol del usuario
     */
    public function getCitasProximas()
    {
        $user = Auth::user();
        $userRole = $user->role ? $user->role->nombre : null;
        
        if ($userRole === 'admin') {
            return $this->getAdminCitasProximas();
        } elseif ($userRole === 'dueño') {
            return $this->getDueñoCitasProximas();
        }
        
        return response()->json(['message' => 'No autorizado'], 403);
    }
    
    /**
     * Estadísticas para administradores (todo el sistema)
     */
    private function getAdminStats()
    {
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        
        $stats = [
            'citas_hoy' => Cita::whereDate('fecha', $hoy)->count(),
            'citas_pendientes' => Cita::where('estado', 'pendiente')->count(),
            'citas_completadas_mes' => Cita::where('estado', 'completada')
                ->whereBetween('fecha', [$inicioMes, Carbon::now()])
                ->count(),
            'total_servicios' => Servicio::count(),
            'total_barberias' => Barberia::count(),
            'total_barberos' => Barbero::count(),
            'barberias_activas' => Barberia::where('status', 'approved')->count(),
            'citas_pendientes_verificacion' => Cita::where('estado_pago', 'pendiente_verificacion')->count()
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Estadísticas para dueños (solo su barbería)
     */
    private function getDueñoStats()
    {
        $user = Auth::user();
        $barberia = $user->barberia;
        
        if (!$barberia) {
            return response()->json(['message' => 'No tienes una barbería asignada'], 404);
        }
        
        $hoy = Carbon::today();
        $inicioMes = Carbon::now()->startOfMonth();
        
        $stats = [
            'citas_hoy' => Cita::where('barberia_id', $barberia->id)
                ->whereDate('fecha', $hoy)
                ->count(),
            'citas_pendientes' => Cita::where('barberia_id', $barberia->id)
                ->where('estado', 'pendiente')
                ->count(),
            'citas_completadas_mes' => Cita::where('barberia_id', $barberia->id)
                ->where('estado', 'completada')
                ->whereBetween('fecha', [$inicioMes, Carbon::now()])
                ->count(),
            'total_servicios' => $barberia->servicios()->count(),
            'total_barberos' => $barberia->barberos()->count(),
            'citas_pendientes_verificacion' => Cita::where('barberia_id', $barberia->id)
                ->where('estado_pago', 'pendiente_verificacion')
                ->count(),
            'ingresos_mes' => Cita::where('barberia_id', $barberia->id)
                ->where('estado', 'completada')
                ->whereBetween('fecha', [$inicioMes, Carbon::now()])
                ->sum('total')
        ];
        
        return response()->json($stats);
    }
    
    /**
     * Citas próximas para administradores
     */
    private function getAdminCitasProximas()
    {
        $proximasCitas = Cita::with(['cliente', 'barberia', 'barbero.user', 'servicio'])
            ->where('fecha', '>=', Carbon::today())
            ->where('estado', '!=', 'cancelada')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->limit(10)
            ->get();
            
        return response()->json($proximasCitas);
    }
    
    /**
     * Citas próximas para dueños
     */
    private function getDueñoCitasProximas()
    {
        $user = Auth::user();
        $barberia = $user->barberia;
        
        if (!$barberia) {
            return response()->json(['message' => 'No tienes una barbería asignada'], 404);
        }
        
        $proximasCitas = Cita::with(['cliente', 'barbero.user', 'servicio'])
            ->where('barberia_id', $barberia->id)
            ->where('fecha', '>=', Carbon::today())
            ->where('estado', '!=', 'cancelada')
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->limit(10)
            ->get();
            
        return response()->json($proximasCitas);
    }
}
