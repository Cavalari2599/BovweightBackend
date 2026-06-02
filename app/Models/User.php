<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'identificacion_usuario';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'identificacion_usuario',
        'correo',
        'clave',
        'id_rol',
        'estado',
        'nombre_usuario',
        'apellido1_usuario',
        'apellido2_usuario',
    ];

    protected $hidden = [
        'clave',
    ];

    public function rol()
    {
        return $this->belongsTo(Rol::class, 'id_rol', 'id_rol');
    }

    public function fincas()
    {
        return $this->hasMany(Finca::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function ayudante()
    {
        return $this->hasOne(Ayudante::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function atiende()
    {
        return $this->hasMany(Atiende::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function tratamientos()
    {
        return $this->hasMany(Tratamiento::class, 'identificacion_usuario', 'identificacion_usuario');
    }

    public function historial()
    {
        return $this->hasMany(HistorialAcciones::class, 'identificacion_usuario', 'identificacion_usuario');
    }
}