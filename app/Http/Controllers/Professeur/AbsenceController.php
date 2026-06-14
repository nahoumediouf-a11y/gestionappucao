<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Absence;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class AbsenceController extends Controller
{
    use InteractsWithEtudiants;

    public function index(): View
    {
        $absences = Absence::with('etudiant.user')
            ->where('professeur_id', auth()->id())
            ->orderByDesc('date')
            ->get();

        return view('professeur.absences.index', [
            'absences' => $absences,
        ]);
    }

    public function create(): View
    {
        return view('professeur.absences.create', [
            'etudiants' => $this->etudiantsDuProfesseur(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $etudiantIds = $this->etudiantsDuProfesseur()->pluck('id');

        $validated = $request->validate([
            'etudiant_id' => ['required', Rule::in($etudiantIds)],
            'matiere' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'justifiee' => ['sometimes', 'boolean'],
        ]);

        Absence::create([
            ...$validated,
            'justifiee' => $request->boolean('justifiee'),
            'professeur_id' => auth()->id(),
        ]);

        return redirect()->route('professeur.absences.index')->with('success', 'Absence enregistrée avec succès.');
    }

    public function edit(Absence $absence): View
    {
        abort_unless($absence->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $absence->load('etudiant.user');

        return view('professeur.absences.edit', [
            'absence' => $absence,
        ]);
    }

    public function update(Request $request, Absence $absence): RedirectResponse
    {
        abort_unless($absence->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'matiere' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'justifiee' => ['sometimes', 'boolean'],
        ]);

        $absence->update([
            ...$validated,
            'justifiee' => $request->boolean('justifiee'),
        ]);

        return redirect()->route('professeur.absences.index')->with('success', 'Absence modifiée avec succès.');
    }
}
