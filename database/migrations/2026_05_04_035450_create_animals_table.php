<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('animales', function (Blueprint $table) {
            $table->string('n_arete', 50)->primary();
            $table->string('nombre_animal', 100)->nullable();
            $table->string('raza', 100)->nullable();
            $table->integer('edad')->nullable();
            $table->decimal('peso', 8, 2)->nullable();
            $table->string('estado', 50);
            $table->date('fecha_nacimiento')->nullable();
            $table->string('foto_animal', 255)->nullable();
            $table->unsignedInteger('id_finca');

            $table->foreign('id_finca')->references('id_finca')->on('fincas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('animales');
    }
};