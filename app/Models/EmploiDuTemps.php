<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmploiDuTemps extends Model
{
    use HasFactory;

    protected $table = 'emplois_du_temps';

    /** Types de séance et couleur Bootstrap associée pour l'affichage. */
    public const TYPES = [
        'CM' => ['label' => 'Cours magistral', 'couleur' => 'primary'],
        'TD' => ['label' => 'Travaux dirigés', 'couleur' => 'success'],
        'TP' => ['label' => 'Travaux pratiques', 'couleur' => 'warning'],
        'Examen' => ['label' => 'Examen', 'couleur' => 'danger'],
    ];

    /** Jours ouvrés gérés par l'emploi du temps, dans l'ordre d'affichage. */
    public const JOURS = ['Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];

    protected $fillable = [
        'filiere',
        'niveau',
        'jour',
        'heure_debut',
        'heure_fin',
        'matiere',
        'type',
        'salle',
        'professeur_id',
    ];

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    /** Libellé complet du type de séance (ex. "Travaux dirigés"). */
    public function typeLabel(): string
    {
        return self::TYPES[$this->type]['label'] ?? $this->type;
    }

    /** Couleur Bootstrap associée au type (ex. "success"). */
    public function typeCouleur(): string
    {
        return self::TYPES[$this->type]['couleur'] ?? 'secondary';
    }

    /**
     * Détecte les conflits d'un créneau (prof, salle ou classe déjà occupé(e)
     * sur le même jour avec un chevauchement horaire). Deux créneaux se
     * chevauchent si debut1 < fin2 ET debut2 < fin1.
     *
     * @return array<int, string> Liste de messages de conflit (vide si aucun).
     */
    public static function detecterConflits(array $data, ?int $ignorerId = null): array
    {
        $base = static::query()
            ->where('jour', $data['jour'])
            ->where('heure_debut', '<', $data['heure_fin'])
            ->where('heure_fin', '>', $data['heure_debut'])
            ->when($ignorerId, fn ($q) => $q->where('id', '!=', $ignorerId));

        $conflits = [];

        // Salle déjà occupée sur ce créneau
        $salle = (clone $base)->where('salle', $data['salle'])->with('professeur')->first();
        if ($salle) {
            $conflits[] = sprintf(
                'La salle %s est déjà occupée %s de %s à %s (%s — %s %s).',
                $data['salle'], $data['jour'],
                substr((string) $salle->heure_debut, 0, 5), substr((string) $salle->heure_fin, 0, 5),
                $salle->matiere, $salle->filiere, $salle->niveau
            );
        }

        // Professeur déjà en cours sur ce créneau
        if (! empty($data['professeur_id'])) {
            $prof = (clone $base)->where('professeur_id', $data['professeur_id'])->first();
            if ($prof) {
                $conflits[] = sprintf(
                    'Le professeur a déjà un cours %s de %s à %s (%s — %s %s).',
                    $data['jour'],
                    substr((string) $prof->heure_debut, 0, 5), substr((string) $prof->heure_fin, 0, 5),
                    $prof->matiere, $prof->filiere, $prof->niveau
                );
            }
        }

        // Classe (filière + niveau) ayant déjà cours sur ce créneau
        $classe = (clone $base)
            ->where('filiere', $data['filiere'])
            ->where('niveau', $data['niveau'])
            ->first();
        if ($classe) {
            $conflits[] = sprintf(
                'La classe %s %s a déjà cours %s de %s à %s (%s).',
                $data['filiere'], $data['niveau'], $data['jour'],
                substr((string) $classe->heure_debut, 0, 5), substr((string) $classe->heure_fin, 0, 5),
                $classe->matiere
            );
        }

        return $conflits;
    }
}
