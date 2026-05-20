<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->unsignedInteger('identificacion_usuario')->primary();
            $table->string('correo', 255)->unique();
            $table->string('clave', 255);
            $table->unsignedInteger('id_rol');
            $table->boolean('estado')->default(true);
            $table->string('nombre_usuario', 100);
            $table->string('apellido1_usuario', 100);
            $table->string('apellido2_usuario', 100)->nullable();

            $table->foreign('id_rol')->references('id_rol')->on('roles');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};