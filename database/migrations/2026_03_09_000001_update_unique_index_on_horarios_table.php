<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropUnique(['nrc']);
            $table->unique(['nrc', 'dia', 'hora'], 'horarios_nrc_dia_hora_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('horarios', function (Blueprint $table) {
            $table->dropUnique('horarios_nrc_dia_hora_unique');
            $table->unique('nrc');
        });
    }
};

