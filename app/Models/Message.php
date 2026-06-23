<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
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
