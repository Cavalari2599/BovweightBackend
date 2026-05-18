<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tratamiento extends Model
{
    protected $table = 'tratamientos';
    protected $primaryKey = 'id_tratamiento';
    public $timestamps = false;

    protected $fillable = [
        'tipo_tratamiento',
        'medicamento',
        'descripcion',
        'fecha_inicio',
        'fecha_fin',
        'n_arete',
        'identificacion_usuario',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'n_arete', 'n_arete');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'identificacion_usuario', 'identificacion_usuario');
    }
}