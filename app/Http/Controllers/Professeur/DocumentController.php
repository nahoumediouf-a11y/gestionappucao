<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Professeur\Concerns\InteractsWithEtudiants;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    use InteractsWithEtudiants;

    public function index(): View
    {
        $documents = Document::where('professeur_id', auth()->id())
            ->orderByDesc('created_at')
            ->get();

        return view('professeur.documents.index', [
            'documents' => $documents,
        ]);
    }

    public function create(): View
    {
        return view('professeur.documents.create', [
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
            'fichier' => ['required', 'file', 'mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,png,jpg,jpeg', 'max:10240'],
        ]);

        $fichier = $request->file('fichier');
        $chemin = $fichier->store('documents-cours');

        Document::create([
            'professeur_id' => auth()->id(),
            'titre' => $validated['titre'],
            'description' => $validated['description'] ?? null,
            'filiere' => $validated['filiere'],
            'niveau' => $validated['niveau'],
            'matiere' => $validated['matiere'],
            'chemin' => $chemin,
            'nom_original' => $fichier->getClientOriginalName(),
            'taille' => $fichier->getSize(),
        ]);

        return redirect()->route('professeur.documents.index')->with('success', 'Document mis en ligne avec succès.');
    }

    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($document->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        return Storage::download($document->chemin, $document->nom_original);
    }

    public function destroy(Document $document): RedirectResponse
    {
        abort_unless($document->professeur_id === auth()->id(), Response::HTTP_FORBIDDEN);

        Storage::delete($document->chemin);
        $document->delete();

        return redirect()->route('professeur.documents.index')->with('success', 'Document supprimé avec succès.');
    }
}
