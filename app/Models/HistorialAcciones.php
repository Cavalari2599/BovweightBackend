<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistorialAcciones extends Model
{
    protected $table = 'historial_acciones';
    protected $primaryKey = 'id_historial';
    public $timestamps = false;

    protected $fillable = [
        'identificacion_usuario',
        'accion',
        'tabla_afectada',
        'id_registro',
        'fecha_accion',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'identificacion_usuario', 'identificacion_usuario');
    }
}