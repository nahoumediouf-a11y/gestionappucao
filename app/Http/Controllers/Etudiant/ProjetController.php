<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Projet;
use App\Models\Soumission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProjetController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        $projets = Projet::with('professeur')
            ->where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderBy('date_limite')
            ->get();

        // Soumissions de l'étudiant indexées par projet pour l'affichage du statut.
        $soumissions = $etudiant->soumissions()
            ->whereIn('projet_id', $projets->pluck('id'))
            ->get()
            ->keyBy('projet_id');

        return view('etudiant.projets.index', [
            'projets' => $projets,
            'soumissions' => $soumissions,
        ]);
    }

    public function show(Projet $projet): View
    {
        $etudiant = $this->etudiantPourProjet($projet);

        return view('etudiant.projets.show', [
            'projet' => $projet,
            'soumission' => $projet->soumissionDe($etudiant),
        ]);
    }

    public function soumettre(Request $request, Projet $projet): RedirectResponse
    {
        $etudiant = $this->etudiantPourProjet($projet);

        if (! $projet->accepteRendu()) {
            return back()->with('error', 'Le dépôt n\'est pas ouvert pour ce travail.');
        }

        $existante = $projet->soumissionDe($etudiant);

        if ($existante && $projet->copie_unique) {
            return back()->with('error', 'Ce travail n\'autorise qu\'une seule remise : vous avez déjà rendu votre copie.');
        }

        $validated = $request->validate([
            'texte' => ['nullable', 'string', 'max:5000'],
            'fichier' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,zip,jpg,jpeg,png'],
        ]);

        if (empty($validated['texte']) && ! $request->hasFile('fichier') && ! $existante) {
            return back()->with('error', 'Veuillez fournir un texte ou un fichier.');
        }

        $data = [
            'texte' => $validated['texte'] ?? null,
            'rendu_a' => now(),
            'en_retard' => now()->gt($projet->echeance()),
        ];

        if ($request->hasFile('fichier')) {
            // Remplace l'ancien fichier le cas échéant.
            if ($existante?->fichier_path) {
                Storage::disk('local')->delete($existante->fichier_path);
            }
            $fichier = $request->file('fichier');
            $data['fichier_path'] = $fichier->store('soumissions', 'local');
            $data['fichier_nom'] = $fichier->getClientOriginalName();
        }

        Soumission::updateOrCreate(
            ['projet_id' => $projet->id, 'etudiant_id' => $etudiant->id],
            $data
        );

        return redirect()->route('etudiant.projets.show', $projet)
            ->with('success', 'Votre travail a bien été remis.');
    }

    public function telecharger(Projet $projet): StreamedResponse
    {
        $etudiant = $this->etudiantPourProjet($projet);
        $soumission = $projet->soumissionDe($etudiant);

        abort_if(! $soumission || ! $soumission->fichier_path, Response::HTTP_NOT_FOUND);

        return Storage::disk('local')->download($soumission->fichier_path, $soumission->fichier_nom);
    }

    /** Récupère l'étudiant connecté en s'assurant que le projet concerne sa classe. */
    private function etudiantPourProjet(Projet $projet)
    {
        $etudiant = auth()->user()->etudiant;

        abort_unless(
            $projet->filiere === $etudiant->filiere && $projet->niveau === $etudiant->niveau,
            Response::HTTP_FORBIDDEN
        );

        return $etudiant;
    }
}
