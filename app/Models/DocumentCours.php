<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentCours extends Model
{
    protected $table = 'documents_cours';

    protected $fillable = [
        'professeur_id', 'titre', 'description', 'matiere',
        'filiere', 'niveau', 'fichier_path', 'fichier_nom',
        'type_fichier', 'taille',
    ];

    public function professeur()
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function getTailleFormateeAttribute(): string
    {
        $bytes = $this->taille ?? 0;
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' Mo';
        if ($bytes >= 1024)    return round($bytes / 1024, 1) . ' Ko';
        return $bytes . ' o';
    }
}
