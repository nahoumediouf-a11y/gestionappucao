<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Etudiant;
use App\Models\Note;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CarnetController extends Controller
{
    use InteractsWithEtudiants;

    public function index(Request $request): View
    {
        [$filiere, $niveau, $matieres, $matiere] = $this->contexte($request);

        $etudiants = Etudiant::with('user')
            ->where('filiere', $filiere)->where('niveau', $niveau)
            ->orderBy('matricule')->get();

        [$sessions, $notes] = $this->grilleNotes($etudiants->pluck('id'), $matiere, $request->string('nouvelle_session')->toString());

        return view('professeur.carnet.index', [
            'filiere' => $filiere,
            'niveau' => $niveau,
            'matieres' => $matieres,
            'matiere' => $matiere,
            'etudiants' => $etudiants,
            'sessions' => $sessions,
            'notes' => $notes,
        ]);
    }

    public function storeNote(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'filiere' => ['required', 'string'],
            'niveau' => ['required', 'string'],
            'etudiant_id' => ['required', 'integer'],
            'matiere' => ['required', 'string', 'max:255'],
            'session' => ['required', 'string', 'max:255'],
            'valeur' => ['nullable', 'numeric', 'min:0', 'max:20'],
        ]);

        abort_unless($this->enseigneClasse($validated['filiere'], $validated['niveau']), Response::HTTP_FORBIDDEN);

        // L'étudiant doit appartenir à la classe enseignée.
        $appartient = Etudiant::where('id', $validated['etudiant_id'])
            ->where('filiere', $validated['filiere'])->where('niveau', $validated['niveau'])->exists();
        abort_unless($appartient, Response::HTTP_FORBIDDEN);

        if ($validated['valeur'] === null || $validated['valeur'] === '') {
            // Cellule vidée : on supprime la note correspondante si elle existe.
            Note::where('etudiant_id', $validated['etudiant_id'])
                ->where('matiere', $validated['matiere'])
                ->where('session', $validated['session'])
                ->delete();
        } else {
            Note::updateOrCreate(
                [
                    'etudiant_id' => $validated['etudiant_id'],
                    'matiere' => $validated['matiere'],
                    'session' => $validated['session'],
                ],
                ['professeur_id' => auth()->id(), 'valeur' => $validated['valeur']]
            );
            ActivityLogger::log('carnet.note', "Saisie d'une note ({$validated['matiere']} — {$validated['session']}).");
        }

        return redirect()->route('professeur.carnet.index', [
            'filiere' => $validated['filiere'],
            'niveau' => $validated['niveau'],
            'matiere' => $validated['matiere'],
            'session' => $validated['session'],
        ])->with('success', 'Note enregistrée.');
    }

    public function export(Request $request): StreamedResponse
    {
        [$filiere, $niveau, , $matiere] = $this->contexte($request);

        $etudiants = Etudiant::with('user')
            ->where('filiere', $filiere)->where('niveau', $niveau)
            ->orderBy('matricule')->get();

        [$sessions, $notes] = $this->grilleNotes($etudiants->pluck('id'), $matiere, null);
        $nom = 'carnet-'.Str::slug($filiere.'-'.$niveau.'-'.$matiere).'.csv';

        return response()->streamDownload(function () use ($etudiants, $sessions, $notes) {
            $out = fopen('php://output', 'w');
            fputcsv($out, array_merge(['Matricule', 'Étudiant'], $sessions, ['Moyenne']));
            foreach ($etudiants as $e) {
                $valeurs = array_map(fn ($s) => $notes[$e->id][$s] ?? '', $sessions);
                $existantes = array_filter($valeurs, fn ($v) => $v !== '');
                $moyenne = $existantes ? round(array_sum($existantes) / count($existantes), 2) : '';
                fputcsv($out, array_merge([$e->matricule, $e->user?->nom_complet], $valeurs, [$moyenne]));
            }
            fclose($out);
        }, $nom, ['Content-Type' => 'text/csv']);
    }

    /** Résout et sécurise le contexte filière/niveau/matière depuis la requête. */
    private function contexte(Request $request): array
    {
        $filiere = (string) $request->query('filiere');
        $niveau = (string) $request->query('niveau');

        abort_unless($this->enseigneClasse($filiere, $niveau), Response::HTTP_FORBIDDEN);

        $matieres = $this->creneauxDuProfesseur()
            ->where('filiere', $filiere)->where('niveau', $niveau)
            ->pluck('matiere')->unique()->values();

        $matiere = (string) ($request->query('matiere') ?: $matieres->first());

        return [$filiere, $niveau, $matieres, $matiere];
    }

    /**
     * Construit la liste des sessions (colonnes) et la grille [etudiant_id][session] => note.
     *
     * @return array{0: array<int, string>, 1: array<int, array<string, float>>}
     */
    private function grilleNotes($etudiantIds, string $matiere, ?string $nouvelleSession): array
    {
        $lignes = Note::whereIn('etudiant_id', $etudiantIds)
            ->where('matiere', $matiere)
            ->get(['etudiant_id', 'session', 'valeur']);

        $sessions = $lignes->pluck('session')->unique()->sort()->values()->all();

        if ($nouvelleSession && ! in_array($nouvelleSession, $sessions, true)) {
            $sessions[] = $nouvelleSession;
        }

        $notes = [];
        foreach ($lignes as $l) {
            $notes[$l->etudiant_id][$l->session] = (float) $l->valeur;
        }

        return [$sessions, $notes];
    }
}
