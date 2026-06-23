<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Soumission extends Model
{
    use HasFactory;

    protected $fillable = [
        'projet_id',
        'etudiant_id',
        'texte',
        'fichier_path',
        'fichier_nom',
        'rendu_a',
        'en_retard',
        'note',
        'commentaire_correction',
        'corrige_a',
        'corrige_par',
    ];

    protected function casts(): array
    {
        return [
            'rendu_a' => 'datetime',
            'corrige_a' => 'datetime',
            'en_retard' => 'boolean',
            'note' => 'decimal:2',
        ];
    }

    public function projet(): BelongsTo
    {
        return $this->belongsTo(Projet::class);
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function correcteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrige_par');
    }

    /** La copie a-t-elle été corrigée (note publiée) ? */
    public function estCorrigee(): bool
    {
        return $this->corrige_a !== null;
    }

    /** Libellé d'état de la copie pour l'affichage. */
    public function statutLabel(): string
    {
        if ($this->estCorrigee()) {
            return 'Corrigée';
        }

        return $this->en_retard ? 'Rendue en retard' : 'Rendue';
    }

    public function statutCouleur(): string
    {
        if ($this->estCorrigee()) {
            return 'primary';
        }

        return $this->en_retard ? 'warning' : 'success';
    }
}
