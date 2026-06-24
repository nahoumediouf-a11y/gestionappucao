<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessagePieceJointe extends Model
{
    use HasFactory;

    protected $table = 'message_pieces_jointes';

    protected $fillable = [
        'diffusion_id',
        'expediteur_id',
        'chemin',
        'nom',
        'mime',
        'taille',
    ];

    public function expediteur(): BelongsTo
    {
        return $this->belongsTo(User::class, 'expediteur_id');
    }

    /** Vrai si la pièce jointe est une image (pour l'aperçu). */
    public function estImage(): bool
    {
        return str_starts_with((string) $this->mime, 'image/');
    }

    /** Taille lisible (Ko / Mo). */
    public function tailleLisible(): string
    {
        $o = (int) $this->taille;
        if ($o >= 1_048_576) {
            return round($o / 1_048_576, 1).' Mo';
        }

        return max(1, (int) round($o / 1024)).' Ko';
    }

    /** Supprime le fichier du disque (appelé à la suppression du modèle). */
    protected static function booted(): void
    {
        static::deleting(function (MessagePieceJointe $piece) {
            Storage::disk('local')->delete($piece->chemin);
        });
    }
}
