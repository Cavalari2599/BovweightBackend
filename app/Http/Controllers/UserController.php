<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('rol');

        if ($request->has('buscar') && $request->buscar !== '') {
            $buscar = $request->buscar;
            $query->where(function($q) use ($buscar) {
                $q->where('nombre_usuario', 'like', "%$buscar%")
                  ->orWhere('apellido1_usuario', 'like', "%$buscar%")
                  ->orWhere('apellido2_usuario', 'like', "%$buscar%")
                  ->orWhere('correo', 'like', "%$buscar%")
                  ->orWhere('identificacion_usuario', 'like', "%$buscar%");
            });
        }

        return response()->json($query->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'identificacion_usuario' => 'required|integer|unique:users,identificacion_usuario',
            'correo'                 => 'required|email|unique:users,correo',
            'clave'                  => 'required|string|min:8',
            'id_rol'                 => 'required|integer|exists:roles,id_rol',
            'nombre_usuario'         => 'required|string|max:100',
            'apellido1_usuario'      => 'required|string|max:100',
            'apellido2_usuario'      => 'nullable|string|max:100',
        ]);

        $user = User::create([
            'identificacion_usuario' => $request->identificacion_usuario,
            'correo'                 => $request->correo,
            'clave'                  => Hash::make($request->clave),
            'id_rol'                 => $request->id_rol,
            'estado'                 => true,
            'nombre_usuario'         => $request->nombre_usuario,
            'apellido1_usuario'      => $request->apellido1_usuario,
            'apellido2_usuario'      => $request->apellido2_usuario,
        ]);

        return response()->json([
            'message' => 'Usuario creado correctamente.',
            'usuario' => $user,
        ], 201);
    }

    public function show($id)
    {
        $user = User::with('rol')->find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        return response()->json($user, 200);
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $request->validate([
            'correo'            => 'sometimes|email|unique:users,correo,' . $id . ',identificacion_usuario',
            'clave'             => 'sometimes|string|min:8',
            'id_rol'            => 'sometimes|integer|exists:roles,id_rol',
            'nombre_usuario'    => 'sometimes|string|max:100',
            'apellido1_usuario' => 'sometimes|string|max:100',
            'apellido2_usuario' => 'nullable|string|max:100',
        ]);

        if ($request->has('clave')) {
            $request->merge(['clave' => Hash::make($request->clave)]);
        }

        $user->update($request->except(['identificacion_usuario']));

        return response()->json([
            'message' => 'Usuario actualizado correctamente.',
            'usuario' => $user,
        ], 200);
    }

    public function toggleEstado($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado.'], 404);
        }

        $user->estado = !$user->estado;
        $user->save();

        $estadoTexto = $user->estado ? 'activado' : 'desactivado';

        return response()->json([
            'message' => "Usuario $estadoTexto correctamente.",
            'usuario' => $user,
        ], 200);
    }
}