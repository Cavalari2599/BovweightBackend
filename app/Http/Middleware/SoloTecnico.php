<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SoloTecnico
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        if (!$user || $user->id_rol !== 4)  {
            return response()->json([
                'message' => 'No tienes permisos para realizar esta acción.'
            ], 403);
        }

        return $next($request);
    }
}