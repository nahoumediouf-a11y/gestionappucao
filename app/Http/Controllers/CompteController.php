<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CompteController extends Controller
{
    /** Page « Mon compte » unifiée, accessible à tous les rôles. */
    public function show(): View
    {
        $user = auth()->user();
        $etudiant = $user->etudiant;

        if ($etudiant) {
            // Solde affiché toujours recalculé (source de vérité).
            $etudiant->solde = $etudiant->soldeReel();
        }

        return view('compte.index', [
            'user' => $user,
            'etudiant' => $etudiant,
            'moyenne' => $etudiant?->moyenne(),
        ]);
    }

    /** Mise à jour de ses propres informations personnelles (tous rôles). */
    public function update(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'telephone' => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($validated);

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }
}
