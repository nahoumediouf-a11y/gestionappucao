<?php

namespace App\Http\Controllers\Professeur;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Absence;
use App\Models\Etudiant;
use App\Models\User;
use App\Notifications\SituationRougeNotification;
use App\Support\ParentNotifier;
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
            'heure' => ['nullable', 'date_format:H:i'],
            'justifiee' => ['sometimes', 'boolean'],
        ]);

        $absence = Absence::create([
            ...$validated,
            'justifiee' => $request->boolean('justifiee'),
            'professeur_id' => auth()->id(),
        ]);

        $this->notifierSiSituationRouge($absence);

        return redirect()->route('professeur.absences.index')->with('success', 'Absence enregistrée avec succès.');
    }

    /** Notifie les administrateurs lorsque l'étudiant atteint le seuil de situation rouge. */
    private function notifierSiSituationRouge(Absence $absence): void
    {
        if ($absence->justifiee) {
            return;
        }

        $etudiant = Etudiant::find($absence->etudiant_id);

        if (! $etudiant || ! $etudiant->enSituationRouge()) {
            return;
        }

        $admins = User::where('role', Role::Administrateur)->get();

        foreach ($admins as $admin) {
            $admin->notify(new SituationRougeNotification($etudiant, $absence));
        }

        ParentNotifier::absencesRepetees($etudiant);
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
            'heure' => ['nullable', 'date_format:H:i'],
            'justifiee' => ['sometimes', 'boolean'],
        ]);

        $absence->update([
            ...$validated,
            'justifiee' => $request->boolean('justifiee'),
        ]);

        return redirect()->route('professeur.absences.index')->with('success', 'Absence modifiée avec succès.');
    }
}
