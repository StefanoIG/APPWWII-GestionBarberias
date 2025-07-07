<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barberia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AdminController extends Controller
{
    /**
     * Aprobar una barbería
     */
    public function approveBarberia(Barberia $barberia): JsonResponse
    {
        $barberia->update(['status' => 'approved']);
        
        return response()->json([
            'message' => 'Barbería aprobada exitosamente',
            'barberia' => $barberia
        ]);
    }
    
    /**
     * Rechazar una barbería
     */
    public function rejectBarberia(Barberia $barberia): JsonResponse
    {
        $barberia->update(['status' => 'rejected']);
        
        return response()->json([
            'message' => 'Barbería rechazada',
            'barberia' => $barberia
        ]);
    }
    
    /**
     * Bloquear una barbería
     */
    public function blockBarberia(Barberia $barberia): JsonResponse
    {
        $barberia->update(['status' => 'blocked']);
        
        return response()->json([
            'message' => 'Barbería bloqueada',
            'barberia' => $barberia
        ]);
    }
}
