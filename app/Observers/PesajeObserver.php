<?php

namespace App\Observers;

use App\Models\Pesaje;
use App\Interfaces\HistorialServiceInterface;

class PesajeObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(Pesaje $pesaje): void
    {
        $this->historial->registrar('Registrar pesaje', 'pesajes', $pesaje->id_pesaje);
    }

    public function updated(Pesaje $pesaje): void
    {
        $this->historial->registrar('Editar pesaje', 'pesajes', $pesaje->id_pesaje);
    }

    public function deleted(Pesaje $pesaje): void
    {
        $this->historial->registrar('Eliminar pesaje', 'pesajes', $pesaje->id_pesaje);
    }
}