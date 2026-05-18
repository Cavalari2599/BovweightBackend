<?php

namespace App\Facades;

use App\Models\Finca;
use App\Models\Animal;
use App\Models\Pesaje;
use App\Models\Tratamiento;
use App\Strategies\OrdenarAnimalesStrategy;
use Illuminate\Support\Collection;

class GanaderoFacade
{
    public function getFincas(int $idUsuario): Collection
    {
        return Finca::where('identificacion_usuario', $idUsuario)->get();
    }

    public function getAnimales(int $idFinca, OrdenarAnimalesStrategy $estrategia): Collection
    {
        $animales = Animal::where('id_finca', $idFinca)->get();
        return $estrategia->ordenar($animales);
    }

    public function getPesajes(string $nArete): Collection
    {
        return Pesaje::where('n_arete', $nArete)
            ->orderBy('fecha_pesaje', 'desc')
            ->get();
    }

    public function getTratamientos(string $nArete): Collection
    {
        return Tratamiento::with('usuario')
            ->where('n_arete', $nArete)
            ->orderBy('fecha_inicio', 'desc')
            ->get();
    }

    public function getResumenFinca(int $idFinca): array
    {
        $animales = Animal::where('id_finca', $idFinca)->get();

        return [
            'total_animales' => $animales->count(),
            'peso_promedio'  => round($animales->avg('peso'), 2),
            'peso_maximo'    => $animales->max('peso'),
            'peso_minimo'    => $animales->min('peso'),
        ];
    }
}