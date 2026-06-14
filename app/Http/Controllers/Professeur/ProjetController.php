<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Projet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ProjetController extends Controller
{
    use InteractsWithEtudiants;

    public function index(): View
    {
        $projets = Projet::where('professeur_id', auth()->id())
            ->orderByDesc('date_limite')
            ->get();

        return view('professeur.projets.index', [
            'projets' => $projets,
        ]);
    }

    public function create(): View
    {
        return view('professeur.projets.create', [
            'creneaux' => $this->creneauxDuProfesseur(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'matiere' => ['required', 'string', 'max:255'],
            'date_limite' => ['required', 'date'],
        ]);

        Projet::create([
            ...$validated,
            'professeur_id' => auth()->id(),
        ]);

        return redirect()->route('professeur.projets.index')->with('success', 'Projet assigné avec succès.');
    }

    public function edit(Projet $projet): View
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        return view('professeur.projets.edit', [
            'projet' => $projet,
            'creneaux' => $this->creneauxDuProfesseur(),
        ]);
    }

    public function update(Request $request, Projet $projet): RedirectResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'matiere' => ['required', 'string', 'max:255'],
            'date_limite' => ['required', 'date'],
        ]);

        $projet->update($validated);

        return redirect()->route('professeur.projets.index')->with('success', 'Projet modifié avec succès.');
    }

    public function destroy(Projet $projet): RedirectResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $projet->delete();

        return redirect()->route('professeur.projets.index')->with('success', 'Projet supprimé avec succès.');
    }
}
