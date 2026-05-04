<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FincaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('fincas')->insert([
            [
                'nombre_finca' => 'Finca La Esperanza',
                'ubicacion_finca' => 'Liberia, Guanacaste',
                'identificacion_usuario' => 100001,
            ],
        ]);
    }
}