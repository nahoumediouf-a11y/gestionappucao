<?php

namespace App\Http\Controllers\Auth;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\User;
use App\Support\Captcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function showRegisterForm(): View
    {
        return view('auth.register', ['captcha' => Captcha::generate()]);
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nom' => ['required', 'string', 'max:100'],
            'prenom' => ['required', 'string', 'max:100'],
            'login' => ['required', 'string', 'max:50', 'regex:/^[a-zA-Z0-9._-]+$/', 'unique:users,login'],
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
            'niveau' => ['required', 'string', 'in:L1,L2,L3,M1,M2'],
            'filiere' => ['required', 'string', 'max:100'],
            'captcha' => ['required'],
        ], [
            'login.unique' => 'Ce login est déjà utilisé.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une lettre et un chiffre.',
            'captcha.required' => 'Veuillez répondre à la question de sécurité.',
        ]);

        if (! Captcha::verify($data['captcha'])) {
            return back()
                ->withInput($request->except('password', 'password_confirmation', 'captcha'))
                ->withErrors(['captcha' => 'Réponse incorrecte à la question de sécurité.']);
        }

        DB::transaction(function () use ($data) {
            $user = User::create([
                'nom' => $data['nom'],
                'prenom' => $data['prenom'],
                'login' => $data['login'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($data['password']),
                'role' => Role::Etudiant,
                'statut' => 'en_attente',
            ]);

            Etudiant::create([
                'user_id' => $user->id,
                'matricule' => (string) (1010000 + $user->id),
                'niveau' => $data['niveau'],
                'filiere' => $data['filiere'],
                'solde' => 0,
            ]);

            return $user;
        });

        return redirect()
            ->route('login')
            ->with('success', "Votre demande d'inscription a été enregistrée. Votre compte est actuellement en attente de validation par l'administration. Veuillez finaliser votre inscription pédagogique auprès de l'établissement.");
    }
}
