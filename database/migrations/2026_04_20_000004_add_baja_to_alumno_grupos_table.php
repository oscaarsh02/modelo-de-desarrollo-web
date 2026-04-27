<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('alumno_grupos', function (Blueprint $table) {
            $table->timestamp('baja_at')->nullable()->after('grupo_id');
        });
    }

    public function down(): void
    {
        Schema::table('alumno_grupos', function (Blueprint $table) {
            $table->dropColumn('baja_at');
        });
    }
};
