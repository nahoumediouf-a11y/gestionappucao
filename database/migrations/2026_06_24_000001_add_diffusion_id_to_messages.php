<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Regroupe les messages d'un même envoi groupé (diffusion) sous un identifiant
     * commun. NULL = message individuel (rétrocompatibilité avec l'existant).
     */
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->uuid('diffusion_id')->nullable()->after('id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropIndex(['diffusion_id']);
            $table->dropColumn('diffusion_id');
        });
    }
};
