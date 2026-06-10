<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\HistorialController;
use App\Models\Rol;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FincaController;
use App\Http\Controllers\VeterinarioController;
use App\Http\Controllers\GanaderoController;
use App\Http\Controllers\AyudanteController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:sanctum', 'rol:tecnico'])->group(function () {
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

Route::middleware(['auth:sanctum', 'rol:veterinario'])->group(function () {
    Route::get('/veterinario/fincas', [VeterinarioController::class, 'getFincas']);
    Route::get('/veterinario/fincas/{idFinca}/animales', [VeterinarioController::class, 'getAnimales']);
    Route::get('/veterinario/animales/{nArete}/tratamientos', [VeterinarioController::class, 'getTratamientos']);
    Route::post('/veterinario/tratamientos', [VeterinarioController::class, 'createTratamiento']);
    Route::put('/veterinario/tratamientos/{id}', [VeterinarioController::class, 'updateTratamiento']);
    Route::get('/veterinario/animales/{nArete}/pesajes', [VeterinarioController::class, 'getPesajes']);
});

Route::middleware(['auth:sanctum', 'rol:ganadero'])->group(function () {
    Route::get('/ganadero/fincas', [GanaderoController::class, 'getFincas']);
    Route::post('/ganadero/fincas', [GanaderoController::class, 'crearFinca']);
    Route::put('/ganadero/fincas/{idFinca}', [GanaderoController::class, 'editarFinca']);
    Route::get('/ganadero/fincas/{idFinca}/animales', [GanaderoController::class, 'getAnimales']);
    Route::get('/ganadero/fincas/{idFinca}/resumen', [GanaderoController::class, 'getResumenFinca']);
    Route::post('/ganadero/fincas/{idFinca}/animales', [GanaderoController::class, 'crearAnimal']);
    Route::put('/ganadero/animales/{nArete}', [GanaderoController::class, 'editarAnimal']);
    Route::get('/ganadero/animales/{nArete}/pesajes', [GanaderoController::class, 'getPesajes']);
    Route::post('/ganadero/estimar-peso', [GanaderoController::class, 'estimarPeso']);
    Route::post('/ganadero/animales/{nArete}/pesajes', [GanaderoController::class, 'crearPesaje']);
    Route::get('/ganadero/recordatorios', [GanaderoController::class, 'getRecordatorios']);
    Route::get('/ganadero/animales-todos', [GanaderoController::class, 'getTodosAnimales']);
    Route::put('/ganadero/animales/{nArete}/programar', [GanaderoController::class, 'programarPesaje']);
    Route::get('/ganadero/animales/{nArete}/tratamientos', [GanaderoController::class, 'getTratamientos']);
    Route::get('/ganadero/veterinarios', [GanaderoController::class, 'getVeterinarios']);
    Route::get('/ganadero/ayudantes', [GanaderoController::class, 'getAyudantes']);
    Route::post('/ganadero/fincas/{idFinca}/veterinarios', [GanaderoController::class, 'asignarVeterinario']);
    Route::delete('/ganadero/fincas/{idFinca}/veterinarios', [GanaderoController::class, 'desasignarVeterinario']);
    Route::post('/ganadero/fincas/{idFinca}/ayudantes', [GanaderoController::class, 'asignarAyudante']);
    Route::delete('/ganadero/ayudantes', [GanaderoController::class, 'desasignarAyudante']);
    Route::get('/ganadero/animales/{nArete}', [GanaderoController::class, 'getAnimal']);
    Route::get('/ganadero/animales/{nArete}/foto', [GanaderoController::class, 'getFoto']);
    Route::post('/ganadero/reportes', [GanaderoController::class, 'registrarReporte']);
    Route::get('/ganadero/fincas/{idFinca}/veterinarios', [GanaderoController::class, 'getVeterinariosAsignados']);
    Route::get('/ganadero/fincas/{idFinca}/ayudantes', [GanaderoController::class, 'getAyudantesAsignados']);
});

Route::middleware(['auth:sanctum', 'rol:ayudante'])->group(function () {
    Route::get('/ayudante/finca', [AyudanteController::class, 'getFinca']);
    Route::get('/ayudante/animales', [AyudanteController::class, 'getAnimales']);
    Route::get('/ayudante/animales/{nArete}', [AyudanteController::class, 'getAnimal']);
    Route::get('/ayudante/animales/{nArete}/pesajes', [AyudanteController::class, 'getPesajes']);
    Route::get('/ayudante/animales/{nArete}/tratamientos', [AyudanteController::class, 'getTratamientos']);
    Route::post('/ayudante/estimar-peso', [AyudanteController::class, 'estimarPeso']);
    Route::post('/ayudante/animales/{nArete}/pesajes', [AyudanteController::class, 'crearPesaje']);
});