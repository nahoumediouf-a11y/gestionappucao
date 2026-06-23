<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CoursEnLigne extends Model
{
    use HasFactory;

    protected $table = 'cours_en_ligne';

    /** Minutes avant le début planifié à partir desquelles la salle s'ouvre. */
    public const FENETRE_OUVERTURE_MINUTES = 15;

    /** Statuts possibles d'une séance et couleur Bootstrap associée pour l'affichage. */
    public const STATUTS = [
        'planifie' => ['label' => 'Planifié', 'couleur' => 'secondary'],
        'en_cours' => ['label' => 'En cours', 'couleur' => 'success'],
        'termine' => ['label' => 'Terminé', 'couleur' => 'dark'],
        'annule' => ['label' => 'Annulé', 'couleur' => 'danger'],
    ];

    protected $fillable = [
        'emploi_du_temps_id',
        'professeur_id',
        'titre',
        'description',
        'filiere',
        'niveau',
        'room_name',
        'debut_prevu',
        'fin_prevue',
        'statut',
        'demarre_a',
        'termine_a',
    ];

    protected function casts(): array
    {
        return [
            'debut_prevu' => 'datetime',
            'fin_prevue' => 'datetime',
            'demarre_a' => 'datetime',
            'termine_a' => 'datetime',
        ];
    }

    public function professeur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function emploiDuTemps(): BelongsTo
    {
        return $this->belongsTo(EmploiDuTemps::class, 'emploi_du_temps_id');
    }

    /**
     * Génère un nom de salle Jitsi unique et non devinable à partir du titre.
     * Sur meet.jit.si, quiconque connaît le nom de salle peut entrer : le jeton
     * aléatoire est donc le seul rempart d'accès.
     */
    public static function genererRoomName(string $titre): string
    {
        $base = Str::slug($titre) ?: 'cours';

        return 'ucao-'.$base.'-'.Str::lower(Str::random(12));
    }

    /** URL absolue de la salle de visioconférence. */
    public function lienVisio(): string
    {
        return rtrim((string) config('services.jitsi.base_url'), '/').'/'.$this->room_name;
    }

    /** Libellé du statut (ex. « En cours »). */
    public function statutLabel(): string
    {
        return self::STATUTS[$this->statut]['label'] ?? $this->statut;
    }

    /** Couleur Bootstrap associée au statut (ex. « success »). */
    public function statutCouleur(): string
    {
        return self::STATUTS[$this->statut]['couleur'] ?? 'secondary';
    }

    /** Séances d'une classe (filière + niveau) donnée. */
    public function scopePourClasse(Builder $query, string $filiere, string $niveau): Builder
    {
        return $query->where('filiere', $filiere)->where('niveau', $niveau);
    }

    /** Séances à venir ou en cours (ni terminées ni annulées). */
    public function scopeAVenir(Builder $query): Builder
    {
        return $query->whereIn('statut', ['planifie', 'en_cours']);
    }

    /**
     * La séance peut-elle être rejointe maintenant ? Vrai si elle est en cours,
     * ou planifiée et que l'on est dans la fenêtre d'ouverture (15 min avant le
     * début). Faux si terminée ou annulée.
     */
    public function estRejoignable(): bool
    {
        if (in_array($this->statut, ['termine', 'annule'], true)) {
            return false;
        }

        if ($this->statut === 'en_cours') {
            return true;
        }

        return now()->gte($this->debut_prevu->copy()->subMinutes(self::FENETRE_OUVERTURE_MINUTES));
    }
}
