<?php

namespace App\Services;

use App\Interfaces\HistorialServiceInterface;
use App\Models\HistorialAcciones;
use Illuminate\Support\Facades\Auth;

class HistorialService implements HistorialServiceInterface
{
    public function registrar(string $accion, string $tabla, $id): void
    {
        $usuario = Auth::user();
        if (!$usuario) return;

        HistorialAcciones::create([
            'identificacion_usuario' => $usuario->identificacion_usuario,
            'accion'                 => $accion,
            'tabla_afectada'         => $tabla,
            'id_registro'            => $id,
            'fecha_accion'           => now(),
        ]);
    }
}