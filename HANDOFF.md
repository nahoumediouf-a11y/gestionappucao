# SIGE UCAO — Document de reprise (handoff)

> Colle ce fichier dans une nouvelle session Claude Code (ou donne-lui le chemin
> `/Users/nahoumediouf/classproject/recouvrement-ucao/HANDOFF.md`) pour reprendre
> le projet exactement là où il a été laissé.

---

## 1. Présentation

**SIGE UCAO** (Système Intégré de Gestion des Étudiants) est une application web
de gestion académique et financière pour l'UCAO Saint Michel (projet de classe /
soutenance). Elle gère les étudiants, paiements/recouvrement, notes, absences,
emplois du temps, documents de cours, projets, et intègre un assistant IA.

- **Répertoire projet** : `/Users/nahoumediouf/classproject/recouvrement-ucao`
- **Stack** : Laravel 12, PHP 8.2+ (8.5 installé), SQLite, Blade + Bootstrap 5
  (Bootstrap Icons), Tailwind v4 via Vite (assets), DomPDF, Chart.js (CDN).
- **C'est un dépôt git** (le dossier `recouvrement-ucao` lui-même).

---

## 2. Lancer le projet

```bash
cd /Users/nahoumediouf/classproject/recouvrement-ucao

# Serveur applicatif (suffit pour tout tester — les assets sont déjà buildés dans public/)
php artisan serve --port=8000           # http://127.0.0.1:8000

# (optionnel) assets en dev — node/npm sont à /usr/local/bin
#   ⚠️ Dans le shell zsh non-interactif, npm peut renvoyer "command not found"
#   alors que le binaire existe. Utiliser le chemin absolu si besoin :
/usr/local/bin/npm run dev

# Partage public via tunnel Cloudflare (lien temporaire, change à chaque lancement) :
cloudflared tunnel --url http://127.0.0.1:8000
#   Le tunnel quick est INSTABLE : s'il boucle sur "control stream failure",
#   le tuer et le relancer. Récupérer l'URL avec :
#   grep -o 'https://[a-z-]*\.trycloudflare\.com' dans la sortie.
```

Base de données : SQLite (`database/database.sqlite`). Réinitialiser les données :

```bash
php artisan migrate:fresh --seed --force   # recrée tout
php artisan db:seed --force                # re-seed sans tout effacer (updateOrCreate)
```

---

## 3. Comptes de démonstration

Mot de passe **`password`** pour tous. La page de login affiche un captcha
arithmétique (« combien font X + Y »).

| Login | Rôle |
|-------|------|
| `admin` | Administrateur |
| `comptable` | Agent comptable |
| `recouvrement` | Agent de recouvrement |
| `financier` | Responsable financier |
| `prof`, `prof2`…`prof5` | Professeurs |
| `etudiant1`, `etudiant2`, `etudiant3` | Étudiants principaux |
| `etudiant4`…`etudiant53` | 50 étudiants générés (EtudiantsDemoSeeder) |

> ⚠️ Ces identifiants **ne sont plus affichés** sur la page de login (retirés
> volontairement — pas professionnel). Garder cette liste pour les tests/démo.

---

## 4. Architecture

- **Rôles** : `app/Enums/Role.php` (administrateur, agent_comptable,
  agent_recouvrement, responsable_financier, etudiant, professeur).
- **Routing** : `routes/web.php` — groupes par rôle via middleware `role:xxx`,
  préfixes `admin/`, `comptabilite/`, `recouvrement/`, `financier/`,
  `etudiant/`, `professeur/`.
- **Controllers** : `app/Http/Controllers/{Admin,Auth,Comptabilite,Etudiant,Financier,Professeur,Recouvrement}/`.
- **Models** : `app/Models/` — Absence, ActivityLog, Document, DocumentCours,
  EmploiDuTemps, EngagementPaiement, Etudiant, Note, Paiement, Projet,
  PropositionProjet, User.
- **Services** : `AssistantService.php` (IA), `PaydunyaService.php` (paiement mobile).
- **Vues** : `resources/views/` — layouts `layouts/app` (auth) et
  `layouts/dashboard` (espace connecté). Partials dans `resources/views/partials/`.
- **PDF** : DomPDF (`barryvdh/laravel-dompdf`) — voir bulletins et emploi du temps.
- **Tests** : `tests/Feature/` (Auth, BulletinPdf, EmploiDuTempsSalle, RappelEcheance,
  Registration, RoleAccess). Lancer : `php artisan test`.

### Logique métier importante

- **Scolarité & solde** : le `solde` stocké en base peut être désynchronisé.
  La **source de vérité** est `Etudiant::soldeReel()` = `scolariteTotale()` −
  paiements validés. Frais par niveau dans `Etudiant::SCOLARITE_PAR_NIVEAU`
  (L1 650k → M2 900k FCFA). Toujours afficher `soldeReel()`, pas `->solde` brut.
- **Assistant IA** : Gemini (`GEMINI_API_KEY` + `GEMINI_MODEL=gemini-2.5-flash-lite`
  dans `.env`, mappés dans `config/services.php`). Prompt système + contexte
  par rôle dans `AssistantService.php`. Le modèle renvoie parfois un 503
  transitoire (surcharge Google) — pas un bug de config.

