<?php

namespace App\Strategies;

use Illuminate\Support\Collection;

interface OrdenarAnimalesStrategy
{
    public function ordenar(Collection $animales): Collection;
}