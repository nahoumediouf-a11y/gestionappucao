<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Etudiant;
use App\Models\User;
use App\Notifications\CompteActiveNotification;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $users = User::with('etudiant')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = $request->string('q');
                $query->where(function ($q) use ($term) {
                    $q->where('nom', 'like', "%{$term}%")
                        ->orWhere('prenom', 'like', "%{$term}%")
                        ->orWhere('login', 'like', "%{$term}%")
                        ->orWhere('email', 'like', "%{$term}%")
                        ->orWhereHas('etudiant', fn ($e) => $e->where('matricule', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('statut'), fn ($query) => $query->where('statut', $request->string('statut')))
            ->orderBy('nom')
            ->paginate(15)
            ->withQueryString();

        return view('admin.utilisateurs.index', [
            'users' => $users,
            'roles' => Role::cases(),
            'q' => $request->string('q'),
            'statut' => $request->string('statut'),
            'enAttenteCount' => User::where('statut', 'en_attente')->count(),
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
            'telephone' => $validated['telephone'] ?? null,
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
                'adresse' => $validated['adresse'] ?? null,
                'date_naissance' => $validated['date_naissance'] ?? null,
                'lieu_naissance' => $validated['lieu_naissance'] ?? null,
                'contact_urgence_nom' => $validated['contact_urgence_nom'] ?? null,
                'contact_urgence_telephone' => $validated['contact_urgence_telephone'] ?? null,
                'email_parent' => $validated['email_parent'] ?? null,
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
            'telephone' => $validated['telephone'] ?? null,
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
                    'adresse' => $validated['adresse'] ?? null,
                    'contact_urgence_nom' => $validated['contact_urgence_nom'] ?? null,
                    'contact_urgence_telephone' => $validated['contact_urgence_telephone'] ?? null,
                    'email_parent' => $validated['email_parent'] ?? null,
                ]
            );
        }

        ActivityLogger::log('user.update', 'Modification de l\'utilisateur '.$utilisateur->login);

        return redirect()->route('admin.utilisateurs.index')->with('success', 'Utilisateur modifié avec succès.');
    }

    public function activer(User $utilisateur): RedirectResponse
    {
        $utilisateur->update(['statut' => 'actif']);

        $utilisateur->notify(new CompteActiveNotification());

        ActivityLogger::log('user.activate', "Activation du compte de {$utilisateur->login} après vérification de l'inscription.");

        return back()->with('success', "Le compte de {$utilisateur->nom_complet} a été activé. Une notification lui a été envoyée.");
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
            'telephone' => ['nullable', 'string', 'max:30'],
            'role' => ['required', 'string', 'in:'.implode(',', array_column(Role::cases(), 'value'))],
            'statut' => ['required', 'string', 'in:actif,inactif,en_attente'],
            'matricule' => ['required_if:role,etudiant', 'nullable', 'digits:7', 'integer', 'between:1000678,1080987', 'unique:etudiants,matricule,'.($user?->etudiant?->id)],
            'niveau' => ['required_if:role,etudiant', 'nullable', 'string', 'max:50'],
            'filiere' => ['required_if:role,etudiant', 'nullable', 'string', 'max:255'],
            'solde' => ['nullable', 'numeric', 'min:0'],
            'adresse' => ['nullable', 'string', 'max:255'],
            'date_naissance' => ['nullable', 'date'],
            'lieu_naissance' => ['nullable', 'string', 'max:255'],
            'contact_urgence_nom' => ['nullable', 'string', 'max:255'],
            'contact_urgence_telephone' => ['nullable', 'string', 'max:30'],
            'email_parent' => ['nullable', 'email', 'max:255'],
        ];

        $rules['password'] = $user
            ? ['nullable', 'string', 'min:6']
            : ['required', 'string', 'min:6'];

        return $request->validate($rules, [
            'matricule.digits' => 'Le matricule doit être composé de 7 chiffres (ex : 1000678).',
            'matricule.integer' => 'Le matricule doit être composé de 7 chiffres (ex : 1000678).',
            'matricule.between' => 'Le matricule doit être compris entre 1000678 et 1080987.',
        ]);
    }
}
