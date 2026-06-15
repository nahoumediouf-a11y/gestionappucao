<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Note;
use App\Support\ParentNotifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class NoteController extends Controller
{
    use InteractsWithEtudiants;

    public function index(): View
    {
        $notes = Note::with('etudiant.user')
            ->where('professeur_id', auth()->id())
            ->orderByDesc('id')
            ->get();

        return view('professeur.notes.index', [
            'notes' => $notes,
        ]);
    }

    public function create(): View
    {
        return view('professeur.notes.create', [
            'etudiants' => $this->etudiantsDuProfesseur(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $etudiantIds = $this->etudiantsDuProfesseur()->pluck('id');

        $validated = $request->validate([
            'etudiant_id' => ['required', Rule::in($etudiantIds)],
            'matiere' => ['required', 'string', 'max:255'],
            'session' => ['required', 'string', 'max:255'],
            'valeur' => ['required', 'numeric', 'min:0', 'max:20'],
        ]);

        $ancienneNote = Note::where('etudiant_id', $validated['etudiant_id'])
            ->where('matiere', $validated['matiere'])
            ->orderByDesc('id')
            ->first();

        $note = Note::create([
            ...$validated,
            'professeur_id' => auth()->id(),
        ]);

        if ($ancienneNote && (float) $validated['valeur'] < (float) $ancienneNote->valeur) {
            ParentNotifier::baisseNote($note->etudiant, $note, (float) $ancienneNote->valeur);
        }

        return redirect()->route('professeur.notes.index')->with('success', 'Note enregistrée avec succès.');
    }

    public function edit(Note $note): View
    {
        abort_unless($note->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $note->load('etudiant.user');

        return view('professeur.notes.edit', [
            'note' => $note,
        ]);
    }

    public function update(Request $request, Note $note): RedirectResponse
    {
        abort_unless($note->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $validated = $request->validate([
            'matiere' => ['required', 'string', 'max:255'],
            'session' => ['required', 'string', 'max:255'],
            'valeur' => ['required', 'numeric', 'min:0', 'max:20'],
        ]);

        $ancienneValeur = (float) $note->valeur;

        $note->update($validated);

        if ((float) $validated['valeur'] < $ancienneValeur) {
            ParentNotifier::baisseNote($note->etudiant, $note, $ancienneValeur);
        }

        return redirect()->route('professeur.notes.index')->with('success', 'Note modifiée avec succès.');
    }
}
