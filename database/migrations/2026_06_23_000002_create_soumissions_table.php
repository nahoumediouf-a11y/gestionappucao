<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('soumissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('projet_id')->constrained('projets')->cascadeOnDelete();
            $table->foreignId('etudiant_id')->constrained('etudiants')->cascadeOnDelete();
            $table->text('texte')->nullable();
            $table->string('fichier_path')->nullable();
            $table->string('fichier_nom')->nullable();
            $table->dateTime('rendu_a');
            $table->boolean('en_retard')->default(false);
            $table->decimal('note', 5, 2)->nullable();
            $table->text('commentaire_correction')->nullable();
            $table->dateTime('corrige_a')->nullable();
            $table->foreignId('corrige_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['projet_id', 'etudiant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('soumissions');
    }
};
