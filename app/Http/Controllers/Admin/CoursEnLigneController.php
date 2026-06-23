<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CoursEnLigne;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CoursEnLigneController extends Controller
{
    public function index(): View
    {
        $cours = CoursEnLigne::with('professeur')
            ->orderByRaw("CASE statut WHEN 'en_cours' THEN 0 WHEN 'planifie' THEN 1 ELSE 2 END")
            ->orderBy('debut_prevu', 'desc')
            ->paginate(20);

        return view('admin.cours-en-ligne.index', ['cours' => $cours]);
    }

    public function annuler(CoursEnLigne $cours): RedirectResponse
    {
        if (in_array($cours->statut, ['planifie', 'en_cours'], true)) {
            $cours->update(['statut' => 'annule']);
            ActivityLogger::log('cours_en_ligne.annuler', "Annulation du cours en ligne : {$cours->titre}.");
        }

        return redirect()->route('admin.cours-en-ligne.index')->with('success', 'Cours en ligne annulé.');
    }
}
