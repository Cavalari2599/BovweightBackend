<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'identificacion_usuario' => 100001,
                'correo' => 'ganadero@bovweight.com',
                'clave' => Hash::make('password123'),
                'id_rol' => 1,
                'estado' => true,
                'nombre_usuario' => 'Carlos',
                'apellido1_usuario' => 'Rodríguez',
                'apellido2_usuario' => 'Mora',
            ],
            [
                'identificacion_usuario' => 100002,
                'correo' => 'veterinario@bovweight.com',
                'clave' => Hash::make('password123'),
                'id_rol' => 2,
                'estado' => true,
                'nombre_usuario' => 'Ana',
                'apellido1_usuario' => 'González',
                'apellido2_usuario' => 'Pérez',
            ],
            [
                'identificacion_usuario' => 100003,
                'correo' => 'ayudante@bovweight.com',
                'clave' => Hash::make('password123'),
                'id_rol' => 3,
                'estado' => true,
                'nombre_usuario' => 'Luis',
                'apellido1_usuario' => 'Jiménez',
                'apellido2_usuario' => 'Castro',
            ],
            [
                'identificacion_usuario' => 100004,
                'correo' => 'tecnico@bovweight.com',
                'clave' => Hash::make('password123'),
                'id_rol' => 4,
                'estado' => true,
                'nombre_usuario' => 'María',
                'apellido1_usuario' => 'Vargas',
                'apellido2_usuario' => 'Solís',
            ],
        ]);
    }
}