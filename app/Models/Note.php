<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_id',
        'professeur_id',
        'matiere',
        'valeur',
        'session',
    ];

    protected function casts(): array
    {
        return [
            'valeur' => 'decimal:2',
        ];
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }
}
