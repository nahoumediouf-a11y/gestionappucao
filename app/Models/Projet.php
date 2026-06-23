<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class Projet extends Model
{
    use HasFactory;

    public const TYPES = [
        'projet' => 'Projet',
        'devoir' => 'Devoir',
        'examen' => 'Examen',
    ];

    protected $fillable = [
        'professeur_id',
        'type',
        'titre',
        'description',
        'filiere',
        'niveau',
        'matiere',
        'bareme',
        'rendu_en_ligne',
        'date_limite',
        'ouverture_at',
        'fermeture_at',
        'copie_unique',
        'rappel_envoye',
    ];

    protected function casts(): array
    {
        return [
            'date_limite' => 'date',
            'ouverture_at' => 'datetime',
            'fermeture_at' => 'datetime',
            'rappel_envoye' => 'boolean',
            'rendu_en_ligne' => 'boolean',
            'copie_unique' => 'boolean',
        ];
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function soumissions(): HasMany
    {
        return $this->hasMany(Soumission::class);
    }

    public function statut(): string
    {
        return $this->date_limite->isPast() ? 'Terminé' : 'En cours';
    }

    public function typeLabel(): string
    {
        return self::TYPES[$this->type] ?? 'Projet';
    }

    /** Échéance « souple » : au-delà, un rendu est accepté mais marqué en retard. */
    public function echeance(): Carbon
    {
        return $this->date_limite->copy()->endOfDay();
    }

    /**
     * Le travail accepte-t-il un dépôt maintenant ? Faux si le rendu en ligne est
     * désactivé, avant l'ouverture, ou après la fermeture « dure » (fermeture_at).
     * Les rendus après l'échéance souple restent acceptés (mais marqués en retard).
     */
    public function accepteRendu(): bool
    {
        if (! $this->rendu_en_ligne) {
            return false;
        }

        if ($this->ouverture_at && now()->lt($this->ouverture_at)) {
            return false;
        }

        if ($this->fermeture_at && now()->gt($this->fermeture_at)) {
            return false;
        }

        return true;
    }

    /** Soumission d'un étudiant donné, si elle existe. */
    public function soumissionDe(Etudiant $etudiant): ?Soumission
    {
        return $this->soumissions()->where('etudiant_id', $etudiant->id)->first();
    }
}
