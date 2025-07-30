<?php

namespace App\Http\Middleware; 
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }
        
        $user = Auth::user();
        $userRole = $user->role ? $user->role->nombre : null;
        
        // Debug: log para desarrollo
        if (config('app.debug')) {
            Log::info('CheckRole middleware:', [
                'authenticated' => Auth::check(),
                'user_role' => $userRole,
                'required_roles' => $roles,
                'user_id' => $user->id
            ]);
        }
        
        if (!$userRole || !in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Acceso no autorizado.',
                'user_role' => $userRole,
                'required_roles' => $roles
            ], 403);
        }
        
        return $next($request);
    }
}
