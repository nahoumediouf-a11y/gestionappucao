<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'nom',
        'prenom',
        'login',
        'email',
        'telephone',
        'photo',
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

    /** URL publique de la photo de profil, ou null (repli sur l'avatar à initiales). */
    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
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

    public function coursEnLigne(): HasMany
    {
        return $this->hasMany(CoursEnLigne::class, 'professeur_id');
    }
}
