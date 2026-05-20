<?php

namespace App\Observers;

use App\Models\Ayudante;
use App\Interfaces\HistorialServiceInterface;

class AyudanteObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Ayudante $ayudante): void
    {
        $this->historial->registrar('Asignar ayudante a finca', 'ayudantes', $ayudante->id_finca);
    }

    public function deleted(Ayudante $ayudante): void
    {
        $this->historial->registrar('Desasignar ayudante de finca', 'ayudantes', $ayudante->id_finca);
    }
}