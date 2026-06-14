<?php

namespace App\Models;

use App\Enums\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'email',
        'password',
        'role',
        'statut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function getNomCompletAttribute(): string
    {
        return trim("{$this->prenom} {$this->nom}");
    }

    public function isActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function hasPermission(string $permission): bool
    {
        return in_array($permission, $this->role->permissions(), true);
    }

    public function etudiant(): HasOne
    {
        return $this->hasOne(Etudiant::class);
    }

    public function paiementsEnregistres(): HasMany
    {
        return $this->hasMany(Paiement::class, 'agent_id');
    }

    public function engagementsGeres(): HasMany
    {
        return $this->hasMany(EngagementPaiement::class, 'agent_id');
    }

    public function notesSaisies(): HasMany
    {
        return $this->hasMany(Note::class, 'professeur_id');
    }

    public function absencesEnregistrees(): HasMany
    {
        return $this->hasMany(Absence::class, 'professeur_id');
    }

    public function creneaux(): HasMany
    {
        return $this->hasMany(EmploiDuTemps::class, 'professeur_id');
    }
}
