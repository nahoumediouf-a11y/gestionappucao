<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('expediteur_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('destinataire_id')->constrained('users')->cascadeOnDelete();
            $table->string('sujet');
            $table->text('corps');
            $table->dateTime('lu_a')->nullable();
            $table->timestamps();

            $table->index(['destinataire_id', 'lu_a']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};
