# Prompt — Recherche par suggestions / autocomplétion (typeahead)

> Projet SIGE UCAO (Laravel 12, PHP 8.5, SQLite, Bootstrap 5, Blade, Vite).
> Racine : `/Users/nahoumediouf/classproject/recouvrement-ucao`.

## Objectif
Transformer la recherche globale (rechargement de page, `LIKE %q%`) en un système
d'autocomplétion **robuste et performant** : suggérer en temps réel les étudiants
dès les premières lettres (prénom, nom ou matricule **commençant pareil**) et
trouver un étudiant « en quelques mots » (multi-mots), avec priorité au préfixe.

## Fichiers existants à réutiliser (ne pas réécrire de zéro)
- `app/Http/Controllers/RechercheGlobaleController.php` (logique + RBAC actuels :
  `index()`, `limiterAuxClasses()`, `destinationEtudiant()`).
- `resources/views/layouts/dashboard.blade.php` (barre de recherche ~l.57-60).
- `resources/views/recherche/index.blade.php` (page de résultats complète).
- `routes/web.php` (route `recherche.globale` ~l.85).
- Modèles `App\Models\Etudiant` (matricule, filiere, niveau, relation `user`) et
  `App\Models\User` (nom, prenom, login, email, role) ; enum `App\Enums\Role`.

## Contraintes métier / sécurité — à préserver strictement
1. L'étudiant n'a PAS accès à la recherche (`abort 403`).
2. Le professeur ne voit QUE les étudiants de ses classes : réutiliser
   `limiterAuxClasses()` (ne pas la contourner).
3. Seul l'administrateur peut basculer en `type=personnel`.
4. Requêtes paramétrées (Eloquent) ; échapper les wildcards LIKE (`%` et `_`)
   saisis par l'utilisateur ; valider/borner `q`.

## À implémenter

### A. Endpoint JSON de suggestions
- Route `GET /recherche/suggestions` (`recherche.suggestions`), même middleware/auth
  et MÊME gate RBAC que `recherche.globale`.
- `RechercheGlobaleController@suggestions(Request)` : params `q`, `type`
  (`etudiant|personnel`, `personnel` réservé admin). `q` min 2 car. sinon `[]` (200).
- Retour : ≤ 8 items `{ label, sous_titre, matricule, type, url }`
  (`url` via `destinationEtudiant()`).
- **Factoriser** la construction de requête entre `index()` et `suggestions()`
  (méthode privée commune, pas de duplication).

### B. Multi-mots + priorité au préfixe
- Découper `q` en tokens (espaces) ; AND entre tokens, OR entre champs
  (matricule, nom, prénom ; + filiere, niveau pour la page complète).
- Classement : préfixe (`LIKE 'q%'`) AVANT sous-chaîne (`LIKE '%q%'`) via
  `orderByRaw(CASE WHEN ... LIKE 'token%' THEN 0 ELSE 1)`, puis tri alpha.

### C. Insensibilité accents + casse
- SQLite `LIKE` insensible casse ASCII mais pas aux accents.
- Colonnes normalisées indexées `users.nom_norm`, `users.prenom_norm` remplies à
  l'enregistrement (`Str::ascii` + `mb_strtolower`), recherche sur ces colonnes en
  normalisant aussi `q`. Migration : colonnes + index + **backfill** des données.

### D. Performance
- Index : `etudiants.matricule/filiere/niveau`, `users.nom_norm/prenom_norm`.
- `select()` ciblé, `with('user:id,...')` (anti N+1), `limit` strict (8).

### E. Front-end (barre dashboard.blade.php)
- Combobox accessible + menu déroulant de suggestions, Bootstrap 5 + JS vanilla
  (pas de nouvelle lib), assets via Vite (`resources/js/`).
- Débounce ~200 ms, min 2 car., `AbortController` (annule la requête précédente).
- Affichage : matricule + « Prénom Nom » + filière/niveau, surlignage du préfixe.
- Clavier : ↑/↓, Entrée = aller à l'item, Échap = fermer ; clic = aller.
  Entrée sans sélection = fallback vers la page `/recherche` existante.
- A11y : `role=combobox/listbox/option`, `aria-expanded`,
  `aria-activedescendant`, état vide « Aucun résultat ».

### F. Tests (`tests/Feature/RechercheSuggestionsTest.php`)
1. `suggestions` → 403 pour un étudiant.
2. Un prof ne reçoit QUE les étudiants de ses classes.
3. Préfixe de matricule → bon étudiant en 1er.
4. Multi-mots « prénom nom » → trouve l'étudiant.
5. Accent manquant (« nene » → « Néné ») → fonctionne.
6. `q` < 2 caractères → liste vide.

## Livrable
- Code (controller, route, migration(s), modèle/normalisation, vue layout, asset JS,
  vue résultats si besoin), migrations exécutables (avec backfill), tests verts
  (`php artisan test`), récap des fichiers touchés.

## Exigences
- Ne casse aucune fonctionnalité ni le RBAC. Pas de duplication. Code commenté en
  français, cohérent avec le style du projet.
