# PROMPT — Photos réelles (portraits) des étudiants — SIGE UCAO
# Laravel 12 · Blade · Bootstrap 5 · stockage public · design system existant

## OBJECTIF
Photo de profil **réelle** (portrait professionnel d'une vraie personne) pour
chaque étudiant, partout où l'identité apparaît : upload (admin + étudiant), jeu de
démo de **vraies personnes** (PAS d'IA), repli sur l'avatar à initiales.

> EXIGENCE STRICTE : uniquement des **photographies réelles de personnes** issues
> de banques libres (randomuser.me / Pexels / Unsplash). JAMAIS d'images générées
> par IA. Portraits neutres, professionnels.

## DÉCISIONS RETENUES
- Source démo : **randomuser.me** (vraies photos, libres, idempotent).
- Champ `photo` sur **users** (avatar général ; on ne seede que les étudiants).
- **Téléchargement au seed** dans `storage/app/public/photos` (affichage fiable).

## DONNÉES
- Migration additive `users.photo` (string nullable, chemin sur disque `public`).
- `User::photoUrl(): ?string` (URL publique si photo, sinon null).

## UPLOAD (sécurisé)
- Étudiant : « Mon compte » (`CompteController`). Admin : formulaire utilisateur.
- Validation `image|mimes:jpg,jpeg,png,webp|max:2048`. Stockage `photos` (public).
  Suppression de l'ancienne au remplacement ; option « retirer la photo ».
- Helper réutilisable `App\Support\PhotoUtilisateur`.

## SEED
- `PhotosEtudiantsSeeder` : télécharge un portrait réel par étudiant (index
  déterministe homme/femme), idempotent, **tolérant hors-ligne** (si échec → null).

## AFFICHAGE
- `partials/_identite.blade.php` : `<img>` ronde (object-fit:cover, alt=nom,
  loading=lazy) si `photoUrl()`, sinon initiales. Répercuté partout où `_identite`
  est utilisé + topbar (profil), dashboard étudiant, fiche classe, bulletin, recherche.

## SÉCURITÉ / PERF
- Étudiant ne change que sa photo ; admin toutes. Validation serveur. Pas de N+1
  (photo sur users déjà chargé). `storage:link`. Lazy-loading, dimensions fixes.

## TESTS
- Upload image → `users.photo` rempli (Storage::fake) ; non-image refusé ;
  étudiant ne change pas la photo d'un autre ; remplacement supprime l'ancien
  fichier ; `_identite` affiche `<img>` ou initiales. `php artisan test` + Pint.

## LIVRABLES & DoD
- [ ] Migration `users.photo` + `photoUrl()` + helper.
- [ ] Upload (Mon compte + admin), validation, suppression ancienne.
- [ ] `PhotosEtudiantsSeeder` (vraies photos, idempotent, tolérant hors-ligne).
- [ ] `_identite` + points d'affichage avec repli initiales.
- [ ] Tests verts, Pint, `storage:link`, HANDOFF mis à jour.

## CONTRAINTE
- Aucune image IA — photos réelles, source libre documentée. UI française.
