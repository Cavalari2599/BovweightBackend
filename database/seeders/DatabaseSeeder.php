<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolSeeder::class,
            UserSeeder::class,
            FincaSeeder::class,
            AyudanteSeeder::class,
            AtiendeSeeder::class,
            AnimalSeeder::class,
            PesajeSeeder::class,
            TratamientoSeeder::class,
            HistorialAccionesSeeder::class,
        ]);
    }
}