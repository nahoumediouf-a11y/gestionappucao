<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cours_en_ligne', function (Blueprint $table) {
            $table->id();
            $table->foreignId('emploi_du_temps_id')->nullable()->constrained('emplois_du_temps')->nullOnDelete();
            $table->foreignId('professeur_id')->constrained('users')->cascadeOnDelete();
            $table->string('titre');
            $table->text('description')->nullable();
            $table->string('filiere');
            $table->string('niveau');
            $table->string('room_name')->unique();
            $table->dateTime('debut_prevu');
            $table->dateTime('fin_prevue')->nullable();
            $table->string('statut')->default('planifie');
            $table->dateTime('demarre_a')->nullable();
            $table->dateTime('termine_a')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cours_en_ligne');
    }
};
