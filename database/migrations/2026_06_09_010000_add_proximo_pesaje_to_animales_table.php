<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            // Recordatorio de pesaje: cuando toca volver a pesar este animal.
            $table->date('proximo_pesaje')->nullable()->after('peso');
            // Recurrencia: null = no repite; 7/15/30... dias para reprogramar al pesar.
            $table->unsignedSmallInteger('repetir_cada_dias')->nullable()->after('proximo_pesaje');
        });
    }

    public function down(): void
    {
        Schema::table('animales', function (Blueprint $table) {
            $table->dropColumn(['proximo_pesaje', 'repetir_cada_dias']);
        });
    }
};
