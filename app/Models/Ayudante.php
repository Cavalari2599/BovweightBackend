<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ayudante extends Model
{
    protected $table = 'ayudantes';
    protected $primaryKey = 'identificacion_usuario';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'identificacion_usuario',
        'id_finca',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function finca()
    {
        return $this->belongsTo(Finca::class, 'id_finca', 'id_finca');
    }
}