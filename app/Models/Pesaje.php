<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesaje extends Model
{
    protected $table = 'pesajes';
    protected $primaryKey = 'id_pesaje';
    public $timestamps = false;

    protected $fillable = [
        'foto_pesaje',
        'fecha_pesaje',
        'peso_aproximado',
        'n_arete',
    ];

    public function animal()
    {
        return $this->belongsTo(Animal::class, 'n_arete', 'n_arete');
    }
}