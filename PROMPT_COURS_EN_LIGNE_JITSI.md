# PROMPT — Intégration Jitsi Meet (cours en ligne) dans SIGE UCAO

> Colle ce fichier (ou son chemin) dans une session Claude Code pour implémenter
> la fonctionnalité « cours en ligne » par visioconférence. Lis d'abord
> `HANDOFF.md` pour le contexte global du projet.

---

## 0. Contexte projet (rappel)

**SIGE UCAO** — application Laravel 12 de gestion académique/financière pour
l'UCAO Saint Michel (projet de soutenance). Stack : Laravel 12, PHP 8.2+, SQLite,
Blade + Bootstrap 5 (Bootstrap Icons), Tailwind v4 (Vite), DomPDF, Chart.js (CDN).

Rôles (`app/Enums/Role.php`) : `administrateur`, `agent_comptable`,
`agent_recouvrement`, `responsable_financier`, `etudiant`, `professeur`.
Routing par rôle dans `routes/web.php` (middleware `role:xxx`, préfixes
`admin/`, `professeur/`, `etudiant/`…). Le module **Emploi du temps** existe déjà :
model `App\Models\EmploiDuTemps` (table `emplois_du_temps`, champs `filiere`,
`niveau`, `jour`, `heure_debut`, `heure_fin`, `matiere`, `type` [CM/TD/TP/Examen],
`salle`, `professeur_id`), avec détection de conflits et grille hebdomadaire
(`resources/views/partials/edt-grille.blade.php`).

**Conventions impératives** :
- Tout le code et l'UI **en français** (libellés, commentaires, messages flash).
- Style Laravel existant ; formatter avec `vendor/bin/pint` à la fin.
- Validations dans les contrôleurs, logique réutilisable sur les models.
- Journaliser les actions admin sensibles via `App\Support\ActivityLogger`.
- Après modif vues/routes : `php artisan view:clear && php artisan route:clear`.
- SQLite : ne **jamais** modifier une migration déjà jouée ; en créer une nouvelle.

---

## 1. Objectif

