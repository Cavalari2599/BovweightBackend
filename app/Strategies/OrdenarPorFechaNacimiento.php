<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

class OrdenarPorFechaNacimiento implements OrdenarAnimalesStrategy
{
    public function ordenar(Collection $animales): Collection
    {
        return $animales->sortByDesc('fecha_nacimiento')->values();
    }
}