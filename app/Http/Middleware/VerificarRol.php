<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class VerificarRol
{
    public function handle(Request $request, Closure $next, string ...$roles): mixed
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'No autenticado.'], 401);
        }

        $rolesPermitidos = [
            'tecnico'     => 4,
            'veterinario' => 2,
            'ayudante'    => 3,
            'ganadero'    => 1,
        ];

        foreach ($roles as $rol) {
            if (isset($rolesPermitidos[$rol]) && $user->id_rol === $rolesPermitidos[$rol]) {
                return $next($request);
            }
        }

        return response()->json(['message' => 'No tienes permisos para realizar esta acción.'], 403);
    }
}