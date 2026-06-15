<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('etudiants', function (Blueprint $table) {
            $table->string('contact_urgence_nom')->nullable()->after('solde');
            $table->string('contact_urgence_telephone')->nullable()->after('contact_urgence_nom');
        });
    }

    public function down(): void
    {
        Schema::table('etudiants', function (Blueprint $table) {
            $table->dropColumn(['contact_urgence_nom', 'contact_urgence_telephone']);
        });
    }
};
