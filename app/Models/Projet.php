<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Projet extends Model
{
    use HasFactory;

    public const TYPES = [
        'projet' => 'Projet',
        'devoir' => 'Devoir',
        'examen' => 'Examen',
    ];

    protected $fillable = [
        'professeur_id',
        'type',
        'titre',
        'description',
        'filiere',
        'niveau',
        'matiere',
        'date_limite',
        'rappel_envoye',
    ];

    protected function casts(): array
    {
        return [
            'date_limite' => 'date',
            'rappel_envoye' => 'boolean',
        ];
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function statut(): string
    {
        return $this->date_limite->isPast() ? 'Terminé' : 'En cours';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Projet';
    }
}
