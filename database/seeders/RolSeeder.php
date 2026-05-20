<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['nombre_rol' => 'Ganadero'],
            ['nombre_rol' => 'Veterinario'],
            ['nombre_rol' => 'Ayudante'],
            ['nombre_rol' => 'Tecnico'],
        ]);
    }
}