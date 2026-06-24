<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\User;
use App\Support\Recherche;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RechercheGlobaleController extends Controller
{
    /** Page de recherche complète (rechargement). */
    public function index(Request $request): View
    {
        $user = $this->personnelAutorise();
        $q = trim((string) $request->query('q'));
        $type = $this->typeDemande($request, $user);

        $etudiants = collect();
        $personnel = collect();

        if ($q !== '') {
            if ($type === 'personnel') {
                $personnel = $this->requetePersonnel($q)->limit(50)->get();
            } else {
                $etudiants = $this->requeteEtudiants($q, $user)->limit(50)->get();
            }
        }

        return view('recherche.index', [
            'q' => $q,
            'type' => $type,
            'etudiants' => $etudiants,
            'personnel' => $personnel,
            'estAdmin' => $user->role === Role::Administrateur,
        ]);
    }

    /** Suggestions JSON pour l'autocomplétion de la barre de recherche (typeahead). */
    public function suggestions(Request $request): JsonResponse
    {
        $user = $this->personnelAutorise();
        $q = trim((string) $request->query('q'));
        $type = $this->typeDemande($request, $user);

        // En dessous du seuil, aucune suggestion (évite des requêtes inutiles).
        if (mb_strlen($q) < Recherche::LONGUEUR_MIN) {
            return response()->json([]);
        }

        if ($type === 'personnel') {
            $items = $this->requetePersonnel($q)
                ->limit(Recherche::LIMITE_SUGGESTIONS)
                ->get()
                ->map(fn (User $u) => [
                    'id' => $u->id,
                    'label' => $u->nom_complet,
                    'sous_titre' => $u->role->label().' · '.$u->login,
                    'matricule' => null,
                    'type' => 'personnel',
                    'url' => route('admin.utilisateurs.index', ['q' => $u->login]),
                ]);
        } else {
            $items = $this->requeteEtudiants($q, $user)
                ->limit(Recherche::LIMITE_SUGGESTIONS)
                ->get()
                ->map(fn (Etudiant $e) => [
                    'id' => $e->user_id,
                    'label' => $e->user?->nom_complet ?? $e->matricule,
                    'sous_titre' => $e->matricule.' · '.$e->filiere.' '.$e->niveau,
                    'matricule' => $e->matricule,
                    'type' => 'etudiant',
                    'url' => self::destinationEtudiant($user->role, $e),
                ]);
        }

        return response()->json($items->values());
    }

    /** Vérifie l'accès (l'étudiant n'a pas de recherche globale) et renvoie l'utilisateur. */
    private function personnelAutorise(): User
    {
        $user = auth()->user();
        abort_if($user->role === Role::Etudiant, Response::HTTP_FORBIDDEN);

        return $user;
    }

    /** Type de recherche demandé ; « personnel » réservé à l'administrateur. */
    private function typeDemande(Request $request, User $user): string
    {
        return $request->query('type') === 'personnel' && $user->role === Role::Administrateur
            ? 'personnel'
            : 'etudiant';
    }

    /**
     * Requête étudiants : recherche multi-mots (matricule, nom, prénom), insensible
     * aux accents et à la casse, résultats par préfixe en premier, restreinte aux
     * classes du professeur le cas échéant.
     */
    private function requeteEtudiants(string $q, User $user): Builder
    {
        $colonnes = ['etudiants.matricule', 'users.nom_norm', 'users.prenom_norm'];

        $query = Etudiant::query()
            ->join('users', 'users.id', '=', 'etudiants.user_id')
            ->select('etudiants.*')
            ->with('user:id,nom,prenom');

        $this->appliquerTokens($query, Recherche::tokens($q), $colonnes);
        $this->ordonnerParPrefixe($query, $q, $colonnes);
        $query->orderBy('users.nom')->orderBy('users.prenom');

        if ($user->role === Role::Professeur) {
            $this->limiterAuxClasses($query, $user->id);
        }

        return $query;
    }

    /** Requête personnel (hors étudiants) : nom, prénom, login, email. */
    private function requetePersonnel(string $q): Builder
    {
        $colonnes = ['users.nom_norm', 'users.prenom_norm', 'users.login', 'users.email'];

        $query = User::query()->where('role', '!=', Role::Etudiant->value);

        $this->appliquerTokens($query, Recherche::tokens($q), $colonnes);
        $this->ordonnerParPrefixe($query, $q, $colonnes);
        $query->orderBy('nom')->orderBy('prenom');

        return $query;
    }

    /**
     * Chaque mot doit correspondre à au moins une colonne (ET entre mots, OU entre
     * colonnes) : permet de trouver « en quelques mots » (ex. « nahoume info »).
     *
     * @param  array<int, string>  $tokens  mots déjà normalisés et échappés
     * @param  array<int, string>  $colonnes
     */
    private function appliquerTokens(Builder $query, array $tokens, array $colonnes): void
    {
        foreach ($tokens as $token) {
            $query->where(function (Builder $sub) use ($token, $colonnes) {
                foreach ($colonnes as $colonne) {
                    $sub->orWhereRaw("{$colonne} LIKE ? ESCAPE '\\'", ["%{$token}%"]);
                }
            });
        }
    }

    /**
     * Classe les correspondances par préfixe (le terme commence la valeur) avant les
     * correspondances par sous-chaîne, sur le premier mot de la requête.
     *
     * @param  array<int, string>  $colonnes
     */
    private function ordonnerParPrefixe(Builder $query, string $q, array $colonnes): void
    {
        $tokens = Recherche::tokens($q);
        if ($tokens === []) {
            return;
        }

        $premier = $tokens[0];
        $conditions = implode(' OR ', array_map(fn ($c) => "{$c} LIKE ? ESCAPE '\\'", $colonnes));
        $bindings = array_fill(0, count($colonnes), "{$premier}%");

        $query->orderByRaw("CASE WHEN {$conditions} THEN 0 ELSE 1 END", $bindings);
    }

    /** Restreint la recherche aux étudiants des classes (filière+niveau) enseignées par le prof. */
    private function limiterAuxClasses(Builder $query, int $profId): Builder
    {
        $classes = EmploiDuTemps::where('professeur_id', $profId)
            ->get(['filiere', 'niveau'])
            ->map(fn ($c) => $c->filiere.'|'.$c->niveau)
            ->unique();

        return $query->where(function (Builder $q) use ($classes) {
            if ($classes->isEmpty()) {
                $q->whereRaw('1 = 0');

                return;
            }
            foreach ($classes as $cle) {
                [$filiere, $niveau] = explode('|', $cle);
                $q->orWhere(fn (Builder $sub) => $sub->where('filiere', $filiere)->where('niveau', $niveau));
            }
        });
    }

    /** Destination utile pour un résultat étudiant selon le rôle connecté. */
    public static function destinationEtudiant(Role $role, Etudiant $etudiant): string
    {
        return match ($role) {
            Role::Administrateur => route('admin.utilisateurs.index', ['q' => $etudiant->matricule]),
            Role::Professeur => route('professeur.classes.show', ['filiere' => $etudiant->filiere, 'niveau' => $etudiant->niveau]),
            Role::AgentComptable => route('comptabilite.debiteurs.index'),
            Role::AgentRecouvrement => route('recouvrement.impayes.index'),
            Role::ResponsableFinancier => route('financier.paiements.index'),
            default => route('dashboard'),
        };
    }
}
