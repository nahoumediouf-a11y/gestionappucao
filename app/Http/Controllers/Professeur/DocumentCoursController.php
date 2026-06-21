<?php

namespace App\Http\Controllers\Professeur;

use App\Http\Controllers\Controller;
use App\Models\DocumentCours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentCoursController extends Controller
{
    public function index()
    {
        $documents = DocumentCours::where('professeur_id', auth()->id())
            ->latest()->paginate(15);
        return view('professeur.documents_cours.index', compact('documents'));
    }

    public function create()
    {
        return view('professeur.documents_cours.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'matiere'     => 'required|string|max:100',
            'filiere'     => 'nullable|string|max:100',
            'niveau'      => 'nullable|string|max:10',
            'fichier'     => 'required|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,zip,txt|max:20480',
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents_cours', 'public');

        DocumentCours::create([
            'professeur_id' => auth()->id(),
            'titre'         => $request->titre,
            'description'   => $request->description,
            'matiere'       => $request->matiere,
            'filiere'       => $request->filiere,
            'niveau'        => $request->niveau,
            'fichier_path'  => $path,
            'fichier_nom'   => $file->getClientOriginalName(),
            'type_fichier'  => $file->getClientOriginalExtension(),
            'taille'        => $file->getSize(),
        ]);

        return redirect()->route('professeur.documents_cours.index')
            ->with('success', 'Document publié avec succès.');
    }

    public function destroy(DocumentCours $document)
    {
        abort_unless($document->professeur_id === auth()->id(), 403);
        Storage::disk('public')->delete($document->fichier_path);
        $document->delete();
        return back()->with('success', 'Document supprimé.');
    }
}
