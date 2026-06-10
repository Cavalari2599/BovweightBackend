<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Facades\AyudanteFacade;
use App\Strategies\OrdenarPorPeso;
use App\Strategies\OrdenarPorNombre;
use App\Strategies\OrdenarPorFechaNacimiento;

class AyudanteController extends Controller
{
    public function __construct(private AyudanteFacade $facade) {}

    private function idUsuario(Request $request): int
    {
        return $request->user()->identificacion_usuario;
    }

    public function getFinca(Request $request)
    {
        return response()->json($this->facade->getFinca($this->idUsuario($request)), 200);
    }

    public function getAnimales(Request $request)
    {
        $estrategia = match ($request->query('ordenar', 'peso')) {
            'nombre' => new OrdenarPorNombre(),
            'fecha'  => new OrdenarPorFechaNacimiento(),
            default  => new OrdenarPorPeso(),
        };

        return response()->json($this->facade->getAnimales($this->idUsuario($request), $estrategia), 200);
    }

    public function getAnimal(Request $request, string $nArete)
    {
        return response()->json($this->facade->getAnimal($this->idUsuario($request), $nArete), 200);
    }

    public function getPesajes(Request $request, string $nArete)
    {
        return response()->json($this->facade->getPesajes($this->idUsuario($request), $nArete), 200);
    }

    public function getTratamientos(Request $request, string $nArete)
    {
        return response()->json($this->facade->getTratamientos($this->idUsuario($request), $nArete), 200);
    }

    public function estimarPeso(Request $request)
    {
        $request->validate([
            'foto' => 'required|image|mimes:jpeg,jpg,png,webp|max:8192',
            'sexo' => 'required|in:M,F',
        ]);

        try {
            $peso = $this->facade->estimarPeso($request->file('foto'), $request->sexo);
        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['peso_estimado_kg' => $peso], 200);
    }

    public function crearPesaje(Request $request, string $nArete)
    {
        $request->validate([
            'peso_aproximado' => 'required|numeric|min:1',
            'foto'            => 'nullable|image|mimes:jpeg,jpg,png,webp|max:8192',
            'sexo'            => 'nullable|in:M,F',
        ]);

        $pesaje = $this->facade->crearPesaje(
            $this->idUsuario($request),
            $nArete,
            (float) $request->peso_aproximado,
            $request->file('foto'),
            $request->sexo,
        );

        return response()->json(['message' => 'Pesaje registrado correctamente.', 'pesaje' => $pesaje], 201);
    }
}
