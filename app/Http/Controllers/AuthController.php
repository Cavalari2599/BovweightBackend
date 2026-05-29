<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
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

        $token = Str::random(64);

        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $request->correo],
            [
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]
        );

        $enlace = "http://localhost:5173/reset-password?token={$token}&correo={$request->correo}";

        Mail::raw(
            "Recibiste una solicitud para restablecer tu contraseña.\n\nHaz clic en el siguiente enlace para continuar:\n\n{$enlace}\n\nEste enlace expira en 60 minutos.\n\nSi no solicitaste esto, ignora este correo.",
            function ($message) use ($request) {
                $message->to($request->correo)
                        ->subject('Restablecimiento de contraseña - BovWeight CR');
            }
        );

        return response()->json([
            'mensaje' => 'Se ha enviado un enlace de restablecimiento al correo proporcionado',
        ], 200);
    }

    public function resetPassword(Request $request)
    {
        \Log::info('Reset password request', [
    'correo' => $request->correo,
    'token' => $request->token,
]);
        $validator = Validator::make($request->all(), [
            'correo' => 'required|email',
            'token'  => 'required|string',
            'clave'  => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'mensaje' => 'Validación fallida',
                'errores' => $validator->errors(),
            ], 422);
        }

        $registro = DB::table('password_reset_tokens')
            ->where('email', $request->correo)
            ->first();

        if (!$registro || !Hash::check($request->token, $registro->token)) {
            return response()->json([
                'mensaje' => 'El enlace de restablecimiento es inválido o ha expirado',
            ], 422);
        }

        if (now()->diffInMinutes($registro->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->correo)->delete();
            return response()->json([
                'mensaje' => 'El enlace de restablecimiento ha expirado',
            ], 422);
        }

        $usuario = User::where('correo', $request->correo)->first();

        $usuario->update([
            'clave' => Hash::make($request->clave),
        ]);

        DB::table('password_reset_tokens')->where('email', $request->correo)->delete();

        HistorialAcciones::create([
            'identificacion_usuario' => $usuario->identificacion_usuario,
            'accion'                 => 'Restablecimiento de contraseña',
            'tabla_afectada'         => 'usuarios',
            'id_registro'            => $usuario->identificacion_usuario,
            'fecha_accion'           => now(),
        ]);

        return response()->json([
            'mensaje' => 'Contraseña restablecida exitosamente',
        ], 200);
    }
}