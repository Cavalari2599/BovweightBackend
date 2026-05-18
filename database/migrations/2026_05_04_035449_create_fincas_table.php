<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fincas', function (Blueprint $table) {
            $table->increments('id_finca');
            $table->string('nombre_finca', 150);
            $table->string('ubicacion_finca', 255);
            $table->unsignedInteger('identificacion_usuario');

            $table->foreign('identificacion_usuario')->references('identificacion_usuario')->on('users');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fincas');
    }
};