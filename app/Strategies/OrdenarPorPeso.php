<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

class OrdenarPorPeso implements OrdenarAnimalesStrategy
{
    public function ordenar(Collection $animales): Collection
    {
        return $animales->sortByDesc('peso')->values();
    }
}