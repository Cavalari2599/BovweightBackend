<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AyudanteSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('ayudantes')->insert([
            [
                'identificacion_usuario' => 100003,
                'id_finca' => 1,
            ],
        ]);
    }
}