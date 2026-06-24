<?php

use App\Support\Recherche;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute des colonnes normalisées (sans accents, minuscules) indexées pour une
     * recherche d'étudiants/personnel rapide et insensible aux accents, plus des
     * index sur les colonnes filtrées des étudiants.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nom_norm')->nullable()->after('nom')->index();
            $table->string('prenom_norm')->nullable()->after('prenom')->index();
        });

        Schema::table('etudiants', function (Blueprint $table) {
            $table->index('filiere');
            $table->index('niveau');
        });

        // Backfill des données existantes (matricule reste ASCII, pas de normalisation).
        DB::table('users')->select('id', 'nom', 'prenom')->orderBy('id')->chunk(200, function ($lignes) {
            foreach ($lignes as $ligne) {
                DB::table('users')->where('id', $ligne->id)->update([
                    'nom_norm' => Recherche::normaliser($ligne->nom),
                    'prenom_norm' => Recherche::normaliser($ligne->prenom),
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('etudiants', function (Blueprint $table) {
            $table->dropIndex(['filiere']);
            $table->dropIndex(['niveau']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['nom_norm']);
            $table->dropIndex(['prenom_norm']);
            $table->dropColumn(['nom_norm', 'prenom_norm']);
        });
    }
};
