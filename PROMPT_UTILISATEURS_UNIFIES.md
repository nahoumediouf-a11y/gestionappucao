# PROMPT — Socle utilisateur unifié (SIGE UCAO)
# Stack : Laravel 12, Blade, Bootstrap 5, design system existant dans layouts/app

> Lis `HANDOFF.md`. Aujourd'hui la gestion des comptes est éclatée : l'admin gère
> les « utilisateurs » (`Admin\UserController`, resource), les étudiants ont un
> profil dédié (`Etudiant\ProfilController`), le personnel n'a qu'un changement de
> mot de passe (`ProfileController`), et les données étudiant vivent dans
> `Etudiant`. On UNIFIE tout ça SANS casser l'auth, les rôles, le captcha ni les
> données. Réutiliser le design system (`.ucao-*`, `layouts/dashboard`).

## RÔLE
Tu es ingénieur Laravel senior. Tu crées un **socle utilisateur unifié** : une
seule façon cohérente de gérer et de présenter TOUS les utilisateurs (étudiants
et personnel), quel que soit leur rôle, tout en conservant les spécificités
métier (un étudiant a une fiche `Etudiant`).

## OBJECTIF
1. **Profil unifié (self-service)** : une page « Mon compte » identique pour TOUS
   les rôles (consulter/éditer ses infos : nom, prénom, email, téléphone ;
   changer son mot de passe), avec un encart supplémentaire propre à l'étudiant
   (matricule, filière, niveau, contact d'urgence — déjà géré par
   `Etudiant\ProfilController`). Un seul contrôleur + une seule vue paramétrée par
   le rôle, accessibles à tout utilisateur connecté.
2. **Gestion administrateur unifiée** : `Admin\UserController` gère TOUS les rôles
   dans une seule interface, y compris la création d'un **étudiant** (le formulaire
   révèle les champs `Etudiant` quand le rôle = Étudiant et crée la fiche liée),
   avec filtres par rôle/statut et recherche. Conserver l'activation
   (`activer`) et le cycle de statut (`actif` / `en_attente` / `inactif`).
3. **Identité cohérente** : un seul « bloc identité » (avatar à initiales, nom,
   rôle via `Role::label()`, statut) réutilisé dans la barre de navigation, la
   liste admin et la page profil — un partial unique, pas de duplication.

## IMPLÉMENTATION ATTENDUE
- **Profil** : `App\Http\Controllers\CompteController` (ou regroupe l'existant)
  avec `show`/`update`/`updatePassword`. Route commune `GET/PUT /mon-compte`
  pour tous les rôles authentifiés. La vue inclut un partial `partials/_identite`
  et, si `auth()->user()->etudiant`, le bloc étudiant (réutiliser la logique de
  `Etudiant\ProfilController`, ne pas la dupliquer). Garder l'ancien
  `ProfileController` (mot de passe) ou le fusionner proprement.
- **Admin** : enrichir `Admin\UserController` (create/store/edit/update) pour
  gérer le rôle (enum `Role`) + les champs `Etudiant` conditionnels (matricule,
  filière, niveau…). `index` : filtres `role` et `statut`, recherche par
  nom/login/email, pagination, sans N+1 (`with('etudiant')`). Journaliser via
  `ActivityLogger`.
- **Partial identité** : `partials/_identite.blade.php` (avatar initiales +
  nom + badge rôle + badge statut), utilisé partout.
- **Sécurité** : un utilisateur n'édite que SON compte ; seul l'admin gère les
  autres. Validation dans les contrôleurs (email unique sauf soi-même, login
  unique, mot de passe confirmé). Rôles/permissions via `Role` et
  `User::hasPermission()`. Aucune élévation de privilège possible côté self-service.
- **Cohérence données** : créer un étudiant via l'admin crée User + Etudiant de
  façon atomique (transaction). Supprimer/désactiver gère proprement la fiche liée.

## QUALITÉ / PERF / ACCESSIBILITÉ
- Réutiliser le design system ; aucun CSS dupliqué. UI 100 % française.
- Pas de N+1 (eager loading), pagination des listes, requêtes filtrées en SQL.
- Accessible (labels, aria), responsive.

## TESTS (tests/Feature)
- Tout rôle accède à `/mon-compte` (200) et met à jour ses infos ; ne peut pas
  modifier le compte d'un autre.
- L'admin crée un étudiant → User + Etudiant créés et liés (transaction).
- L'admin filtre par rôle/statut ; un non-admin n'accède pas à la gestion (403).
- Email/login uniques validés ; changement de mot de passe fonctionne ; `AuthTest`
  reste vert.
Lancer `php artisan test` (vert hors `RegistrationTest` désactivé) + `vendor/bin/pint`.

## LIVRABLES & DoD
- [ ] Page « Mon compte » unique pour tous les rôles (+ encart étudiant).
- [ ] Admin : CRUD unifié de tous les rôles, création d'étudiant intégrée, filtres.
- [ ] Partial identité réutilisé (navbar, liste admin, profil).
- [ ] Sécurité (self vs admin), transactions, tests verts, Pint, `view:clear`.
- [ ] `HANDOFF.md` mis à jour.

## CONTRAINTES
- Étendre/fusionner l'existant (`Admin\UserController`, `Etudiant\ProfilController`,
  `ProfileController`) plutôt que réécrire. Ne casse pas les routes/menus actuels
  (garder des redirections si tu renommes une route). Commits/PR seulement si on
  te le demande.
