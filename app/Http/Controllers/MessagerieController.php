<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\EmploiDuTemps;
use App\Models\Etudiant;
use App\Models\Message;
use App\Models\MessagePieceJointe;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MessagerieController extends Controller
{
    /** Boîte de réception. */
    public function index(): View
    {
        $messages = Message::with('expediteur')
            ->where('destinataire_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('messagerie.index', [
            'messages' => $messages,
            'onglet' => 'recus',
        ]);
    }

    /** Messages envoyés, regroupés par diffusion (un envoi groupé = une ligne). */
    public function envoyes(): View
    {
        // Une "ligne" = un message individuel (diffusion_id NULL) ou un envoi groupé.
        $groupes = Message::query()
            ->where('expediteur_id', auth()->id())
            ->selectRaw('MAX(id) as id, COUNT(*) as nb')
            ->groupByRaw('COALESCE(diffusion_id, CAST(id AS TEXT))')
            ->orderByRaw('MAX(created_at) DESC')
            ->paginate(15);

        // Charge les messages représentatifs avec leur destinataire, dans l'ordre.
        $nbParId = $groupes->getCollection()->pluck('nb', 'id');
        $representatifs = Message::with('destinataire')
            ->whereIn('id', $nbParId->keys())
            ->get()
            ->keyBy('id');

        $groupes->setCollection(
            $groupes->getCollection()->map(function ($ligne) use ($nbParId, $representatifs) {
                $message = $representatifs[$ligne->id];
                $message->setAttribute('nb_destinataires', (int) $nbParId[$ligne->id]);

                return $message;
            })
        );

        return view('messagerie.index', [
            'messages' => $groupes,
            'onglet' => 'envoyes',
        ]);
    }

    public function create(Request $request): View
    {
        $expediteur = auth()->user();
        $autorisees = $this->requeteDestinatairesAutorises($expediteur);

        // Personnel (comptes non-étudiants) que l'expéditeur peut contacter.
        $personnel = (clone $autorisees)
            ->where('role', '!=', Role::Etudiant->value)
            ->orderBy('nom')->orderBy('prenom')
            ->get(['id', 'nom', 'prenom', 'role']);

        // Rôles ciblables en bloc (« Tous les professeurs »…), avec effectifs.
        $rolesDisponibles = $personnel->groupBy(fn (User $u) => $u->role->value)
            ->map(fn (Collection $g) => ['role' => $g->first()->role, 'nb' => $g->count()])
            ->values();

        // Classes ciblables (filière + niveau) avec effectif, si l'expéditeur peut
        // contacter des étudiants.
        $classesDisponibles = (clone $autorisees)
            ->where('users.role', Role::Etudiant->value)
            ->join('etudiants', 'etudiants.user_id', '=', 'users.id')
            ->selectRaw('etudiants.filiere as filiere, etudiants.niveau as niveau, COUNT(*) as effectif')
            ->groupBy('etudiants.filiere', 'etudiants.niveau')
            ->orderBy('etudiants.filiere')->orderBy('etudiants.niveau')
            ->get();

        return view('messagerie.create', [
            'personnel' => $personnel,
            'rolesDisponibles' => $rolesDisponibles,
            'classesDisponibles' => $classesDisponibles,
            'peutCiblerEtudiants' => $expediteur->role !== Role::Etudiant,
            'destinataireId' => $request->integer('a') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $expediteur = auth()->user();

        $validated = $request->validate([
            'destinataire_id' => ['nullable', 'integer', Rule::notIn([$expediteur->id]), 'exists:users,id'],
            'sujet' => ['required', 'string', 'max:255'],
            'corps' => ['required', 'string', 'max:5000'],
            'classes' => ['array'],
            'classes.*' => ['string'],
            'roles' => ['array'],
            'roles.*' => ['string'],
            'etudiants' => ['array'],
            'etudiants.*' => ['integer'],
            'users' => ['array'],
            'users.*' => ['integer'],
            'pieces' => ['array', 'max:5'],
            'pieces.*' => ['file', 'max:8192', 'mimes:jpg,jpeg,png,gif,webp,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,zip'],
        ], [
            'destinataire_id.not_in' => 'Vous ne pouvez pas vous envoyer un message à vous-même.',
            'pieces.max' => 'Vous pouvez joindre au maximum 5 fichiers.',
            'pieces.*.max' => 'Chaque fichier ne doit pas dépasser 8 Mo.',
            'pieces.*.mimes' => 'Type de fichier non autorisé.',
        ]);

        // Liste finale TOUJOURS recalculée et filtrée côté serveur (sécurité).
        $destinataires = $this->resoudreDestinataires($request, $expediteur);

        if ($destinataires->isEmpty()) {
            return back()->withInput()
                ->withErrors(['destinataires' => 'Sélectionnez au moins un destinataire valide.']);
        }

        // Une diffusion par envoi (groupé OU individuel) : sert de clé aux pièces jointes.
        $diffusionId = (string) Str::uuid();
        $maintenant = now();

        // Stocke les fichiers UNE seule fois pour toute la diffusion.
        $this->enregistrerPiecesJointes($request, $expediteur, $diffusionId);

        $lignes = $destinataires->map(fn (int $id) => [
            'diffusion_id' => $diffusionId,
            'expediteur_id' => $expediteur->id,
            'destinataire_id' => $id,
            'sujet' => $validated['sujet'],
            'corps' => $validated['corps'],
            'lu_a' => null,
            'created_at' => $maintenant,
            'updated_at' => $maintenant,
        ])->all();

        foreach (array_chunk($lignes, 500) as $bloc) {
            Message::insert($bloc);
        }

        $nb = $destinataires->count();

        return redirect()->route('messagerie.envoyes')->with(
            'success',
            $nb > 1 ? "Message envoyé à {$nb} destinataires." : 'Message envoyé.'
        );
    }

    public function show(Message $message): View
    {
        abort_unless(
            in_array(auth()->id(), [$message->expediteur_id, $message->destinataire_id], true),
            Response::HTTP_FORBIDDEN
        );

        // Marque comme lu quand le destinataire l'ouvre.
        if ($message->destinataire_id === auth()->id() && ! $message->estLu()) {
            $message->update(['lu_a' => now()]);
        }

        $message->load('expediteur', 'destinataire', 'piecesJointes');

        return view('messagerie.show', ['message' => $message]);
    }

    /** Téléchargement d'une pièce jointe (réservé à l'expéditeur et aux destinataires). */
    public function pieceJointe(MessagePieceJointe $piece): StreamedResponse
    {
        $userId = auth()->id();

        $autorise = $piece->expediteur_id === $userId
            || Message::where('diffusion_id', $piece->diffusion_id)
                ->where('destinataire_id', $userId)
                ->exists();

        abort_unless($autorise, Response::HTTP_FORBIDDEN);
        abort_unless(Storage::disk('local')->exists($piece->chemin), Response::HTTP_NOT_FOUND);

        return Storage::disk('local')->download($piece->chemin, $piece->nom);
    }

    public function destroy(Message $message): RedirectResponse
    {
        abort_unless(
            in_array(auth()->id(), [$message->expediteur_id, $message->destinataire_id], true),
            Response::HTTP_FORBIDDEN
        );

        $message->delete();

        return redirect()->route('messagerie.index')->with('success', 'Message supprimé.');
    }

    /** Stocke les fichiers joints (une fois par diffusion) sur le disque privé. */
    private function enregistrerPiecesJointes(Request $request, User $expediteur, string $diffusionId): void
    {
        foreach ($request->file('pieces', []) as $fichier) {
            if (! $fichier || ! $fichier->isValid()) {
                continue;
            }

            MessagePieceJointe::create([
                'diffusion_id' => $diffusionId,
                'expediteur_id' => $expediteur->id,
                'chemin' => $fichier->store('messagerie', 'local'),
                'nom' => $fichier->getClientOriginalName(),
                'mime' => $fichier->getClientMimeType(),
                'taille' => $fichier->getSize(),
            ]);
        }
    }

    /**
     * Résout toutes les cibles (destinataire unique, étudiants/personnel individuels,
     * classes entières, rôles entiers) en une liste d'identifiants utilisateur, puis
     * n'en garde que ceux que l'expéditeur a le DROIT de contacter.
     *
     * @return Collection<int, int>
     */
    private function resoudreDestinataires(Request $request, User $expediteur): Collection
    {
        $candidats = collect();

        if ($request->filled('destinataire_id')) {
            $candidats->push((int) $request->input('destinataire_id'));
        }

        $candidats = $candidats
            ->merge($request->collect('etudiants')->map(fn ($v) => (int) $v))
            ->merge($request->collect('users')->map(fn ($v) => (int) $v));

        // Classes « filiere|niveau » -> identifiants des étudiants concernés.
        $classes = $request->collect('classes')->filter();
        if ($classes->isNotEmpty()) {
            $candidats = $candidats->merge(
                Etudiant::query()->where(function (Builder $q) use ($classes) {
                    foreach ($classes as $cle) {
                        [$filiere, $niveau] = array_pad(explode('|', $cle, 2), 2, null);
                        $q->orWhere(fn (Builder $s) => $s->where('filiere', $filiere)->where('niveau', $niveau));
                    }
                })->pluck('user_id')
            );
        }

        // Rôles entiers -> identifiants des comptes ayant ce rôle.
        $roles = $request->collect('roles')->filter();
        if ($roles->isNotEmpty()) {
            $candidats = $candidats->merge(User::whereIn('role', $roles->all())->pluck('id'));
        }

        // Sécurité : intersection avec l'ensemble réellement autorisé, sans soi-même.
        $autorisees = $this->requeteDestinatairesAutorises($expediteur)->pluck('id');

        return $candidats->map(fn ($v) => (int) $v)
            ->unique()
            ->intersect($autorisees)
            ->reject(fn (int $id) => $id === $expediteur->id)
            ->values();
    }

    /**
     * Ensemble des comptes que l'expéditeur a le droit de contacter, selon son rôle :
     * - Administrateur / comptabilité / recouvrement / finances : tout le monde ;
     * - Professeur : le personnel + uniquement les étudiants de SES classes ;
     * - Étudiant : uniquement le personnel (pas d'autres étudiants).
     */
    private function requeteDestinatairesAutorises(User $expediteur): Builder
    {
        $query = User::query()
            ->where('users.id', '!=', $expediteur->id)
            ->where('statut', 'actif');

        return match ($expediteur->role) {
            Role::Etudiant => $query->where('role', '!=', Role::Etudiant->value),
            Role::Professeur => $query->where(function (Builder $q) use ($expediteur) {
                $q->where('role', '!=', Role::Etudiant->value)
                    ->orWhereHas('etudiant', fn (Builder $e) => $this->limiterAuxClassesDuProf($e, $expediteur->id));
            }),
            default => $query,
        };
    }

    /** Restreint une requête d'étudiants aux classes (filière+niveau) enseignées par le prof. */
    private function limiterAuxClassesDuProf(Builder $query, int $profId): void
    {
        $classes = EmploiDuTemps::where('professeur_id', $profId)
            ->get(['filiere', 'niveau'])
            ->map(fn ($c) => $c->filiere.'|'.$c->niveau)
            ->unique();

        if ($classes->isEmpty()) {
            $query->whereRaw('1 = 0');

            return;
        }

        $query->where(function (Builder $q) use ($classes) {
            foreach ($classes as $cle) {
                [$filiere, $niveau] = explode('|', $cle);
                $q->orWhere(fn (Builder $s) => $s->where('filiere', $filiere)->where('niveau', $niveau));
            }
        });
    }
}
