<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PesajeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pesajes')->insert([
            // Pesajes de ARETE-001
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-08-01', 'peso_aproximado' => 290.00, 'n_arete' => 'ARETE-001'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-09-01', 'peso_aproximado' => 300.50, 'n_arete' => 'ARETE-001'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-10-01', 'peso_aproximado' => 308.00, 'n_arete' => 'ARETE-001'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-11-01', 'peso_aproximado' => 315.75, 'n_arete' => 'ARETE-001'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-12-01', 'peso_aproximado' => 320.50, 'n_arete' => 'ARETE-001'],
            // Pesajes de ARETE-002
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-08-01', 'peso_aproximado' => 250.00, 'n_arete' => 'ARETE-002'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-09-01', 'peso_aproximado' => 258.50, 'n_arete' => 'ARETE-002'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-10-01', 'peso_aproximado' => 265.00, 'n_arete' => 'ARETE-002'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-11-01', 'peso_aproximado' => 272.25, 'n_arete' => 'ARETE-002'],
            ['foto_pesaje' => null, 'fecha_pesaje' => '2024-12-01', 'peso_aproximado' => 280.00, 'n_arete' => 'ARETE-002'],
        ]);
    }
}