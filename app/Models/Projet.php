<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Projet extends Model
{
    use HasFactory;

    protected $fillable = [
        'professeur_id',
        'titre',
        'description',
        'filiere',
        'niveau',
        'matiere',
        'date_limite',
    ];

    protected function casts(): array
    {
        return [
            'date_limite' => 'date',
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
}
