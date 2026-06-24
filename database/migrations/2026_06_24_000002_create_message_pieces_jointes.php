<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Pièces jointes d'un message. Rattachées à la DIFFUSION (diffusion_id) : un envoi
     * groupé stocke le fichier UNE seule fois, partagé par tous les destinataires.
     */
    public function up(): void
    {
        Schema::create('message_pieces_jointes', function (Blueprint $table) {
            $table->id();
            $table->uuid('diffusion_id')->index();
            $table->foreignId('expediteur_id')->constrained('users')->cascadeOnDelete();
            $table->string('chemin');        // chemin sur le disque privé "local"
            $table->string('nom');           // nom d'origine affiché/téléchargé
            $table->string('mime')->nullable();
            $table->unsignedBigInteger('taille')->default(0); // octets
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('message_pieces_jointes');
    }
};
