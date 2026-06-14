<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploiDuTemps extends Model
{
    use HasFactory;

    protected $table = 'emplois_du_temps';

    protected $fillable = [
        'filiere',
        'niveau',
        'jour',
        'heure_debut',
        'heure_fin',
        'matiere',
        'salle',
        'professeur_id',
    ];

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }
}
