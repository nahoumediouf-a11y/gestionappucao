<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ponderation extends Model
{
    use HasFactory;

    protected $table = 'ponderations';

    /** Poids par défaut (%) si aucune pondération n'est définie pour la matière. */
    public const DEFAUTS = ['examen' => 70, 'tp' => 30, 'td' => 0, 'cc' => 0];

    protected $fillable = [
        'professeur_id',
        'filiere',
        'niveau',
        'matiere',
        'poids_examen',
        'poids_tp',
        'poids_td',
        'poids_cc',
    ];

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    /**
     * Pondération en vigueur pour une matière d'une classe : l'enregistrement
     * existant, sinon un objet (non persisté) avec les poids par défaut.
     */
    public static function pour(string $filiere, string $niveau, string $matiere): self
    {
        return static::query()
            ->where('filiere', $filiere)
            ->where('niveau', $niveau)
            ->where('matiere', $matiere)
            ->first()
            ?? new self([
                'filiere' => $filiere,
                'niveau' => $niveau,
                'matiere' => $matiere,
                'poids_examen' => self::DEFAUTS['examen'],
                'poids_tp' => self::DEFAUTS['tp'],
                'poids_td' => self::DEFAUTS['td'],
                'poids_cc' => self::DEFAUTS['cc'],
            ]);
    }

    /** @return array<string, int> poids par catégorie. */
    public function poids(): array
    {
        return [
            'examen' => (int) $this->poids_examen,
            'tp' => (int) $this->poids_tp,
            'td' => (int) $this->poids_td,
            'cc' => (int) $this->poids_cc,
        ];
    }

    /** La somme des poids vaut-elle 100 ? */
    public function valide(): bool
    {
        return array_sum($this->poids()) === 100;
    }
}
