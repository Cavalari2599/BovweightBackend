<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Tratamiento;

class VeterinarioController extends Controller
{
    public function getFincas(Request $request)
    {
        $usuario = $request->user();

        $fincas = Finca::with('usuario')
            ->whereHas('atiende', function($q) use ($usuario) {
                $q->where('identificacion_usuario', $usuario->identificacion_usuario);
            })->get();

        return response()->json($fincas, 200);
    }

    public function getAnimales(Request $request, $idFinca)
    {
        $usuario = $request->user();

        $atiende = $usuario->atiende()->where('id_finca', $idFinca)->exists();

        if (!$atiende) {
            return response()->json(['message' => 'No tienes acceso a esta finca.'], 403);
        }

        $animales = Animal::where('id_finca', $idFinca)->get();

        return response()->json($animales, 200);
    }

    public function getTratamientos(Request $request, $nArete)
    {
        $tratamientos = Tratamiento::with('usuario')
            ->where('n_arete', $nArete)
            ->orderBy('fecha_inicio', 'desc')
            ->get();

        return response()->json($tratamientos, 200);
    }

    public function createTratamiento(Request $request)
    {
        $request->validate([
            'tipo_tratamiento' => 'required|string|max:100',
            'medicamento'      => 'nullable|string|max:150',
            'descripcion'      => 'nullable|string',
            'fecha_inicio'     => 'required|date',
            'fecha_fin'        => 'nullable|date',
            'n_arete'          => 'required|string|exists:animales,n_arete',
        ]);

        $tratamiento = Tratamiento::create([
            'tipo_tratamiento'     => $request->tipo_tratamiento,
            'medicamento'          => $request->medicamento,
            'descripcion'          => $request->descripcion,
            'fecha_inicio'         => $request->fecha_inicio,
            'fecha_fin'            => $request->fecha_fin,
            'n_arete'              => $request->n_arete,
            'identificacion_usuario' => $request->user()->identificacion_usuario,
        ]);

        return response()->json([
            'message'     => 'Tratamiento registrado correctamente.',
            'tratamiento' => $tratamiento,
        ], 201);
    }

    public function updateTratamiento(Request $request, $id)
    {
        $tratamiento = Tratamiento::find($id);

        if (!$tratamiento) {
            return response()->json(['message' => 'Tratamiento no encontrado.'], 404);
        }

        $request->validate([
            'tipo_tratamiento' => 'sometimes|string|max:100',
            'medicamento'      => 'nullable|string|max:150',
            'descripcion'      => 'nullable|string',
            'fecha_inicio'     => 'sometimes|date',
            'fecha_fin'        => 'nullable|date',
        ]);

        $tratamiento->update($request->all());

        return response()->json([
            'message'     => 'Tratamiento actualizado correctamente.',
            'tratamiento' => $tratamiento,
        ], 200);
    }

    public function getPesajes(Request $request, $nArete)
    {
        $pesajes = Pesaje::where('n_arete', $nArete)
            ->orderBy('fecha_pesaje', 'desc')
            ->get();

        return response()->json($pesajes, 200);
    }
}