Permettre de **tenir des cours en visioconférence** directement depuis
l'application, en s'appuyant sur **Jitsi Meet** (open-source, gratuit, aucune
clé API requise pour les salles publiques sur `meet.jit.si`, intégré via
l'**Iframe / External API** chargée en CDN : `https://meet.jit.si/external_api.js`).

Cas d'usage cible :
1. Un **professeur** (ou un **admin**) planifie une *séance en ligne* rattachée à
   un cours de l'emploi du temps (matière + filière + niveau + créneau).
2. À l'heure dite, le **professeur rejoint en modérateur** et les **étudiants de
   la classe concernée** rejoignent la salle depuis leur espace.
3. Une séance a un **statut** (`planifie` → `en_cours` → `termine` / `annule`) et
   un lien d'invitation stable.

> Choix techno assumé : **Jitsi Meet via Iframe API sur l'instance publique
> `meet.jit.si`**. Pas de backend média à héberger, parfait pour une démo /
> soutenance. Prévoir une **config centralisée** (`config/services.php` +
> `.env`) pour pouvoir basculer plus tard vers une instance auto-hébergée ou
> **JaaS (8x8)** avec JWT, sans réécrire le code applicatif.

---

## 2. Modèle de données

Créer un model + migration **`CoursEnLigne`** (table `cours_en_ligne`) :

| Champ | Type | Notes |
|-------|------|-------|
| `id` | bigint PK | |
| `emploi_du_temps_id` | FK nullable → `emplois_du_temps` | rattache à un créneau existant (optionnel : une séance peut être ponctuelle) |
| `professeur_id` | FK → `users` | l'animateur (modérateur) |
| `titre` | string | ex. « Algorithmique — Chap. 3 » |
| `description` | text nullable | ordre du jour / consignes |
| `filiere` | string | pour cibler les étudiants |
| `niveau` | string | idem (ex. L1, M2) |
| `room_name` | string unique | nom de salle Jitsi **non devinable** (slug + token aléatoire) |
| `debut_prevu` | datetime | |
| `fin_prevue` | datetime nullable | |
| `statut` | string | `planifie` / `en_cours` / `termine` / `annule` |
| `demarre_a` | datetime nullable | rempli quand le prof lance la séance |
| `termine_a` | datetime nullable | |
| timestamps | | |

Sur le model `CoursEnLigne` :
- Constante `STATUTS` (label + couleur Bootstrap, comme `EmploiDuTemps::TYPES`).
- Relations : `professeur()` (BelongsTo User), `emploiDuTemps()` (BelongsTo).
- `roomName()` : génère un nom unique à la création — préfixe lisible +
  hash aléatoire (`Str::slug($titre).'-'.Str::random(10)`), **jamais** un nom
  trivial (sécurité : sur `meet.jit.si` toute personne connaissant le nom de
  salle peut entrer).
- `lienVisio()` : `https://meet.jit.si/{room_name}` (URL absolue depuis config).
- Helpers `statutLabel()` / `statutCouleur()`.
- Scope `pourClasse($filiere, $niveau)` et scope `aVenir()`.
- Accessor `estRejoignable()` : vrai si `statut` ∈ {planifie (à ± X min du
  début), en_cours}. Définir une fenêtre (ex. ouverture 15 min avant `debut_prevu`).

> **Optionnel mais recommandé** : table pivot `cours_en_ligne_participations`
> (`cours_en_ligne_id`, `user_id`, `rejoint_a`) pour tracer la présence des
> étudiants — utile pour une stat « taux de présence » et cohérent avec le
> module Absences existant. Implémenter seulement si le temps le permet.

Fournir un **seeder** (`CoursEnLigneSeeder`, appelé depuis `DatabaseSeeder`)
créant 3–4 séances de démo (1 `en_cours`, 1 `planifie`, 1 `termine`) rattachées
à des créneaux et profs existants, en `updateOrCreate` (idempotent).

---

## 3. Configuration

Dans `config/services.php`, ajouter un bloc `jitsi` :

```php
'jitsi' => [
    'domain'   => env('JITSI_DOMAIN', 'meet.jit.si'),
    'base_url' => env('JITSI_BASE_URL', 'https://meet.jit.si'),
    // Réservé à une future instance JaaS/self-hosted avec JWT (laisser vide) :
    'app_id'      => env('JITSI_APP_ID'),
    'jwt_secret'  => env('JITSI_JWT_SECRET'),
],
```

Ajouter les variables (vides par défaut) dans `.env` et `.env.example`.
Documenter en commentaire que sur `meet.jit.si` aucune clé n'est nécessaire.

---

## 4. Autorisations & rôles

Mettre à jour `app/Enums/Role.php` (méthode `permissions()`) :
- **Professeur** : ajouter `creer_cours_en_ligne`, `animer_cours_en_ligne`,
  `gerer_cours_en_ligne`.
- **Étudiant** : ajouter `rejoindre_cours_en_ligne`, `consulter_cours_en_ligne`.
- **Administrateur** : ajouter `gerer_cours_en_ligne` (supervision globale).

Règles d'accès (à appliquer dans les contrôleurs/policies) :
- Un professeur ne gère/anime **que ses propres** séances (`professeur_id`).
- Un étudiant ne voit/rejoint **que** les séances de **sa filière + son niveau**.
- L'admin voit et gère tout.
- Le **modérateur** Jitsi = le professeur (paramètre `userInfo` + on **ne**
  partage pas le bouton modérateur aux étudiants ; via `configOverwrite` /
  `interfaceConfigOverwrite` on restreint les contrôles côté étudiant).

---

## 5. Routes (`routes/web.php`)

**Professeur** (groupe `role:professeur`, préfixe `professeur/`, name `professeur.`) :
```
GET    cours-en-ligne                 -> index   (cours.index)
GET    cours-en-ligne/creer           -> create  (cours.create)
POST   cours-en-ligne                 -> store   (cours.store)
GET    cours-en-ligne/{cours}/modifier-> edit    (cours.edit)
PUT    cours-en-ligne/{cours}         -> update  (cours.update)
DELETE cours-en-ligne/{cours}         -> destroy (cours.destroy)
POST   cours-en-ligne/{cours}/demarrer-> demarrer(cours.demarrer)  // statut en_cours
POST   cours-en-ligne/{cours}/terminer-> terminer(cours.terminer)  // statut termine
GET    cours-en-ligne/{cours}/salle   -> salle   (cours.salle)     // page visio modérateur
```

**Étudiant** (groupe `role:etudiant`, préfixe `etudiant/`, name `etudiant.`) :
```
GET  cours-en-ligne               -> index (cours.index)   // séances de sa classe
GET  cours-en-ligne/{cours}/salle -> salle (cours.salle)   // rejoint si estRejoignable
```

**Admin** (groupe `role:administrateur`, préfixe `admin/`) : un `index` de
supervision (lecture + annulation), name `admin.cours-en-ligne.*`.

Utiliser le **route-model binding** (`{cours}`) + vérification d'appartenance.

---

## 6. Contrôleurs

Créer :
- `app/Http/Controllers/Professeur/CoursEnLigneController.php`
- `app/Http/Controllers/Etudiant/CoursEnLigneController.php`
- `app/Http/Controllers/Admin/CoursEnLigneController.php`

Points clés :
- **Validation** dans les contrôleurs (titre requis, `debut_prevu` >= maintenant
  à la création, `fin_prevue` > `debut_prevu`, filière/niveau valides, etc.).
- À la création : générer `room_name` via le model, statut `planifie`.
- `demarrer()` : passe `statut=en_cours`, set `demarre_a`. `terminer()` : `termine`.
- `salle()` :
  - Vérifie l'autorisation (prof propriétaire OU étudiant de la classe).
  - Vérifie `estRejoignable()` côté étudiant (sinon redirige avec message).
  - Passe à la vue : `room_name`, `domain`, infos utilisateur (nom, email),
    et un booléen `$estModerateur`.
- Journaliser création/démarrage/annulation côté prof et admin via
  `ActivityLogger`.
- Messages flash en français.

---

## 7. Vues (Blade)

Layout connecté : `layouts/dashboard`. Créer sous `resources/views/` :

- `professeur/cours/index.blade.php` — liste des séances du prof (badge statut
  coloré, boutons Démarrer / Rejoindre / Terminer / Modifier / Supprimer).
- `professeur/cours/create.blade.php` & `edit.blade.php` — formulaire (titre,
  description, rattachement à un créneau EDT optionnel via `<select>`, filière,
  niveau, début/fin). Réutiliser le style des formulaires EDT existants.
- `etudiant/cours/index.blade.php` — séances de la classe de l'étudiant
  (à venir / en cours mises en avant ; bouton « Rejoindre » actif seulement si
  `estRejoignable()`).
- `cours/salle.blade.php` (partagée prof/étudiant) — page plein écran intégrant
  Jitsi.
- `admin/cours-en-ligne/index.blade.php` — supervision.

**Intégration Jitsi dans `salle.blade.php`** (Iframe API, chargée en CDN) :

```blade
@push('scripts')
<script src="https://{{ $domain }}/external_api.js"></script>
<script>
  const api = new JitsiMeetExternalAPI(@json($domain), {
    roomName: @json($cours->room_name),
    parentNode: document.querySelector('#jitsi-container'),
    width: '100%', height: 600,
    userInfo: { displayName: @json($displayName), email: @json($email) },
    configOverwrite: {
      prejoinPageEnabled: true,
      startWithAudioMuted: {{ $estModerateur ? 'false' : 'true' }},
      startWithVideoMuted: {{ $estModerateur ? 'false' : 'true' }},
    },
    interfaceConfigOverwrite: {
      // côté étudiant : interface allégée
      TOOLBAR_BUTTONS: @json($estModerateur ? $boutonsModerateur : $boutonsEtudiant),
    },
  });
  api.addEventListener('readyToClose', () => { window.location = @json($retourUrl); });
</script>
@endpush
```

- `#jitsi-container` = conteneur responsive dans la vue.
- Définir deux jeux de `TOOLBAR_BUTTONS` (modérateur vs étudiant) côté
  contrôleur ou vue.
- Bouton « Quitter » qui revient à l'index du rôle.
- Prévoir un fallback : lien direct `{{ $cours->lienVisio() }}` (« Ouvrir dans
  un nouvel onglet ») au cas où l'iframe est bloquée.

**Navigation** : ajouter une entrée « Cours en ligne » dans le menu latéral
(`resources/views/partials/` — repérer le partial de menu du dashboard) pour les
rôles professeur et étudiant (et admin).

---

## 8. Lien avec l'emploi du temps (bonus cohérence)

- Sur la grille EDT (`partials/edt-grille.blade.php`) et/ou la liste, si un
  créneau a une séance `CoursEnLigne` à venir/en cours, afficher une **pastille
  « 🟢 En ligne »** avec lien direct vers la salle. (Optionnel mais valorisant
  pour la démo.)
- Le `<select>` de rattachement au formulaire de création doit lister les
  créneaux du professeur connecté.

---

## 9. Tests (`tests/Feature/`)

Écrire des tests Feature (suivre le style de `RoleAccess`, `EmploiDuTempsSalle`) :
- `CoursEnLigneAccessTest` : un étudiant d'une autre classe **ne peut pas**
  rejoindre (403/redirect) ; l'étudiant de la bonne classe accède quand
  `estRejoignable`.
- Un prof ne peut pas gérer la séance d'un autre prof.
- `store` crée bien la séance avec un `room_name` unique et statut `planifie`.
- `demarrer`/`terminer` changent le statut.
- `estRejoignable` respecte la fenêtre temporelle (planifie hors fenêtre → non).

Lancer : `php artisan test`. Puis `vendor/bin/pint`.

---

## 10. Critères d'acceptation (definition of done)

- [ ] Migration + model `CoursEnLigne` + seeder idempotent ; `migrate:fresh --seed` OK.
- [ ] Un professeur crée, démarre, anime puis termine une séance ; il est modérateur.
- [ ] Les étudiants de la classe voient la séance et la rejoignent dans la fenêtre ;
      les autres n'y ont pas accès.
- [ ] La visio Jitsi se charge dans l'app (iframe) + fallback lien externe.
- [ ] Statuts colorés, messages flash FR, menu mis à jour, actions admin journalisées.
- [ ] Tests verts (`php artisan test`), code formaté (`vendor/bin/pint`), aucun
      warning de route/vue (`view:clear`, `route:clear`).
- [ ] Vérification live : `php artisan serve` → login `prof` → créer/démarrer →
      login `etudiant1` (classe correspondante) → rejoindre.

---

## 11. Pièges connus / garde-fous

- **Sécurité des salles `meet.jit.si`** : pas d'auth serveur → le seul rempart
  est un `room_name` long et imprévisible. Ne jamais exposer un nom devinable.
  Mentionner dans la doc que pour un usage réel il faudrait JaaS/JWT (bloc config
  déjà prévu).
- **HTTPS requis** pour caméra/micro : OK en local sur `127.0.0.1` ; via le
  tunnel Cloudflare (cf. HANDOFF) c'est en HTTPS donc bon pour la démo publique.
- **npm en shell non-interactif** → `/usr/local/bin/npm` (cf. HANDOFF). Mais ici
  l'intégration est en CDN, donc **pas besoin de rebuild d'assets**.
- **SQLite** : nouvelle migration, ne pas éditer l'existante.
- Ne pas casser la détection de conflits EDT existante.
- `view:clear` + `route:clear` après ajout de routes/vues.

---

## 12. Ordre d'implémentation suggéré

1. Config (`services.php`, `.env`) + enum `Role` (permissions).
2. Migration + model `CoursEnLigne` (+ pivot participations si retenu).
3. Seeder + `migrate:fresh --seed`, vérifier en tinker.
4. Routes + contrôleurs (prof, étudiant, admin).
5. Vues (index prof, formulaires, index étudiant, **salle Jitsi**, admin).
6. Menu latéral + pastille EDT.
7. Tests Feature + Pint.
8. Vérification live de bout en bout (prof crée → étudiant rejoint).
