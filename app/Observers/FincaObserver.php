<?php

namespace App\Observers;

use App\Models\Finca;
use App\Interfaces\HistorialServiceInterface;

class FincaObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Finca $finca): void
    {
        $this->historial->registrar('Crear finca', 'fincas', $finca->id_finca);
    }

    public function updated(Finca $finca): void
    {
        $this->historial->registrar('Editar finca', 'fincas', $finca->id_finca);
    }

    public function deleted(Finca $finca): void
    {
        $this->historial->registrar('Eliminar finca', 'fincas', $finca->id_finca);
    }
}