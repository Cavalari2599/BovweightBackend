<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            // 'M' = macho, 'F' = hembra. Usado por el modelo de estimacion de peso (CNN).
            $table->char('sexo', 1)->nullable()->after('raza');
        });
    }

    public function down(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->dropColumn('sexo');
        });
    }
};
