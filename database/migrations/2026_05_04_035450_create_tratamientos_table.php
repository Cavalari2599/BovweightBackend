<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tratamientos', function (Blueprint $table) {
            $table->increments('id_tratamiento');
            $table->string('tipo_tratamiento', 100);
            $table->string('medicamento', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();
            $table->string('n_arete', 50);
           $table->unsignedInteger('identificacion_usuario');

            $table->foreign('n_arete')->references('n_arete')->on('animales');
            $table->foreign('identificacion_usuario')->references('identificacion_usuario')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tratamientos');
    }
};