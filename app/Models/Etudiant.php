<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Etudiant extends Model
{
    use HasFactory;

    /** Nombre d'absences non justifiées à partir duquel l'étudiant est en situation rouge (accès examen bloqué). */
    public const SEUIL_ABSENCES_SITUATION_ROUGE = 3;

    protected $fillable = [
        'user_id',
        'matricule',
        'niveau',
        'filiere',
        'solde',
        'contact_urgence_nom',
        'contact_urgence_telephone',
        'email_parent',
        'adresse',
        'date_naissance',
        'lieu_naissance',
    ];

    protected function casts(): array
    {
        return [
            'solde' => 'decimal:2',
            'date_naissance' => 'date',
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

    public function absencesNonJustifieesCount(): int
    {
        return $this->absences()->where('justifiee', false)->count();
    }

    /** L'étudiant est en situation rouge (accès examen bloqué) au-delà du seuil d'absences non justifiées. */
    public function enSituationRouge(): bool
    {
        return $this->absencesNonJustifieesCount() >= self::SEUIL_ABSENCES_SITUATION_ROUGE;
    }

    /** L'étudiant est en règle avec le service de recouvrement (aucun solde restant à payer). */
    public function estEnRegleAvecRecouvrement(): bool
    {
        return (float) $this->solde <= 0;
    }
}
