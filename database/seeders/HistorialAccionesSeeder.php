<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistorialAccionesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('historial_acciones')->insert([
            [
                'identificacion_usuario' => 100001,
                'accion' => 'Registro de animal',
                'tabla_afectada' => 'animales',
                'id_registro' => 'ARETE-001',
                'fecha_accion' => '2024-08-01 08:00:00',
            ],
        ]);
    }
}