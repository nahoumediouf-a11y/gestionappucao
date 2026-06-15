<?php

namespace App\Http\Controllers\Etudiant;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfilController extends Controller
{
    public function index(): View
    {
        $etudiant = auth()->user()->etudiant;

        return view('etudiant.profil.index', [
            'etudiant' => $etudiant,
            'moyenne' => $etudiant->moyenne(),
        ]);
    }

    public function updateContactUrgence(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'contact_urgence_nom' => ['required', 'string', 'max:255'],
            'contact_urgence_telephone' => ['required', 'string', 'max:30'],
        ]);

        auth()->user()->etudiant->update($validated);

        return back()->with('success', 'Contact d\'urgence (parent/tuteur) mis à jour.');
    }
}
