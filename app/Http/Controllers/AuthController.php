<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\HistorialAcciones;
class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'correo' => 'required|email',
            'clave' => 'required|string',
        ]);

        $user = User::where('correo', $request->correo)->first();

        if (!$user || !Hash::check($request->clave, $user->clave)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        if (!$user->estado) {
            return response()->json([
                'message' => 'Usuario inactivo.',
            ], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
HistorialAcciones::create([
    'identificacion_usuario' => $user->identificacion_usuario,
    'accion'                 => 'Login',
    'tabla_afectada'         => 'users',
    'id_registro'            => $user->identificacion_usuario,
    'fecha_accion'           => now(),
]);
        return response()->json([
            'token' => $token,
            'usuario' => [
                'identificacion' => $user->identificacion_usuario,
                'nombre' => $user->nombre_usuario,
                'apellido' => $user->apellido1_usuario,
                'correo' => $user->correo,
                'rol' => $user->id_rol,
                'estado' => $user->estado,
            ],
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente.',
        ], 200);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'identificacion' => $user->identificacion_usuario,
            'nombre' => $user->nombre_usuario,
            'apellido' => $user->apellido1_usuario,
            'correo' => $user->correo,
            'rol' => $user->id_rol,
            'estado' => $user->estado,
        ], 200);
    }
}