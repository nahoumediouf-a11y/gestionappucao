<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\Document;
use App\Models\EngagementPaiement;
use App\Models\Etudiant;
use App\Models\Paiement;
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
        $apiKey = config('services.gemini.key');

        if (! $apiKey) {
            return "L'assistant IA n'est pas encore configuré sur ce serveur (clé API manquante). "
                .'Demandez à l\'administrateur de définir GEMINI_API_KEY dans le fichier .env.';
        }

        $contents = [
            ...array_map(
                fn (array $m) => [
                    'role' => $m['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $m['content']]],
                ],
                $historique
            ),
            ['role' => 'user', 'parts' => [['text' => $message]]],
        ];

        $response = Http::post(
            'https://generativelanguage.googleapis.com/v1beta/models/'.config('services.gemini.model').':generateContent?key='.$apiKey,
            [
                'contents' => $contents,
                'systemInstruction' => ['parts' => [['text' => $this->systemPrompt($user)]]],
            ]
        );

        if ($response->failed()) {
            return "Désolé, l'assistant IA est momentanément indisponible. Réessayez plus tard.";
        }

        return $response->json('candidates.0.content.parts.0.text') ?? "Désolé, je n'ai pas pu générer de réponse.";
    }

    // -------------------------------------------------------------------------
    //  System prompt (instructions + contexte données réelles)
    // -------------------------------------------------------------------------

    private function systemPrompt(User $user): string
    {
        $instructions = $this->instructions($user);
        $contexte     = $this->contexte($user);
        $connaissance = $this->connaissanceGenerale();

        return <<<PROMPT
            Tu es l'assistant virtuel de "SIGE UCAO", une plateforme de gestion académique et financière universitaire.
            Réponds toujours en français, de manière concise, claire et professionnelle.
            Utilise le Markdown : tableaux, listes, gras, titres selon ce qui est le plus lisible.
            {$instructions}
            --- RÈGLEMENT ET INFORMATIONS GÉNÉRALES DE L'ÉTABLISSEMENT ---
            {$connaissance}
            --- DONNÉES ACTUELLES DE L'UTILISATEUR CONNECTÉ ---
            {$contexte}
            PROMPT;
    }

    /**
     * Base de connaissances institutionnelle (règlement, scolarité, paiement) commune à tous les rôles.
     * Sert de contexte factuel fiable pour éviter que l'IA invente des règles ou des montants.
     */
    private function connaissanceGenerale(): string
    {
        $scolarites = collect(Etudiant::SCOLARITE_PAR_NIVEAU)
            ->map(fn ($montant, $niveau) => "{$niveau} : ".number_format($montant, 0, ',', ' ').' FCFA/an')
            ->implode("\n- ");

        $modes = collect(Paiement::MODES)->implode(', ');

        return <<<TXT
            Frais de scolarité annuels par niveau :
            - {$scolarites}
            La scolarité est payable en 6 tranches mensuelles égales, de septembre à février.

            Modes de paiement acceptés : {$modes}.
            Un paiement déclaré par un étudiant (Wave, Orange Money, virement, etc.) passe par le statut
            "en attente de validation" jusqu'à confirmation par le service comptable (sous 24h en général).
            Les paiements via PayDunya (Wave, Orange Money, Free Money) sont validés automatiquement après confirmation du prestataire.

            Règlement assiduité : un étudiant atteignant 3 absences non justifiées passe en "situation rouge"
            et son accès aux examens est bloqué jusqu'à régularisation auprès de l'administration.

            En cas de solde impayé prolongé, le service de recouvrement peut proposer un engagement de paiement
            (échéancier) à l'étudiant ; le non-respect d'un engagement entraîne une relance.
            TXT;
    }

    // -------------------------------------------------------------------------
    //  Instructions spécifiques par rôle (ce que l'assistant PEUT faire)
    // -------------------------------------------------------------------------

    private function instructions(User $user): string
    {
        return match ($user->role) {
            Role::Etudiant => $this->instructionsEtudiant(),
            Role::Professeur => $this->instructionsProfesseur(),
            Role::Administrateur => $this->instructionsAdmin(),
            Role::AgentComptable => $this->instructionsAgentComptable(),
            Role::AgentRecouvrement => $this->instructionsAgentRecouvrement(),
            Role::ResponsableFinancier => $this->instructionsResponsableFinancier(),
        };
    }

    private function instructionsEtudiant(): string
    {
        return <<<TXT
            Tu aides uniquement cet étudiant sur les sujets suivants :
            - Son profil, son solde restant à payer et les démarches pour régulariser.
            - Ses notes, sa moyenne, son bulletin et ses absences.
            - Son emploi du temps et les projets / devoirs / examens à rendre.
            - Les documents de cours disponibles.
            - Tutorat académique : expliquer des notions de cours de sa filière, proposer des plans de révision (sous forme de tableau jour/créneau/matière), analyser ses points faibles et donner des conseils méthodologiques.
            - L'alerter si sa situation est préoccupante (absences proches du seuil, solde bloquant le bulletin) et lui indiquer les démarches à suivre.
            Ne réponds pas aux questions sans rapport avec sa scolarité à l'UCAO.
            TXT;
    }

    private function instructionsProfesseur(): string
    {
        return <<<TXT
            Tu aides uniquement ce professeur sur les sujets suivants :
            - Son emploi du temps : classes, horaires, salles, matières.
            - Les projets, devoirs et examens qu'il a assignés : suivi des échéances, statuts.
            - La liste des étudiants de ses classes (filière/niveau).
            - La saisie et la modification des notes et des absences.
            - Les notifications reçues (ex. changement de salle).
            Ne réponds pas aux questions financières, comptables ou administratives des autres services.
            TXT;
    }

    private function instructionsAdmin(): string
    {
        return <<<TXT
            Tu aides uniquement l'administrateur sur les sujets suivants :
            - Gestion des utilisateurs (créer, modifier, activer/désactiver des comptes).
            - Gestion de l'emploi du temps (créneaux, salles, professeurs).
            - Statistiques globales : nombre d'étudiants, situations rouges, répartition par filière/niveau.
            - Notifications système et alertes.
            - Questions d'organisation ou de paramétrage de l'application SIGE UCAO.
            Ne réponds pas aux questions de tutorat académique ou de comptabilité détaillée.
            TXT;
    }

    private function instructionsAgentComptable(): string
    {
        return <<<TXT
            Tu aides uniquement l'agent comptable sur les sujets suivants :
            - Enregistrement et modification des paiements étudiants.
            - Validation ou rejet des déclarations de paiement soumises par les étudiants (Wave, Orange Money, Visa, etc.).
            - Génération et consultation des reçus de paiement.
            - Consultation de la liste des débiteurs (étudiants avec solde impayé).
            - Suivi des paiements en attente de validation.
            - Questions sur les modes de paiement acceptés (espèces, virement, Wave, Orange Money, Visa, chèque).
            Ne réponds pas aux questions pédagogiques, académiques ou d'administration système.
            TXT;
    }

    private function instructionsAgentRecouvrement(): string
    {
        return <<<TXT
            Tu aides uniquement l'agent de recouvrement sur les sujets suivants :
            - Liste des étudiants débiteurs (solde impayé) et montants dus.
            - Engagements de paiement : suivi des échéances à venir et honorées.
            - Envoi de relances et suivi des impayés.
            - Statistiques de recouvrement : taux de recouvrement, montants recouvrés vs en attente.
            - Identification des étudiants en situation critique (solde élevé, engagements non honorés).
            Ne réponds pas aux questions pédagogiques, académiques ou d'administration système.
            TXT;
    }

    private function instructionsResponsableFinancier(): string
    {
        return <<<TXT
            Tu aides uniquement le responsable financier sur les sujets suivants :
            - Vue d'ensemble financière : total encaissé, soldes impayés, taux de recouvrement.
            - Validation et supervision des opérations comptables.
            - Rapports financiers : répartition des paiements par mode, par période, par filière.
            - Supervision du recouvrement : étudiants débiteurs, engagements en cours.
            - Statistiques et indicateurs de performance financière de l'établissement.
            Ne réponds pas aux questions pédagogiques, académiques ou d'administration système.
            TXT;
    }

    // -------------------------------------------------------------------------
    //  Contexte données réelles par rôle
    // -------------------------------------------------------------------------

    private function contexte(User $user): string
    {
        return match ($user->role) {
            Role::Etudiant           => $this->contexteEtudiant($user),
            Role::Professeur         => $this->contexteProfesseur($user),
            Role::Administrateur     => $this->contexteAdmin($user),
            Role::AgentComptable     => $this->contexteAgentComptable($user),
            Role::AgentRecouvrement  => $this->contexteAgentRecouvrement($user),
            Role::ResponsableFinancier => $this->contexteResponsableFinancier($user),
        };
    }

    private function contexteEtudiant(User $user): string
    {
        $etudiant = $user->etudiant;

        if (! $etudiant) {
            return "Nom : {$user->nom_complet}\nRôle : Étudiant (profil incomplet)";
        }

        $absNJ = $etudiant->absencesNonJustifieesCount();
        $rouge = $etudiant->enSituationRouge() ? 'OUI — accès aux examens bloqué' : 'Non';

        $notes = $etudiant->notes()->get()
            ->map(fn ($n) => "{$n->matiere} ({$n->session}) : {$n->valeur}/20")
            ->implode("\n- ");

        $projets = Projet::where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderBy('date_limite')->get()
            ->map(fn ($p) => "[{$p->typeLabel()}] {$p->titre} ({$p->matiere}) — échéance {$p->date_limite->format('d/m/Y')} — {$p->statut()}")
            ->implode("\n- ");

        $documents = Document::where('filiere', $etudiant->filiere)
            ->where('niveau', $etudiant->niveau)
            ->orderByDesc('created_at')->get()
            ->map(fn ($d) => "{$d->titre} ({$d->matiere}) — {$d->nom_original}")
            ->implode("\n- ");

        $edt = $etudiant->emploiDuTemps()
            ->map(fn ($c) => sprintf('%s %s-%s : %s (salle %s)%s',
                $c->jour, substr($c->heure_debut, 0, 5), substr($c->heure_fin, 0, 5),
                $c->matiere, $c->salle ?: '—',
                $c->professeur ? ' — Prof. '.$c->professeur->nom_complet : ''))
            ->implode("\n- ");

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Étudiant
            Matricule : {$etudiant->matricule}
            Filière / Classe : {$etudiant->filiere} {$etudiant->niveau}
            Solde restant à payer : {$etudiant->soldeReel()} FCFA
            Moyenne générale : {$etudiant->moyenne()}/20
            Absences non justifiées : {$absNJ} (seuil blocage : 3)
            Situation rouge (accès examens bloqué) : {$rouge}
            Notes par matière :
            - {$notes}
            Projets / Devoirs / Examens à venir :
            - {$projets}
            Documents de cours disponibles :
            - {$documents}
            Emploi du temps :
            - {$edt}
            CTX;
    }

    private function contexteProfesseur(User $user): string
    {
        $edt = $user->creneaux()->orderBy('jour')->orderBy('heure_debut')->get()
            ->map(fn ($c) => sprintf('%s %s-%s : %s (%s %s, salle %s)',
                $c->jour, substr($c->heure_debut, 0, 5), substr($c->heure_fin, 0, 5),
                $c->matiere, $c->filiere, $c->niveau, $c->salle ?: '—'))
            ->implode("\n- ");

        $projets = Projet::where('professeur_id', $user->id)->orderBy('date_limite')->get()
            ->map(fn ($p) => "[{$p->typeLabel()}] {$p->titre} ({$p->filiere} {$p->niveau}, {$p->matiere}) — échéance {$p->date_limite->format('d/m/Y')} — {$p->statut()}")
            ->implode("\n- ");

        $classes = $user->creneaux()->select('filiere', 'niveau')->distinct()->get()
            ->map(fn ($c) => "{$c->filiere} {$c->niveau}")->unique()->implode(', ');

        $nbEtudiants = Etudiant::whereIn(
            \DB::raw("filiere || ' ' || niveau"),
            $user->creneaux()->select(\DB::raw("filiere || ' ' || niveau"))->distinct()->pluck(\DB::raw("filiere || ' ' || niveau"))
        )->count();

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Professeur
            Classes enseignées : {$classes}
            Nombre d'étudiants concernés : {$nbEtudiants}
            Emploi du temps :
            - {$edt}
            Projets / Devoirs / Examens assignés :
            - {$projets}
            CTX;
    }

    private function contexteAdmin(User $user): string
    {
        $totalEtudiants   = Etudiant::count();
        $enAttente        = User::where('statut', 'en_attente')->count();
        $etudiantsRouge   = Etudiant::all()->filter(fn ($e) => $e->enSituationRouge())->count();
        $totalProfesseurs = User::where('role', Role::Professeur)->count();
        $nonLues          = $user->unreadNotifications()->count();

        $filieres = Etudiant::select('filiere', 'niveau')
            ->selectRaw('count(*) as nb')
            ->groupBy('filiere', 'niveau')
            ->orderBy('filiere')->orderBy('niveau')
            ->get()
            ->map(fn ($r) => "{$r->filiere} {$r->niveau} : {$r->nb} étudiant(s)")
            ->implode("\n- ");

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Administrateur
            Étudiants inscrits : {$totalEtudiants}
            Comptes en attente de validation : {$enAttente}
            Étudiants en situation rouge (≥3 absences non justifiées) : {$etudiantsRouge}
            Professeurs actifs : {$totalProfesseurs}
            Notifications non lues : {$nonLues}
            Répartition par filière / classe :
            - {$filieres}
            CTX;
    }

    private function contexteAgentComptable(User $user): string
    {
        $enAttente = Paiement::where('statut', 'en_attente_validation')
            ->with('etudiant.user')->get();

        $listeAttente = $enAttente->map(fn ($p) => sprintf(
            '%s — %s — %s FCFA — %s — réf. %s',
            $p->etudiant->user->nom_complet,
            $p->etudiant->matricule,
            number_format((float) $p->montant, 0, ',', ' '),
            $p->modeLabel(),
            $p->reference
        ))->implode("\n- ");

        $recents = Paiement::where('statut', 'valide')
            ->orderByDesc('date_paiement')->take(10)
            ->with('etudiant.user')->get()
            ->map(fn ($p) => sprintf('%s — %s FCFA — %s — %s',
                $p->etudiant->user->nom_complet,
                number_format((float) $p->montant, 0, ',', ' '),
                $p->modeLabel(),
                $p->date_paiement->format('d/m/Y')))
            ->implode("\n- ");

        $totalMois = Paiement::where('statut', 'valide')
            ->whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->sum('montant');

        $nbDebiteurs = Etudiant::where('solde', '>', 0)->count();

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Agent comptable
            Paiements encaissés ce mois-ci : {$totalMois} FCFA
            Étudiants avec solde impayé (débiteurs) : {$nbDebiteurs}
            Déclarations en attente de validation ({$enAttente->count()}) :
            - {$listeAttente}
            10 derniers paiements validés :
            - {$recents}
            CTX;
    }

    private function contexteAgentRecouvrement(User $user): string
    {
        $debiteurs = Etudiant::where('solde', '>', 0)
            ->with('user')->orderByDesc('solde')->take(15)->get()
            ->map(fn ($e) => sprintf('%s (%s) — %s FCFA restant',
                $e->user->nom_complet, $e->matricule,
                number_format((float) $e->solde, 0, ',', ' ')))
            ->implode("\n- ");

        $totalImpaye = Etudiant::where('solde', '>', 0)->sum('solde');
        $nbDebiteurs = Etudiant::where('solde', '>', 0)->count();

        $engagements = EngagementPaiement::where('statut', 'planifie')
            ->orderBy('echeance')->take(10)
            ->with('etudiant.user')->get()
            ->map(fn ($eg) => sprintf('%s — %s FCFA — échéance %s',
                $eg->etudiant->user->nom_complet,
                number_format((float) $eg->montant, 0, ',', ' '),
                $eg->echeance->format('d/m/Y')))
            ->implode("\n- ");

        $totalRecouvre = Paiement::where('statut', 'valide')->sum('montant');

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Agent de recouvrement
            Nombre de débiteurs : {$nbDebiteurs}
            Total impayé global : {$totalImpaye} FCFA
            Total recouvré (tous paiements validés) : {$totalRecouvre} FCFA
            Top débiteurs (solde restant le plus élevé) :
            - {$debiteurs}
            Prochains engagements de paiement planifiés :
            - {$engagements}
            CTX;
    }

    private function contexteResponsableFinancier(User $user): string
    {
        $totalMois = Paiement::where('statut', 'valide')
            ->whereMonth('date_paiement', now()->month)
            ->whereYear('date_paiement', now()->year)
            ->sum('montant');

        $totalAnnee = Paiement::where('statut', 'valide')
            ->whereYear('date_paiement', now()->year)
            ->sum('montant');

        $totalImpaye   = Etudiant::where('solde', '>', 0)->sum('solde');
        $nbDebiteurs   = Etudiant::where('solde', '>', 0)->count();
        $enAttente     = Paiement::where('statut', 'en_attente_validation')->count();

        $parMode = Paiement::where('statut', 'valide')
            ->selectRaw('mode_paiement, count(*) as nb, sum(montant) as total')
            ->groupBy('mode_paiement')->get()
            ->map(fn ($r) => sprintf('%s : %d paiement(s) — %s FCFA',
                Paiement::MODES[$r->mode_paiement] ?? $r->mode_paiement,
                $r->nb, number_format((float) $r->total, 0, ',', ' ')))
            ->implode("\n- ");

        $nonLues = $user->unreadNotifications()->count();

        return <<<CTX
            Nom : {$user->nom_complet}
            Rôle : Responsable financier
            Encaissements ce mois : {$totalMois} FCFA
            Encaissements cette année : {$totalAnnee} FCFA
            Total impayé en cours : {$totalImpaye} FCFA ({$nbDebiteurs} étudiants débiteurs)
            Déclarations en attente de validation : {$enAttente}
            Notifications non lues : {$nonLues}
            Répartition des paiements validés par mode :
            - {$parMode}
            CTX;
    }
}
