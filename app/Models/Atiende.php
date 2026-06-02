<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atiende extends Model
{
    protected $table = 'atiende';
    protected $primaryKey = ['identificacion_usuario', 'id_finca'];
    public $incrementing = false;
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

    /**
     * PK compuesta: ubica el registro por ambas llaves para que
     * update()/delete() por instancia funcionen y disparen observers.
     */
    protected function setKeysForSaveQuery($query)
    {
        return $query
            ->where('identificacion_usuario', $this->getAttribute('identificacion_usuario'))
            ->where('id_finca', $this->getAttribute('id_finca'));
    }
}