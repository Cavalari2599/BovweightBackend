<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pesajes', function (Blueprint $table) {
            $table->increments('id_pesaje');
            $table->string('foto_pesaje', 255)->nullable();
            $table->date('fecha_pesaje');
            $table->decimal('peso_aproximado', 8, 2);
            $table->string('n_arete', 50);

            $table->foreign('n_arete')->references('n_arete')->on('animales');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pesajes');
    }
};