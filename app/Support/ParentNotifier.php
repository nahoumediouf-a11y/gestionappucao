<?php

namespace App\Support;

use App\Mail\AlerteParentMail;
use App\Models\Etudiant;
use App\Models\Note;
use Illuminate\Support\Facades\Mail;

class ParentNotifier
{
    /** Informe le parent/tuteur que l'étudiant a atteint le seuil d'absences non justifiées. */
    public static function absencesRepetees(Etudiant $etudiant): void
    {
        if (! $etudiant->email_parent) {
            return;
        }

        $etudiant->loadMissing('user');

        Mail::to($etudiant->email_parent)->send(new AlerteParentMail(
            etudiant: $etudiant,
            titre: 'Absences répétées',
            contenu: sprintf(
                "Nous vous informons que %s a accumulé %d absences non justifiées et se trouve désormais en situation rouge (accès aux examens bloqué tant que la situation n'est pas régularisée). Nous vous invitons à échanger avec votre enfant et, si besoin, à contacter l'administration de l'UCAO.",
                $etudiant->user->nom_complet,
                $etudiant->absencesNonJustifieesCount(),
            ),
        ));
    }

    /** Informe le parent/tuteur d'une baisse de note dans une matière. */
    public static function baisseNote(Etudiant $etudiant, Note $note, float $ancienneValeur): void
    {
        if (! $etudiant->email_parent) {
            return;
        }

        $etudiant->loadMissing('user');

        Mail::to($etudiant->email_parent)->send(new AlerteParentMail(
            etudiant: $etudiant,
            titre: 'Baisse de note — '.$note->matiere,
            contenu: sprintf(
                "Nous vous informons que la note de %s en %s (session %s) est en baisse : %s/20, contre %s/20 précédemment. Nous vous encourageons à échanger avec votre enfant sur ses méthodes de travail dans cette matière.",
                $etudiant->user->nom_complet,
                $note->matiere,
                $note->session,
                number_format((float) $note->valeur, 2),
                number_format($ancienneValeur, 2),
            ),
        ));
    }
}
