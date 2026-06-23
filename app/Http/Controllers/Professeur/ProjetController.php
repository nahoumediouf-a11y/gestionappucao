<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Etudiant;
use App\Models\Note;
use App\Models\Projet;
use App\Models\Soumission;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjetController extends Controller
{
    use InteractsWithEtudiants;

    /** Session sous laquelle les notes issues des évaluations sont publiées au bulletin. */
    private const SESSION_NOTE = 'Contrôle continu';

    public function index(): View
    {
        $projets = Projet::withCount('soumissions')
            ->where('professeur_id', auth()->id())
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
        $validated = $this->validateProjet($request);

        Projet::create([
            ...$validated,
            'professeur_id' => auth()->id(),
        ]);

        return redirect()->route('professeur.projets.index')->with('success', 'Échéance assignée avec succès. Les étudiants concernés recevront un rappel 3 jours avant.');
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

        $validated = $this->validateProjet($request);

        if ($validated['date_limite'] !== $projet->date_limite->toDateString()) {
            $validated['rappel_envoye'] = false;
        }

        $projet->update($validated);

        return redirect()->route('professeur.projets.index')->with('success', 'Projet modifié avec succès.');
    }

    public function destroy(Projet $projet): RedirectResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $projet->delete();

        return redirect()->route('professeur.projets.index')->with('success', 'Projet supprimé avec succès.');
    }

    /** Liste des copies rendues pour un travail, avec statistiques de suivi. */
    public function soumissions(Projet $projet): View
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $soumissions = $projet->soumissions()->with('etudiant.user')->get();

        $attendus = Etudiant::where('filiere', $projet->filiere)
            ->where('niveau', $projet->niveau)
            ->count();

        $notes = $soumissions->whereNotNull('note')->pluck('note')->map(fn ($n) => (float) $n)->sort()->values();

        return view('professeur.projets.soumissions', [
            'projet' => $projet,
            'soumissions' => $soumissions,
            'stats' => [
                'attendus' => $attendus,
                'rendus' => $soumissions->count(),
                'retards' => $soumissions->where('en_retard', true)->count(),
                'corrigees' => $soumissions->filter->estCorrigee()->count(),
                'moyenne' => $notes->isNotEmpty() ? round($notes->avg(), 2) : null,
            ],
        ]);
    }

    /** Enregistre la note + le commentaire d'une copie et publie la note au bulletin. */
    public function corriger(Request $request, Projet $projet, Soumission $soumission): RedirectResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);
        abort_unless($soumission->projet_id === $projet->id, Response::HTTP_NOT_FOUND);

        $validated = $request->validate([
            'note' => ['required', 'numeric', 'min:0', 'max:'.$projet->bareme],
            'commentaire_correction' => ['nullable', 'string', 'max:2000'],
        ]);

        $soumission->update([
            'note' => $validated['note'],
            'commentaire_correction' => $validated['commentaire_correction'] ?? null,
            'corrige_a' => now(),
            'corrige_par' => auth()->id(),
        ]);

        // Publication au bulletin : note ramenée sur 20.
        $valeurSur20 = round((float) $validated['note'] / max(1, $projet->bareme) * 20, 2);

        // Catégorie de la note publiée : un examen alimente « Examen », un projet/devoir « TP ».
        $categorie = $projet->type === 'examen' ? 'examen' : 'tp';

        Note::updateOrCreate(
            [
                'etudiant_id' => $soumission->etudiant_id,
                'matiere' => $projet->matiere,
                'session' => self::SESSION_NOTE,
            ],
            [
                'professeur_id' => auth()->id(),
                'valeur' => $valeurSur20,
                'categorie' => $categorie,
            ]
        );

        ActivityLogger::log('evaluation.corriger', "Correction d'une copie ({$projet->titre}) : {$validated['note']}/{$projet->bareme}.");

        return back()->with('success', 'Copie corrigée et note publiée au bulletin.');
    }

    public function telecharger(Projet $projet, Soumission $soumission): StreamedResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);
        abort_unless($soumission->projet_id === $projet->id, Response::HTTP_NOT_FOUND);
        abort_if(! $soumission->fichier_path, Response::HTTP_NOT_FOUND);

        return Storage::disk('local')->download($soumission->fichier_path, $soumission->fichier_nom);
    }

    /** Export CSV des résultats d'un travail. */
    public function exportCsv(Projet $projet): StreamedResponse
    {
        abort_unless($projet->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        $soumissions = $projet->soumissions()->with('etudiant.user')->get();
        $nom = 'resultats-'.Str::slug($projet->titre).'.csv';

        return response()->streamDownload(function () use ($soumissions, $projet) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Matricule', 'Étudiant', 'Rendu le', 'En retard', "Note (/{$projet->bareme})", 'Corrigée le']);
            foreach ($soumissions as $s) {
                fputcsv($out, [
                    $s->etudiant->matricule,
                    $s->etudiant->user?->nom_complet,
                    $s->rendu_a?->format('d/m/Y H:i'),
                    $s->en_retard ? 'oui' : 'non',
                    $s->note,
                    $s->corrige_a?->format('d/m/Y H:i'),
                ]);
            }
            fclose($out);
        }, $nom, ['Content-Type' => 'text/csv']);
    }

    private function validateProjet(Request $request): array
    {
        $validated = $request->validate([
            'type' => ['required', 'string', 'in:'.implode(',', array_keys(Projet::TYPES))],
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'filiere' => ['required', 'string', 'max:255'],
            'niveau' => ['required', 'string', 'max:50'],
            'matiere' => ['required', 'string', 'max:255'],
            'bareme' => ['nullable', 'integer', 'min:1', 'max:100'],
            'date_limite' => ['required', 'date'],
            'ouverture_at' => ['nullable', 'date'],
            'fermeture_at' => ['nullable', 'date', 'after:ouverture_at'],
        ]);

        // Barème par défaut sur 20 si non précisé.
        $validated['bareme'] = $validated['bareme'] ?? 20;

        // Cases à cocher : absentes du payload si décochées (rendu en ligne activé par défaut).
        $validated['rendu_en_ligne'] = $request->has('rendu_en_ligne') ? $request->boolean('rendu_en_ligne') : true;
        $validated['copie_unique'] = $request->boolean('copie_unique');

        return $validated;
    }
}
