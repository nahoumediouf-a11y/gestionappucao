<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Projet;
use App\Models\User;
use Illuminate\Support\Facades\Http;

class AssistantService
{
    /**
     * @param  array<int, array{role: string, content: string}>  $historique
     */
    public function repondre(User $user, string $message, array $historique = []): string
    {
        $apiKey = config('services.anthropic.key');

        if (! $apiKey) {
            return "L'assistant IA n'est pas encore configuré sur ce serveur (clé API manquante). "
                ."Demandez à l'administrateur de définir ANTHROPIC_API_KEY dans le fichier .env.";
        }

        $messages = [
            ...array_map(
                fn (array $m) => ['role' => $m['role'], 'content' => $m['content']],
                $historique
            ),
            ['role' => 'user', 'content' => $message],
        ];

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type' => 'application/json',
        ])->post('https://api.anthropic.com/v1/messages', [
            'model' => config('services.anthropic.model'),
            'max_tokens' => 1024,
            'system' => $this->systemPrompt($user),
            'messages' => $messages,
        ]);

        if ($response->failed()) {
            return "Désolé, l'assistant IA est momentanément indisponible. Réessayez plus tard.";
        }

        return $response->json('content.0.text') ?? "Désolé, je n'ai pas pu générer de réponse.";
    }

    private function systemPrompt(User $user): string
    {
        $base = <<<PROMPT
            Tu es l'assistant virtuel de l'application "Recouvrement UCAO", une plateforme de gestion académique et financière universitaire.
            Réponds toujours en français, de manière concise, claire et bienveillante.
            Tu ne réponds qu'aux questions liées à l'application : profil, notes, absences, paiements, emploi du temps, projets de classe et situation administrative.
            Si une question sort de ce cadre, indique poliment que tu ne peux aider que sur ces sujets.
            Voici les informations actuelles de l'utilisateur connecté, à utiliser pour personnaliser tes réponses :

            PROMPT;

        return $base.$this->contexte($user);
    }

    private function contexte(User $user): string
    {
        return match ($user->role) {
            Role::Etudiant => $this->contexteEtudiant($user),
            Role::Administrateur => $this->contexteAdmin($user),
            Role::Professeur => $this->contexteProfesseur($user),
            default => "Nom : {$user->nom_complet}\nRôle : {$user->role->label()}",
        };
    }

    private function contexteEtudiant(User $user): string
    {
        $etudiant = $user->etudiant;

        if (! $etudiant) {
            return "Nom : {$user->nom_complet}\nRôle : Étudiant (profil incomplet)";
        }

        $absencesNonJustifiees = $etudiant->absencesNonJustifieesCount();
        $situationRouge = $etudiant->enSituationRouge() ? 'OUI — accès aux examens bloqué' : 'Non';

        $notes = $etudiant->notes()->get()
            ->map(fn ($n) => "{$n->matiere} ({$n->session}) : {$n->valeur}/20")
            ->implode("\n- ");

        $projets = Projet::where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderBy('date_limite')
            ->get()
            ->map(fn ($p) => "{$p->titre} ({$p->matiere}) — échéance {$p->date_limite->format('d/m/Y')} — {$p->statut()}")
            ->implode("\n- ");

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Étudiant
            Matricule : {$etudiant->matricule}
            Filière / Niveau : {$etudiant->filiere} {$etudiant->niveau}
            Téléphone : {$user->telephone}
            Adresse : {$etudiant->adresse}
            Contact d'urgence : {$etudiant->contact_urgence_nom} ({$etudiant->contact_urgence_telephone})
            Solde restant à payer : {$etudiant->solde} FCFA
            Moyenne générale : {$etudiant->moyenne()}/20
            Notes :
            - {$notes}
            Absences non justifiées : {$absencesNonJustifiees}
            Situation rouge (blocage examens) : {$situationRouge}
            Projets de classe en cours :
            - {$projets}
            CTX;
    }

    private function contexteProfesseur(User $user): string
    {
        $projets = Projet::where('professeur_id', $user->id)
            ->orderBy('date_limite')
            ->get()
            ->map(fn ($p) => "{$p->titre} ({$p->filiere} {$p->niveau}, {$p->matiere}) — échéance {$p->date_limite->format('d/m/Y')} — {$p->statut()}")
            ->implode("\n- ");

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Professeur
            Projets de classe assignés :
            - {$projets}
            CTX;
    }

    private function contexteAdmin(User $user): string
    {
        $nonLues = $user->unreadNotifications()->count();
        $etudiantsRouge = \App\Models\Etudiant::all()->filter(fn ($e) => $e->enSituationRouge())->count();
        $totalEtudiants = \App\Models\Etudiant::count();

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Administrateur
            Nombre total d'étudiants : {$totalEtudiants}
            Étudiants en situation rouge (3+ absences non justifiées) : {$etudiantsRouge}
            Notifications non lues : {$nonLues}
            CTX;
    }
}
