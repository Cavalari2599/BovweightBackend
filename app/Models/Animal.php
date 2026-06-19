<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Animal extends Model
{
    protected $table = 'animales';
    protected $primaryKey = 'n_arete';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'n_arete',
        'nombre_animal',
        'raza',
        'sexo',
        'edad',
        'peso',
        'proximo_pesaje',
        'repetir_cada_dias',
        'estado',
        'fecha_nacimiento',
        'foto_animal',
        'id_finca',
    ];

    public function finca()
    {
        return $this->belongsTo(Finca::class, 'id_finca', 'id_finca');
    }

    public function pesajes()
    {
        return $this->hasMany(Pesaje::class, 'n_arete', 'n_arete');
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'n_arete', 'n_arete');
    }
}