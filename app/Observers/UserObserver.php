<?php

namespace App\Observers;

use App\Models\User;
use App\Interfaces\HistorialServiceInterface;

class UserObserver
{
    public function __construct(private HistorialServiceInterface $historial) {}

    public function created(User $user): void
    {
        $this->historial->registrar('Crear usuario', 'users', $user->identificacion_usuario);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('estado')) {
            $accion = $user->estado ? 'Activar usuario' : 'Desactivar usuario';
        } else {
            $accion = 'Editar usuario';
        }
        $this->historial->registrar($accion, 'users', $user->identificacion_usuario);
    }

    public function deleted(User $user): void
    {
        $this->historial->registrar('Eliminar usuario', 'users', $user->identificacion_usuario);
    }
}