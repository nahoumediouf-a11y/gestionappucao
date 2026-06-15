<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        $documents = Document::with('professeur')
            ->where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderByDesc('created_at')
            ->get();

        return view('etudiant.documents.index', [
            'documents' => $documents,
        ]);
    }

    public function download(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $etudiant = auth()->user()->etudiant;

        abort_unless(
            $document->filiere === $etudiant->filiere && $document->niveau === $etudiant->niveau,
            Response::HTTP_FORBIDDEN
        );

        return Storage::download($document->chemin, $document->nom_original);
    }
}
