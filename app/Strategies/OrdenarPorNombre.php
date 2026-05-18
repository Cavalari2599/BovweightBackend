<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

class OrdenarPorNombre implements OrdenarAnimalesStrategy
{
    public function ordenar(Collection $animales): Collection
    {
        return $animales->sortBy('nombre_animal')->values();
    }
}