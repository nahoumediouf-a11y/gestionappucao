<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EngagementPaiement extends Model
{
    use HasFactory;

    protected $table = 'engagements_paiement';

    protected $fillable = [
        'etudiant_id',
        'agent_id',
        'date',
        'montant',
        'echeance',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'echeance' => 'date',
            'montant' => 'decimal:2',
        ];
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
