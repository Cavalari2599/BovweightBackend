<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TratamientoSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('tratamientos')->insert([
            [
                'tipo_tratamiento' => 'Vacunación',
                'medicamento' => 'Ivermectina',
                'descripcion' => 'Aplicación de antiparasitario preventivo',
                'fecha_inicio' => '2024-09-01',
                'fecha_fin' => '2024-09-01',
                'n_arete' => 'ARETE-001',
                'identificacion_usuario' => 100002,
            ],
        ]);
    }
}