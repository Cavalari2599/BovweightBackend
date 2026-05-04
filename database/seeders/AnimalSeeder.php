<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('animales')->insert([
            [
                'n_arete' => 'ARETE-001',
                'nombre_animal' => 'Tormenta',
                'raza' => 'Brahman',
                'edad' => 3,
                'peso' => 320.50,
                'estado' => 'Activo',
                'fecha_nacimiento' => '2022-03-15',
                'foto_animal' => null,
                'id_finca' => 1,
            ],
            [
                'n_arete' => 'ARETE-002',
                'nombre_animal' => 'Canela',
                'raza' => 'Charolais',
                'edad' => 2,
                'peso' => 280.00,
                'estado' => 'Activo',
                'fecha_nacimiento' => '2023-06-20',
                'foto_animal' => null,
                'id_finca' => 1,
            ],
        ]);
    }
}