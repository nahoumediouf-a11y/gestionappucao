<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use App\Models\PropositionProjet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropositionProjetController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        $propositions = PropositionProjet::where('etudiant_id', $etudiant->id)
            ->orderByDesc('created_at')
            ->get();

        return view('etudiant.propositions.index', [
            'propositions' => $propositions,
        ]);
    }

    public function create(): View
    {
        return view('etudiant.propositions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'titre' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:2000'],
            'matiere' => ['nullable', 'string', 'max:255'],
        ]);

        $etudiant = auth()->user()->etudiant;

        PropositionProjet::create([
            ...$validated,
            'etudiant_id' => $etudiant->id,
        ]);

        return redirect()
            ->route('etudiant.propositions.index')
            ->with('success', 'Votre proposition de projet a été soumise avec succès. Elle sera examinée par l\'équipe pédagogique.');
    }
}
