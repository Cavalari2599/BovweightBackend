<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Finca extends Model
{
    protected $table = 'fincas';
    protected $primaryKey = 'id_finca';
    public $timestamps = false;

    protected $fillable = [
        'nombre_finca',
        'ubicacion_finca',
        'identificacion_usuario',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function animales()
    {
        return $this->hasMany(Animal::class, 'id_finca', 'id_finca');
    }

    public function ayudantes()
    {
        return $this->hasMany(Ayudante::class, 'id_finca', 'id_finca');
    }

    public function atiende()
    {
        return $this->hasMany(Atiende::class, 'id_finca', 'id_finca');
    }
}