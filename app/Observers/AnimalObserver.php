<?php

namespace App\Observers;

use App\Models\Animal;
use App\Interfaces\HistorialServiceInterface;

class AnimalObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Animal $animal): void
    {
        $this->historial->registrar('Crear animal', 'animales', $animal->n_arete);
    }

    public function updated(Animal $animal): void
    {
        $this->historial->registrar('Editar animal', 'animales', $animal->n_arete);
    }

    public function deleted(Animal $animal): void
    {
        $this->historial->registrar('Eliminar animal', 'animales', $animal->n_arete);
    }
}