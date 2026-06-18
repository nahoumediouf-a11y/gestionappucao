<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropositionProjet extends Model
{
    use HasFactory;

    protected $table = 'propositions_projets';

    protected $fillable = [
        'etudiant_id',
        'titre',
        'description',
        'matiere',
        'statut',
        'commentaire',
        'traite_par',
    ];

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traite_par');
    }

    public function statutLabel(): string
    {
        return match ($this->statut) {
            'accepte' => 'Accepté',
            'refuse' => 'Refusé',
            default => 'En attente',
        };
    }

    public function statutBadge(): string
    {
        return match ($this->statut) {
            'accepte' => 'success',
            'refuse' => 'danger',
            default => 'warning',
        };
    }
}
