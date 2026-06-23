<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Models\CoursEnLigne;
use App\Models\EmploiDuTemps;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CoursEnLigneController extends Controller
{
    public function index(): View
    {
        $cours = CoursEnLigne::with('emploiDuTemps')
            ->where('professeur_id', auth()->id())
            ->orderByRaw("CASE statut WHEN 'en_cours' THEN 0 WHEN 'planifie' THEN 1 ELSE 2 END")
            ->orderBy('debut_prevu')
            ->get();

        return view('professeur.cours.index', ['cours' => $cours]);
    }

    public function create(): View
    {
        return view('professeur.cours.create', [
            'creneaux' => $this->creneauxDuProfesseur(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCours($request);

        $validated['professeur_id'] = auth()->id();
        $validated['room_name'] = CoursEnLigne::genererRoomName($validated['titre']);
        $validated['statut'] = 'planifie';

        $cours = CoursEnLigne::create($validated);

        ActivityLogger::log('cours_en_ligne.create', "Planification d'un cours en ligne : {$cours->titre} ({$cours->filiere} {$cours->niveau}).");

        return redirect()->route('professeur.cours.index')->with('success', 'Cours en ligne planifié avec succès.');
    }

    public function edit(CoursEnLigne $cours): View
    {
        $this->autoriser($cours);

        return view('professeur.cours.edit', [
            'cours' => $cours,
            'creneaux' => $this->creneauxDuProfesseur(),
        ]);
    }

    public function update(Request $request, CoursEnLigne $cours): RedirectResponse
    {
        $this->autoriser($cours);

        $cours->update($this->validateCours($request));

        ActivityLogger::log('cours_en_ligne.update', "Modification du cours en ligne : {$cours->titre}.");

        return redirect()->route('professeur.cours.index')->with('success', 'Cours en ligne modifié avec succès.');
    }

    public function destroy(CoursEnLigne $cours): RedirectResponse
    {
        $this->autoriser($cours);

        $cours->delete();

        ActivityLogger::log('cours_en_ligne.delete', 'Suppression d\'un cours en ligne.');

        return redirect()->route('professeur.cours.index')->with('success', 'Cours en ligne supprimé.');
    }

    public function demarrer(CoursEnLigne $cours): RedirectResponse
    {
        $this->autoriser($cours);

        if ($cours->statut === 'planifie') {
            $cours->update(['statut' => 'en_cours', 'demarre_a' => now()]);
            ActivityLogger::log('cours_en_ligne.demarrer', "Démarrage du cours en ligne : {$cours->titre}.");
        }

        return redirect()->route('professeur.cours.salle', $cours);
    }

    public function terminer(CoursEnLigne $cours): RedirectResponse
    {
        $this->autoriser($cours);

        if ($cours->statut === 'en_cours') {
            $cours->update(['statut' => 'termine', 'termine_a' => now()]);
            ActivityLogger::log('cours_en_ligne.terminer', "Fin du cours en ligne : {$cours->titre}.");
        }

        return redirect()->route('professeur.cours.index')->with('success', 'Cours en ligne terminé.');
    }

    public function salle(CoursEnLigne $cours): View
    {
        $this->autoriser($cours);

        return view('cours.salle', [
            'cours' => $cours,
            'estModerateur' => true,
            'retourUrl' => route('professeur.cours.index'),
        ]);
    }

    /** Le professeur ne peut agir que sur ses propres séances. */
    private function autoriser(CoursEnLigne $cours): void
    {
        abort_unless($cours->professeur_id === auth()->id(), 403);
    }

    private function creneauxDuProfesseur()
    {
        return EmploiDuTemps::where('professeur_id', auth()->id())
            ->orderBy('jour')
            ->orderBy('heure_debut')
            ->get();
    }

    private function validateCours(Request $request): array
    {
        return $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'emploi_du_temps_id' => ['nullable', 'exists:emplois_du_temps,id'],
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'debut_prevu' => ['required', 'date'],
            'fin_prevue' => ['nullable', 'date', 'after:debut_prevu'],
        ]);
    }
}
