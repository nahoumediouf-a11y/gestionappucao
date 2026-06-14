<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etudiant extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'matricule',
        'niveau',
        'filiere',
        'solde',
    ];

    protected function casts(): array
    {
        return [
            'solde' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function paiements(): HasMany
    {
        return $this->hasMany(Paiement::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function absences(): HasMany
    {
        return $this->hasMany(Absence::class);
    }

    public function engagements(): HasMany
    {
        return $this->hasMany(EngagementPaiement::class);
    }

    public function emploiDuTemps()
    {
        return EmploiDuTemps::where('filiere', $this->filiere)
            ->where('niveau', $this->niveau)
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();
    }

    public function moyenne(): float
    {
        return round((float) $this->notes()->avg('valeur'), 2);
    }
}
