<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\DocumentCours;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentCoursController extends Controller
{
    public function index()
    {
        $etudiant = auth()->user()->etudiant;
        $documents = DocumentCours::with('professeur')
            ->when($etudiant, function ($q) use ($etudiant) {
                $q->where(function ($q2) use ($etudiant) {
                    $q2->whereNull('filiere')
                       ->orWhere('filiere', $etudiant->filiere);
                })->where(function ($q2) use ($etudiant) {
                    $q2->whereNull('niveau')
                       ->orWhere('niveau', $etudiant->niveau);
                });
            })
            ->latest()->paginate(20);

        return view('etudiant.documents.index', compact('documents'));
    }

    public function telecharger(DocumentCours $document)
    {
        abort_unless(Storage::disk('public')->exists($document->fichier_path), 404);
        return Storage::disk('public')->download($document->fichier_path, $document->fichier_nom);
    }
}
