<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\ActivityLogger;
use App\Support\Captcha;
use App\Support\Espaces;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function showWelcome(): View
    {
        return view('auth.welcome', ['familles' => Espaces::parFamille()]);
    }

    public function showLoginForm(Request $request): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $espace = Espaces::get($request->query('espace'));

        return view('auth.login', ['espace' => $espace, 'captcha' => Captcha::generate()]);
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'captcha' => ['required'],
        ], [
            'login.required' => 'Le login est obligatoire.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'captcha.required' => 'Veuillez répondre à la question de sécurité.',
        ]);

        if (! Captcha::verify($credentials['captcha'])) {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['captcha' => 'Réponse incorrecte à la question de sécurité.']);
        }

        $remember = $request->boolean('remember');

        if (
            Auth::attempt(
                ['login' => $credentials['login'], 'password' => $credentials['password'], 'statut' => 'actif'],
                $remember
            )
        ) {
            $request->session()->regenerate();

            ActivityLogger::log('login', 'Connexion de '.Auth::user()->login);

            return redirect()
                ->intended(route('dashboard'))
                ->with('success', 'Connexion réussie. Bienvenue, '.Auth::user()->nom_complet);
        }

        ActivityLogger::log('login_failed', 'Tentative de connexion échouée pour le login "'.$credentials['login'].'"');

        $utilisateur = User::where('login', $credentials['login'])->first();

        if (
            $utilisateur
            && $utilisateur->statut === 'en_attente'
            && Hash::check($credentials['password'], $utilisateur->password)
        ) {
            return back()
                ->withInput($request->only('login', 'remember'))
                ->withErrors(['login' => "Votre compte est en attente de validation par l'administration. Vous recevrez un e-mail dès qu'il sera activé."]);
        }

        return back()
            ->withInput($request->only('login', 'remember'))
            ->withErrors(['login' => 'Login ou mot de passe incorrect, ou compte inactif.']);
    }

    public function logout(Request $request): RedirectResponse
    {
        ActivityLogger::log('logout', 'Déconnexion de '.Auth::user()->login);

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('login')
            ->with('success', 'Vous êtes déconnecté.');
    }
}
