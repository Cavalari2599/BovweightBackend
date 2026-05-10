<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistorialController;
use App\Models\Rol;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FincaController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:sanctum', 'tecnico'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index']);
    Route::post('/usuarios', [UserController::class, 'store']);
    Route::get('/usuarios/{id}', [UserController::class, 'show']);
    Route::put('/usuarios/{id}', [UserController::class, 'update']);
    Route::patch('/usuarios/{id}/estado', [UserController::class, 'toggleEstado']);
    Route::get('/roles', function () {
        return response()->json(Rol::all(), 200);
    });
    Route::get('/historial', [HistorialController::class, 'index']);
    Route::get('/dashboard', [DashboardController::class, 'index']);
     Route::get('/fincas', [FincaController::class, 'index']);
});