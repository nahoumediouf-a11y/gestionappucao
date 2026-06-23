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

    /** Filières (licences) proposées par l'établissement. */
    public const FILIERES = [
        'LIG' => 'Licence Informatique de Gestion',
        'LSG' => 'Licence Sciences de Gestion',
    ];

    /** Classes (niveau + groupe) disponibles pour chaque filière. */
    public const NIVEAUX = [
        'L1-1', 'L1-2',
        'L2-1', 'L2-2',
        'L3-1', 'L3-2',
    ];

    /** Frais de scolarité annuels (FCFA) par niveau. */
    public const SCOLARITE_PAR_NIVEAU = [
        'L1' => 650_000,
        'L2' => 700_000,
        'L3' => 780_000,
        'M1' => 850_000,
        'M2' => 900_000,
    ];

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

    public function soumissions(): HasMany
    {
        return $this->hasMany(Soumission::class);
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

    /** Frais de scolarité annuels (FCFA) selon le niveau de l'étudiant. */
    public function scolariteTotale(): int
    {
        $niveauKey = strtoupper($this->niveau ?? '');
        if (str_contains($niveauKey, 'L3')) {
            $niveauKey = 'L3';
        } elseif (str_contains($niveauKey, 'L2')) {
            $niveauKey = 'L2';
        } elseif (str_contains($niveauKey, 'L1')) {
            $niveauKey = 'L1';
        }

        return self::SCOLARITE_PAR_NIVEAU[$niveauKey] ?? 780_000;
    }

    /** Solde restant réel : scolarité totale moins les paiements validés. Source de vérité pour l'affichage. */
    public function soldeReel(): float
    {
        $totalPaye = $this->paiements()->where('statut', 'valide')->sum('montant');

        return max(0, $this->scolariteTotale() - $totalPaye);
    }
}
