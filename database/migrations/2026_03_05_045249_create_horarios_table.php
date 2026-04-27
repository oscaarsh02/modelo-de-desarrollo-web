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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->string('nrc')->unique(); // Identificador único de la clase
            $table->foreignId('grupo_id')->constrained()->cascadeOnDelete();
            $table->foreignId('profesor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('salon_id')->constrained()->cascadeOnDelete();
            $table->string('dia'); // L, A, M, J, V
            $table->string('hora'); // 1000-1059
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
