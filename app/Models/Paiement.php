<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paiement extends Model
{
    use HasFactory;

    public const STATUTS = [
        'en_attente_validation' => ['label' => 'En attente de validation', 'color' => 'warning'],
        'valide'                => ['label' => 'Validé',                   'color' => 'success'],
        'annule'                => ['label' => 'Annulé',                   'color' => 'secondary'],
    ];

    public const MODES = [
        'especes'      => 'Espèces',
        'wave'         => 'Wave (via PayDunya)',
        'orange_money' => 'Orange Money (via PayDunya)',
        'free_money'   => 'Free Money (via PayDunya)',
        'paydunya'     => 'PayDunya (confirmé)',
        'visa'         => 'Carte Visa / Bancaire',
        'virement'     => 'Virement bancaire',
        'cheque'       => 'Chèque',
        'mobile_money' => 'Mobile Money (autre)',
    ];

    public function modeLabel(): string
    {
        return self::MODES[$this->mode_paiement] ?? ucfirst(str_replace('_', ' ', $this->mode_paiement));
    }

    protected $fillable = [
        'etudiant_id',
        'agent_id',
        'date_paiement',
        'montant',
        'mode_paiement',
        'reference',
        'numero_mobile',
        'note_etudiant',
        'statut',
        'valide_par',
        'valide_le',
    ];

    protected function casts(): array
    {
        return [
            'date_paiement' => 'date',
            'montant' => 'decimal:2',
            'valide_le' => 'datetime',
        ];
    }

    public function etudiant(): BelongsTo
    {
        return $this->belongsTo(Etudiant::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function validePar(): BelongsTo
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
