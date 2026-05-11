<?php

namespace App\Observers;

use App\Models\User;
use App\Models\HistorialAcciones;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    private function registrar($accion, $id)
    {
        $usuario = Auth::user();
        if (!$usuario) return;

        HistorialAcciones::create([
            'identificacion_usuario' => $usuario->identificacion_usuario,
            'accion'                 => $accion,
            'tabla_afectada'         => 'users',
            'id_registro'            => $id,
            'fecha_accion'           => now(),
        ]);
    }

    public function created(User $user): void
    {
        $this->registrar('Crear usuario', $user->identificacion_usuario);
    }

    public function updated(User $user): void
    {
        if ($user->wasChanged('estado')) {
            $accion = $user->estado ? 'Activar usuario' : 'Desactivar usuario';
        } else {
            $accion = 'Editar usuario';
        }
        $this->registrar($accion, $user->identificacion_usuario);
    }

    public function deleted(User $user): void
    {
        $this->registrar('Eliminar usuario', $user->identificacion_usuario);
    }
}