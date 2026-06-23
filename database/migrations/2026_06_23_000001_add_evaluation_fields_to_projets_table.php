<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            // Note maximale (barème) sur laquelle le travail est évalué.
            $table->unsignedSmallInteger('bareme')->default(20)->after('matiere');
            // Le travail accepte-t-il un rendu en ligne par les étudiants ?
            $table->boolean('rendu_en_ligne')->default(true)->after('bareme');
            // Fenêtre de composition (surtout pour les examens). Null = libre jusqu'à l'échéance.
            $table->dateTime('ouverture_at')->nullable()->after('date_limite');
            $table->dateTime('fermeture_at')->nullable()->after('ouverture_at');
            // Examen « copie unique » : pas de re-soumission une fois rendu.
            $table->boolean('copie_unique')->default(false)->after('fermeture_at');
        });
    }

    public function down(): void
    {
        Schema::table('projets', function (Blueprint $table) {
            $table->dropColumn(['bareme', 'rendu_en_ligne', 'ouverture_at', 'fermeture_at', 'copie_unique']);
        });
    }
};
