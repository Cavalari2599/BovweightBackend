<?php

namespace App\Observers;

use App\Models\Tratamiento;
use App\Interfaces\HistorialServiceInterface;

class TratamientoObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Tratamiento $tratamiento): void
    {
        $this->historial->registrar('Crear tratamiento', 'tratamientos', $tratamiento->id_tratamiento);
    }

    public function updated(Tratamiento $tratamiento): void
    {
        $this->historial->registrar('Editar tratamiento', 'tratamientos', $tratamiento->id_tratamiento);
    }

    public function deleted(Tratamiento $tratamiento): void
    {
        $this->historial->registrar('Eliminar tratamiento', 'tratamientos', $tratamiento->id_tratamiento);
    }
}