<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class ForgotPasswordController extends Controller
{
    public function show(): View
    {
        return view('auth.forgot-password');
    }

    public function send(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
        ], [
            'login.required' => 'Veuillez indiquer votre login.',
        ]);

        $user = User::where('login', $data['login'])->first();

        if (! $user || ! $user->email) {
            return back()
                ->withInput($data)
                ->withErrors(['login' => 'Aucune adresse email n\'est associée à ce compte. Contactez l\'administration.']);
        }

        Password::sendResetLink(['email' => $user->email]);

        return back()->with('success', 'Si un compte existe pour ce login, un lien de réinitialisation a été envoyé à l\'adresse email associée.');
    }

    public function showReset(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Za-z])(?=.*\d).+$/', 'confirmed'],
        ], [
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une lettre et un chiffre.',
        ]);

        $status = Password::reset(
            $data,
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Ce lien de réinitialisation est invalide ou a expiré.']);
        }

        return redirect()
            ->route('login')
            ->with('success', 'Votre mot de passe a été réinitialisé. Vous pouvez maintenant vous connecter.');
    }
}
