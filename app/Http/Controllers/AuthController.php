<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
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
                'nombre'         => $user->nombre_usuario,
                'apellido'       => $user->apellido1_usuario,
                'correo'         => $user->correo,
                'rol'            => $user->id_rol,
                'estado'         => $user->estado,
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
            'nombre'         => $user->nombre_usuario,
            'apellido'       => $user->apellido1_usuario,
            'correo'         => $user->correo,
            'rol'            => $user->id_rol,
            'estado'         => $user->estado,
        ], 200);
    }

    public function forgotPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Validación fallida',
                'errores' => $validator->errors(),
            ], 422);
        }

        $usuario = User::where('correo', $request->correo)->first();

        if (!$usuario) {
            return response()->json([
                'mensaje' => 'El correo no existe en el sistema',
            ], 422);
        }

        $nuevaContrasena = Str::random(10);

        $usuario->update([
            'clave' => Hash::make($nuevaContrasena),
        ]);

        Mail::raw("Tu nueva contraseña temporal es: $nuevaContrasena — Por favor cámbiala después de iniciar sesión.", function ($message) use ($request) {
            $message->to($request->correo)
                    ->subject('Restablecimiento de contraseña - BovWeight CR');
        });

        return response()->json([
            'mensaje' => 'Se ha enviado una nueva contraseña al correo proporcionado',
        ], 200);
    }
}