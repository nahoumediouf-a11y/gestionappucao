<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'professeur_id',
        'titre',
        'description',
        'filiere',
        'niveau',
        'matiere',
        'chemin',
        'nom_original',
        'taille',
    ];

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function tailleLisible(): string
    {
        $taille = $this->taille;

        if ($taille < 1024) {
            return $taille.' o';
        }

        if ($taille < 1024 * 1024) {
            return round($taille / 1024, 1).' Ko';
        }

        return round($taille / (1024 * 1024), 1).' Mo';
    }
}
