<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('emplois_du_temps', function (Blueprint $table) {
            // Type de séance : CM (cours magistral), TD (travaux dirigés),
            // TP (travaux pratiques) ou Examen. Par défaut CM.
            $table->string('type', 10)->default('CM')->after('matiere');
        });
    }

    public function down(): void
    {
        Schema::table('emplois_du_temps', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
