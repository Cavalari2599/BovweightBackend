<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('historial_acciones', function (Blueprint $table) {
            $table->increments('id_historial');
            $table->unsignedInteger('identificacion_usuario');
            $table->string('accion', 100);
            $table->string('tabla_afectada', 100)->nullable();
            $table->string('id_registro', 50)->nullable();
            $table->dateTime('fecha_accion')->useCurrent();

            $table->foreign('identificacion_usuario')->references('identificacion_usuario')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('historial_acciones');
    }
};