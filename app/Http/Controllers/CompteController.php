<?php

namespace App\Http\Controllers;

use App\Support\PhotoUtilisateur;
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
            ...PhotoUtilisateur::regles(),
        ], PhotoUtilisateur::messages());

        $user->update([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'] ?? null,
            'telephone' => $validated['telephone'] ?? null,
        ]);

        PhotoUtilisateur::appliquer($user, $request);

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }
}
