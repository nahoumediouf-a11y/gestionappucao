<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\User;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('etudiant')->orderBy('nom')->get();

        return view('admin.utilisateurs.index', [
            'users' => $users,
            'roles' => Role::cases(),
        ]);
    }

    public function create(): View
    {
        return view('admin.utilisateurs.create', [
            'roles' => Role::cases(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateUser($request);

        $user = User::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => $validated['role'],
            'statut' => $validated['statut'],
        ]);

        if ($validated['role'] === Role::Etudiant->value) {
            Etudiant::create([
                'user_id' => $user->id,
                'matricule' => $validated['matricule'],
                'niveau' => $validated['niveau'],
                'filiere' => $validated['filiere'],
                'solde' => $validated['solde'] ?? 0,
            ]);
        }

        ActivityLogger::log('user.create', 'Création de l\'utilisateur '.$user->login.' (rôle : '.$user->role->label().')');

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur créé avec succès.');
    }

    public function edit(User $utilisateur): View
    {
        $utilisateur->load('etudiant');

        return view('admin.utilisateurs.edit', [
            'user' => $utilisateur,
            'roles' => Role::cases(),
        ]);
    }

    public function update(Request $request, User $utilisateur): RedirectResponse
    {
        $validated = $this->validateUser($request, $utilisateur);

        $data = [
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'login' => $validated['login'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'statut' => $validated['statut'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = $validated['password'];
        }

        $utilisateur->update($data);

        if ($validated['role'] === Role::Etudiant->value) {
            Etudiant::updateOrCreate(
                ['user_id' => $utilisateur->id],
                [
                    'matricule' => $validated['matricule'],
                    'niveau' => $validated['niveau'],
                    'filiere' => $validated['filiere'],
                    'solde' => $validated['solde'] ?? 0,
                ]
            );
        }

        ActivityLogger::log('user.update', 'Modification de l\'utilisateur '.$utilisateur->login);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy(User $utilisateur): RedirectResponse
    {
        if ($utilisateur->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $login = $utilisateur->login;
        $utilisateur->delete();

        ActivityLogger::log('user.delete', 'Suppression de l\'utilisateur '.$login);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur supprimé avec succès.');
    }

    private function validateUser(Request $request, ?User $user = null): array
    {
        $userId = $user?->id;

        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'login' => ['required', 'string', 'max:255', 'unique:users,login,'.$userId],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email,'.$userId],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(Role::cases(), 'value'))],
            'statut' => ['required', 'string', 'in:actif,inactif'],
            'matricule' => ['required_if:role,etudiant', 'nullable', 'string', 'regex:/^\d{7}$/', 'unique:etudiants,matricule,'.($user?->etudiant?->id)],
            'niveau' => ['required_if:role,etudiant', 'nullable', 'string', 'max:50'],
            'filiere' => ['required_if:role,etudiant', 'nullable', 'string', 'max:255'],
            'solde' => ['nullable', 'numeric', 'min:0'],
        ];

        $rules['password'] = $user
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        return $request->validate($rules, [
            'matricule.regex' => 'Le matricule doit être composé de 7 chiffres (ex : 1067604).',
        ]);
    }
}
