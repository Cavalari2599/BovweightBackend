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
        // Distingue la accion de recordatorio del resto de ediciones, para auditar claro.
        $cambios = array_keys($animal->getChanges());
        $soloRecordatorio = $cambios !== []
            && array_diff($cambios, ['proximo_pesaje', 'repetir_cada_dias']) === [];

        if ($soloRecordatorio) {
            $accion = $animal->proximo_pesaje ? 'Programar pesaje' : 'Quitar recordatorio de pesaje';
        } else {
            $accion = 'Editar animal';
        }

        $this->historial->registrar($accion, 'animales', $animal->n_arete);
    }

    public function deleted(Animal $animal): void
    {
        $this->historial->registrar('Eliminar animal', 'animales', $animal->n_arete);
    }
}