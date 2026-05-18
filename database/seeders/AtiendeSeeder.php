<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AtiendeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('atiende')->insert([
            [
                'identificacion_usuario' => 100002,
                'id_finca' => 1,
            ],
        ]);
    }
}