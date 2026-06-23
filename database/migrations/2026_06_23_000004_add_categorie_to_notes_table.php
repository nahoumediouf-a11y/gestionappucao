<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            // Catégorie d'évaluation : examen, tp (travaux pratiques), td, cc (contrôle continu).
            // Défaut « examen » : les notes existantes restent prises en compte avec
            // la pondération par défaut (Examen 70 % / TP 30 %, re-normalisée).
            $table->string('categorie')->default('examen')->after('matiere');
        });
    }

    public function down(): void
    {
        Schema::table('notes', function (Blueprint $table) {
            $table->dropColumn('categorie');
        });
    }
};
