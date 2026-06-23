<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

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

    /** Messages envoyés. */
    public function envoyes(): View
    {
        $messages = Message::with('destinataire')
            ->where('expediteur_id', auth()->id())
            ->latest()
            ->paginate(15);

        return view('messagerie.index', [
            'messages' => $messages,
            'onglet' => 'envoyes',
        ]);
    }

    public function create(Request $request): View
    {
        return view('messagerie.create', [
            'destinataires' => $this->destinatairesDisponibles(),
            'destinataireId' => $request->integer('a') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'destinataire_id' => ['required', 'integer', Rule::notIn([auth()->id()]), 'exists:users,id'],
            'sujet' => ['required', 'string', 'max:255'],
            'corps' => ['required', 'string', 'max:5000'],
        ], [
            'destinataire_id.not_in' => 'Vous ne pouvez pas vous envoyer un message à vous-même.',
        ]);

        Message::create([
            'expediteur_id' => auth()->id(),
            'destinataire_id' => $validated['destinataire_id'],
            'sujet' => $validated['sujet'],
            'corps' => $validated['corps'],
        ]);

        return redirect()->route('messagerie.envoyes')->with('success', 'Message envoyé.');
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

        $message->load('expediteur', 'destinataire');

        return view('messagerie.show', ['message' => $message]);
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

    /** Liste des destinataires possibles (tous les comptes actifs sauf soi-même). */
    private function destinatairesDisponibles()
    {
        return User::where('id', '!=', auth()->id())
            ->where('statut', 'actif')
            ->orderBy('nom')
            ->get(['id', 'nom', 'prenom', 'role']);
    }
}
