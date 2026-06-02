<?php

namespace App\Observers;

use App\Models\Atiende;
use App\Interfaces\HistorialServiceInterface;

class AtiendeObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Atiende $atiende): void
    {
        $this->historial->registrar('Asignar veterinario a finca', 'atiende', $atiende->id_finca);
    }

    public function deleted(Atiende $atiende): void
    {
        $this->historial->registrar('Desasignar veterinario de finca', 'atiende', $atiende->id_finca);
    }
}