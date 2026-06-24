<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'diffusion_id',
        'expediteur_id',
        'destinataire_id',
        'sujet',
        'corps',
        'lu_a',
    ];

    protected function casts(): array
    {
        return [
            'lu_a' => 'datetime',
        ];
    }

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    public function destinataire(): BelongsTo
    {
        return $this->belongsTo(User::class, 'destinataire_id');
    }

    /**
     * Pièces jointes de la diffusion à laquelle appartient ce message (stockées une
     * seule fois pour tout l'envoi, reliées par diffusion_id).
     */
    public function piecesJointes(): HasMany
    {
        return $this->hasMany(MessagePieceJointe::class, 'diffusion_id', 'diffusion_id');
    }

    public function estLu(): bool
    {
        return $this->lu_a !== null;
    }

    /** Messages non lus reçus par un utilisateur. */
    public function scopeNonLusPour(Builder $query, int $userId): Builder
    {
        return $query->where('destinataire_id', $userId)->whereNull('lu_a');
    }
}
