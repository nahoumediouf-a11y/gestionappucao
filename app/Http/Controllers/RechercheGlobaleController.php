<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class RechercheGlobaleController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();

        // Recherche réservée au personnel (l'étudiant n'a pas de recherche globale).
        abort_if($user->role === Role::Etudiant, Response::HTTP_FORBIDDEN);

        $q = trim((string) $request->query('q'));
        $type = $request->query('type') === 'personnel' && $user->role === Role::Administrateur ? 'personnel' : 'etudiant';

        $etudiants = collect();
        $personnel = collect();

        if ($q !== '') {
            if ($type === 'personnel') {
                $personnel = User::where('role', '!=', Role::Etudiant->value)
                    ->where(function ($query) use ($q) {
                        $query->where('nom', 'like', "%{$q}%")
                            ->orWhere('prenom', 'like', "%{$q}%")
                            ->orWhere('login', 'like', "%{$q}%")
                            ->orWhere('email', 'like', "%{$q}%");
                    })
                    ->orderBy('nom')
                    ->limit(50)
                    ->get();
            } else {
                $etudiants = Etudiant::with('user')
                    ->where(function ($query) use ($q) {
                        $query->where('matricule', 'like', "%{$q}%")
                            ->orWhere('filiere', 'like', "%{$q}%")
                            ->orWhere('niveau', 'like', "%{$q}%")
                            ->orWhereHas('user', fn ($u) => $u->where('nom', 'like', "%{$q}%")->orWhere('prenom', 'like', "%{$q}%"));
                    })
                    ->when($user->role === Role::Professeur, fn ($query) => $this->limiterAuxClasses($query, $user->id))
                    ->orderBy('matricule')
                    ->limit(50)
                    ->get();
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

    /** Restreint la recherche aux étudiants des classes (filière+niveau) enseignées par le prof. */
    private function limiterAuxClasses($query, int $profId)
    {
        $classes = EmploiDuTemps::where('professeur_id', $profId)
            ->get(['filiere', 'niveau'])
            ->map(fn ($c) => $c->filiere.'|'.$c->niveau)
            ->unique();

        return $query->where(function ($q) use ($classes) {
            if ($classes->isEmpty()) {
                $q->whereRaw('1 = 0');

                return;
            }
            foreach ($classes as $cle) {
                [$filiere, $niveau] = explode('|', $cle);
                $q->orWhere(fn ($sub) => $sub->where('filiere', $filiere)->where('niveau', $niveau));
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
