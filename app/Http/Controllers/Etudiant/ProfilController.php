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
            'telephone' => ['nullable', 'string', 'max:30'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'contact_urgence_nom' => ['required', 'string', 'max:255'],
            'contact_urgence_telephone' => ['required', 'string', 'max:30'],
        ]);

        auth()->user()->update(['telephone' => $validated['telephone'] ?? null]);

        auth()->user()->etudiant->update([
            'adresse' => $validated['adresse'] ?? null,
            'contact_urgence_nom' => $validated['contact_urgence_nom'],
            'contact_urgence_telephone' => $validated['contact_urgence_telephone'],
        ]);

        return back()->with('success', 'Coordonnées et contact d\'urgence mis à jour.');
    }
}
