<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Rutas públicas (No necesitan token)
// Rutas públicas
Route::post('/login', [AuthController::class, 'login']);

// Rutas protegidas (Requieren Token JWT válido)
Route::middleware('auth:api')->group(function () {
    
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Listado
    Route::middleware(['permission:view-maestros'])->group(function () {
        Route::get('/maestros', [AuthController::class, 'index']);
    });

    // Registro
    Route::middleware(['permission:register-maestro'])->group(function () {
        Route::post('/register-maestro', [AuthController::class, 'register']);
    });

    // Edición Independiente
    Route::middleware(['permission:edit-maestros'])->group(function () {
        Route::put('/edit-maestro/{id}', [AuthController::class, 'update']);
    });

    // Eliminación Independiente
    Route::middleware(['permission:delete-maestros'])->group(function () {
        Route::delete('/delete-maestro/{id}', [AuthController::class, 'destroy']);
    });
});