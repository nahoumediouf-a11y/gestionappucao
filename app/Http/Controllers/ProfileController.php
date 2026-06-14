<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.password');
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'mot_de_passe_actuel' => ['required', 'string'],
            'mot_de_passe' => ['required', 'string', 'min:6', 'confirmed'],
        ], [], [
            'mot_de_passe_actuel' => 'mot de passe actuel',
            'mot_de_passe' => 'nouveau mot de passe',
        ]);

        $user = $request->user();

        if (! Hash::check($validated['mot_de_passe_actuel'], $user->password)) {
            return back()->withErrors(['mot_de_passe_actuel' => 'Le mot de passe actuel est incorrect.']);
        }

        $user->update(['password' => $validated['mot_de_passe']]);

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }
}