---

## 5. Travail réalisé dans la dernière session

1. **Nettoyage textes UI** : retrait du jargon UML (« diagramme de cas
   d'utilisation », « UC1 »), des placeholders « Ex : … », et de la section
   « Comptes de démonstration » sur le login. Titre login → « Connexion ».
   Tiret cadratin → tiret simple sur la page d'accueil.
2. **Correctif solde / situation financière** : ajout de `Etudiant::scolariteTotale()`
   et `Etudiant::soldeReel()`. `Etudiant/PaiementController`, `ProfilController`
   et `AssistantService` utilisent désormais `soldeReel()` (le solde restant
   s'affichait à 0 à tort).
3. **Module Emploi du temps — 4 phases intégrées et testées en live** :
   - **Type de séance** (CM/TD/TP/Examen) : migration `type`, constante
     `EmploiDuTemps::TYPES`, helpers `typeLabel()`/`typeCouleur()`, champ dans
     le formulaire admin, badge coloré dans la liste, types répartis au seed.
   - **Détection de conflits** : `EmploiDuTemps::detecterConflits()` (salle /
     professeur / classe déjà occupé(e) avec chevauchement horaire). Blocage à
     la création **et** modification dans `Admin/EmploiDuTempsController`, avec
     messages affichés en alerte dans le formulaire.
   - **Grille hebdomadaire** : partial `resources/views/partials/edt-grille.blade.php`
     (colonnes par jour, cartes colorées par type, légende), utilisé par les
     vues étudiant et professeur.
   - **Export PDF** : méthodes `pdf()` sur `Etudiant/EmploiDuTempsController` et
     `Professeur/EmploiDuTempsController`, vue `resources/views/etudiant/edt/pdf.blade.php`
     (A4 paysage), boutons « Télécharger PDF », routes `etudiant.edt.pdf` et
     `professeur.edt.pdf`.
   - Vérifié end-to-end : login étudiant → grille (200) → PDF (200, application/pdf).

4. **Module « cours en ligne » (visioconférence Jitsi Meet) — implémenté et
   testé**. Prompt d'origine conservé dans `PROMPT_COURS_EN_LIGNE_JITSI.md`.
   - **Modèle** `App\Models\CoursEnLigne` (table `cours_en_ligne`) : statuts
     `planifie`/`en_cours`/`termine`/`annule`, `room_name` unique non devinable
     (`CoursEnLigne::genererRoomName()`), `lienVisio()`, scopes `pourClasse()` /
     `aVenir()`, `estRejoignable()` (ouverture 15 min avant le début, cf.
     `FENETRE_OUVERTURE_MINUTES`). Rattachable à un créneau EDT (nullable).
   - **Config** : bloc `services.jitsi` (`config/services.php`) + variables
     `JITSI_*` dans `.env`/`.env.example`. Sur `meet.jit.si` aucune clé requise ;
     `app_id`/`jwt_secret` réservés à une future instance JaaS/JWT.
   - **Permissions** ajoutées dans `app/Enums/Role.php` (prof : créer/animer/gérer ;
     étudiant : consulter/rejoindre ; admin : gérer).
   - **Contrôleurs** : `Professeur\CoursEnLigneController` (CRUD + `demarrer`/
     `terminer`/`salle`, prof = propriétaire et modérateur), `Etudiant\…`
     (index classe + `salle` si `estRejoignable`), `Admin\…` (supervision +
     `annuler`). Actions journalisées via `ActivityLogger`.
   - **Routes** : `professeur.cours.*`, `etudiant.cours.*`, `admin.cours-en-ligne.*`.
   - **Vues** : `professeur/cours/{index,create,edit,_form}`, `etudiant/cours/index`,
     `cours/salle` (intégration Jitsi via `external_api.js` en CDN, toolbar
     modérateur vs étudiant, fallback lien externe), `admin/cours-en-ligne/index`.
     Cartes « Cours en ligne » ajoutées au tableau de bord (prof, étudiant, admin).
   - **Seeder** `CoursEnLigneSeeder` (appelé par `DatabaseSeeder`) : 3 séances
     démo Informatique L3 (1 en cours, 1 planifiée, 1 terminée) rattachées au prof.
   - **Tests** : `tests/Feature/CoursEnLigneTest.php` (7 tests verts). Au passage,
     correction de `EmploiDuTempsSalleTest` (payload d'update sans le champ `type`
     devenu obligatoire).
   - **Vérifié** : `migrate --force` + seed OK, `php artisan test` (seul
     `RegistrationTest` échoue — inscription désactivée volontairement), toutes
     les vues compilent (`view:cache`), serveur démarre, route protégée → 302.
   - ⚠️ Hypothèse retenue : « meetgitsi » = **Jitsi Meet**. À reconfirmer si un
     autre service était visé (Zoom, Google Meet, BigBlueButton).

5. **Module Évaluations (rendu en ligne + correction) — implémenté et testé**.
   Étend le module `Projet` existant. Prompt d'origine : `PROMPT_EVALUATIONS_SUP.md`.
   - **Modèle** `App\Models\Soumission` (table `soumissions`, 1 par projet+étudiant) :
     `texte`, `fichier_path`/`fichier_nom`, `rendu_a`, `en_retard`, `note`,
     `commentaire_correction`, `corrige_a`, `corrige_par`. Helpers `estCorrigee()`,
     `statutLabel()`/`statutCouleur()`.
   - **`Projet`** enrichi : `bareme` (défaut 20), `rendu_en_ligne`, `ouverture_at`,
     `fermeture_at`, `copie_unique` ; méthodes `echeance()` (limite souple → retard),
     `accepteRendu()` (ouverture/fermeture dure), `soumissionDe()`. Relation
     `soumissions()`. `Etudiant::soumissions()` ajoutée.
   - **Étudiant** : `Etudiant\ProjetController` → `show` (détail + état de sa copie +
     note/commentaire si corrigée), `soumettre` (upload pdf/doc/docx/zip/image max
     10 Mo + texte, re-soumission tant qu'ouvert sauf copie unique), `telecharger`.
     Stockage privé `storage/app/soumissions`.
   - **Professeur** : `Professeur\ProjetController` → `soumissions` (liste + stats :
     rendus/attendus, retards, corrigées, moyenne), `corriger` (note + commentaire →
     **publie une `Note`** session « Contrôle continu », note ramenée sur 20),
     `telecharger`, `exportCsv`. Validation ownership via `abort_unless`.
   - **Routes** : `etudiant.projets.{show,soumettre,fichier}`,
     `professeur.projets.{soumissions,export,copie.fichier,corriger}`.
   - **Vues** : `etudiant/projets/{index (statut),show}`,
     `professeur/projets/{index (bouton Copies),soumissions,_eval-fields}` (champs
     barème/fenêtre/copie unique dans create+edit).
   - **Permissions** : `rendre_evaluations` (étudiant), `corriger_evaluations` (prof).
   - **Seeder** `SoumissionSeeder` : 2 copies démo (etudiant1 corrigée 15/20,
     etudiant2 en attente) sur un travail Informatique L3.
   - **Tests** : `tests/Feature/EvaluationTest.php` (7 verts). `bareme` rendu
     optionnel (défaut 20) pour ne pas régresser `RappelEcheanceTest`.
   - **Vérifié** : migrate + seed OK, vues rendues avec données réelles, suite
     `php artisan test` au vert (seul `RegistrationTest` échoue — inscription
     désactivée volontairement).

---

## 6. Conventions

- Code et UI **en français** (commentaires, libellés, messages).
- Suivre le style Laravel existant ; formatter avec **Laravel Pint** :
  `vendor/bin/pint`.
- Validations dans les contrôleurs, logique réutilisable sur les models.
- Journaliser les actions admin sensibles via `App\Support\ActivityLogger`.
- Après modif de vues/routes : `php artisan view:clear && php artisan route:clear`.

---

## 7. Pistes / TODO possibles (non encore faits)

Cours en ligne (Jitsi) — **fait** (cf. §5). Améliorations possibles : table de
présence (`participations`) + stats, JWT/JaaS pour salles privées, pastille
« En ligne » sur la grille EDT, rappels de séance avant le début.

- **Évaluations (rendu en ligne + correction)** — **fait** (cf. §5). Reste à
  faire si on veut aller plus loin (cf. `PROMPT_EVALUATIONS_SUP.md`) : grille de
  critères pondérés (`CritereEvaluation`), examen réellement chronométré
  (compte à rebours + `duree_minutes`), notifications de correction et rappels
  automatiques, Policies dédiées, blocage du rendu en situation rouge.

Issues du « prompt emploi du temps » non encore implémentées si on veut aller
plus loin :

- Vue d'occupation des salles (libre/occupée) + suggestion de salle dispo.
- Workflow de demande de changement de créneau par les professeurs.
- Disponibilités des professeurs + charge horaire (alertes sur/sous-charge).
- Mode planification d'examens (contraintes renforcées, surveillances).
- Génération automatique d'emploi du temps sous contraintes.
- Récurrence / créneaux ponctuels, gestion multi-années (archivage).
- Export iCal / synchro calendrier externe.
- Intégrer le contenu du PowerPoint `SOUTENANCE_SIGE_UCAO.pptx` dans la page de
  connexion étudiant (demande en cours, contenu du .pptx à lire d'abord).

---

## 8. Pièges connus

- **Tunnel Cloudflare quick** instable → relancer si erreurs « control stream ».
- **npm en shell non-interactif** → utiliser `/usr/local/bin/npm`.
- **Gemini 503** transitoire → réessayer.
- **`solde` en base non fiable** → toujours `Etudiant::soldeReel()`.
- Ne pas utiliser `perl -0pi` pour des remplacements multi-lignes sur les
  seeders : un motif trop large a corrompu plusieurs `updateOrCreate` (corrigé
  depuis). Préférer des éditions ciblées.
