<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// Importar todos los controladores
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AdminController;
use App\Http\Controllers\Api\BarberiaController;
use App\Http\Controllers\Api\BarberoController;
use App\Http\Controllers\Api\CitaController;
use App\Http\Controllers\Api\ServicioController;
use App\Http\Controllers\Api\HorarioController;
use App\Http\Controllers\Api\CalificacionController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// --- RUTAS PÚBLICAS ---
// No requieren autenticación

// Autenticación
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Servicios (Visualización pública)
Route::get('/servicios', [ServicioController::class, 'index'])->name('servicios.index');
Route::get('/servicios/{servicio}', [ServicioController::class, 'show'])->name('servicios.show');


// --- RUTAS PROTEGIDAS ---
// Requieren autenticación con Sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Autenticación
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/usuario', [AuthController::class, 'update']);


    // Barberías (Cualquier usuario autenticado puede verlas)
    Route::apiResource('barberias', BarberiaController::class)->except(['store', 'update', 'destroy']);

    // Citas (Lógica específica por rol)
    Route::prefix('citas')->group(function () {
        Route::get('/', [CitaController::class, 'index']); // Cada rol ve sus citas (incluye admin)
        Route::post('/', [CitaController::class, 'store'])->middleware('role:cliente');
        Route::post('/{cita}/cancelar', [CitaController::class, 'cancelar'])->middleware('role:cliente');
        Route::post('/{cita}/upload-comprobante', [CitaController::class, 'uploadComprobante'])->middleware('role:cliente');
        Route::post('/{cita}/completar', [CitaController::class, 'completar'])->middleware('role:barbero');
    });
    
    // Barberos (Acceso general para consulta, gestión por roles específicos)
    Route::get('/barberos', [BarberoController::class, 'index']); // Permite consulta general
    
    // Calificaciones (Solo clientes)
    Route::post('/calificaciones', [CalificacionController::class, 'store'])->middleware('role:cliente');

    // --- RUTAS DE DASHBOARD ---
    // Para administradores y dueños
    Route::middleware('role:admin,dueño')->group(function () {
        Route::get('/dashboard/stats', [DashboardController::class, 'getStats']);
        Route::get('/dashboard/citas-proximas', [DashboardController::class, 'getCitasProximas']);
    });

    // --- RUTAS ESPECÍFICAS PARA DUEÑOS ---
    Route::middleware('role:dueño')->group(function () {
        // Gestión completa de su barbería
        Route::post('/barberias', [BarberiaController::class, 'store']);
        Route::put('/barberias/{barberia}', [BarberiaController::class, 'update']);
        Route::delete('/barberias/{barberia}', [BarberiaController::class, 'destroy']);
        
        // Gestión de Barberos de su barbería (rutas específicas)
        Route::post('/barberos', [BarberoController::class, 'store']);
        Route::put('/barberos/{barbero}', [BarberoController::class, 'update']);
        Route::delete('/barberos/{barbero}', [BarberoController::class, 'destroy']);
        Route::get('/barberos/{barbero}', [BarberoController::class, 'show']);
        
        // Gestión de Servicios
        Route::post('/servicios', [ServicioController::class, 'store']);
        Route::put('/servicios/{servicio}', [ServicioController::class, 'update']);
        Route::delete('/servicios/{servicio}', [ServicioController::class, 'destroy']);
        
        // Gestión de Horarios
        Route::post('/horarios', [HorarioController::class, 'store']);

        // Gestión de Pagos de Citas
        Route::get('/dueño/citas/pendientes-verificacion', [CitaController::class, 'listPendingVerification']);
        Route::post('/dueño/citas/{cita}/verificar-pago', [CitaController::class, 'verifyPayment']);
        Route::post('/dueño/citas/{cita}/rechazar-pago', [CitaController::class, 'rejectPayment']);
    });


    // --- RUTAS ESPECÍFICAS PARA ADMINISTRADORES ---
    Route::middleware('role:admin')->group(function () {
        // Gestión de estado de Barberías
        Route::prefix('admin/barberias/{barberia}')->group(function () {
            Route::post('/approve', [AdminController::class, 'approveBarberia']);
            Route::post('/reject', [AdminController::class, 'rejectBarberia']);
            Route::post('/block', [AdminController::class, 'blockBarberia']);
            Route::get('/users', [AdminController::class, 'index']);
        });      
        
        // El Admin puede acceder a todas las citas del sistema
        Route::get('/admin/citas', [CitaController::class, 'adminIndex']);
        
        // El Admin puede ver todas las barberías con gestión completa
        Route::get('/admin/barberias', [BarberiaController::class, 'adminIndex']);
        Route::put('/admin/barberias/{barberia}', [BarberiaController::class, 'update']);
        Route::delete('/admin/barberias/{barberia}', [BarberiaController::class, 'destroy']);
    });
});
