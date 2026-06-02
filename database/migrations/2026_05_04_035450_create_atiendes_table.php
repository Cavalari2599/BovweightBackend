<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('atiende', function (Blueprint $table) {
            $table->unsignedInteger('identificacion_usuario');
            $table->unsignedInteger('id_finca');

            $table->primary(['identificacion_usuario', 'id_finca']);
            $table->foreign('identificacion_usuario')->references('identificacion_usuario')->on('users');
            $table->foreign('id_finca')->references('id_finca')->on('fincas');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('atiende');
    }
};