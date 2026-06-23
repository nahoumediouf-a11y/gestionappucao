# PROMPT — Recherche globale : redirection & filtrage (SIGE UCAO)
# Stack : Laravel 12, Blade, Bootstrap 5, design system existant

> Lis `HANDOFF.md`. La barre de recherche de la barre supérieure
> (`layouts/dashboard`) doit fonctionner « normalement » : saisir un terme →
> page de résultats claire → cliquer un résultat → redirection vers la bonne
> destination selon le rôle. Réutiliser l'existant (recherches admin/recouvrement).

## OBJECTIF
Une **recherche globale unique** (`GET /recherche`, `recherche.globale`)
accessible à tous les rôles **du personnel** (admin, professeur, comptable,
recouvrement, financier ; pas l'étudiant pour des raisons de confidentialité).
Comportement standard et prévisible :
1. **Filtrage** : recherche sur les étudiants par matricule, nom, prénom, filière,
   niveau (insensible à la casse, partiel). Pour l'admin, un **filtre `type`**
   permet aussi de chercher dans le personnel (nom/login/email/rôle). Le
   professeur ne voit que les étudiants de **ses classes**.
2. **Redirection contextuelle** : chaque résultat étudiant pointe vers la
   destination utile au rôle connecté :
   - Administrateur → fiche utilisateur (édition)
   - Professeur → fiche de la classe de l'étudiant
   - Agent comptable → enregistrement d'un paiement / débiteurs
   - Agent de recouvrement → impayés / engagements
   - Responsable financier → paiements
3. **États** : si la requête est vide, inviter à saisir un terme ; si aucun
   résultat, message clair. Pagination si nécessaire.

## IMPLÉMENTATION
- `App\Http\Controllers\RechercheGlobaleController@index` : lit `q` et `type`,
  applique le filtrage (sans N+1, `with('user')`), construit les résultats et la
  destination par rôle (helper interne `destinationPour($role, $etudiant)`).
- Route `GET /recherche` (groupe `auth`), nommée `recherche.globale`.
- **Barre supérieure** (`layouts/dashboard`) : pour tout rôle ≠ étudiant, la
  recherche pointe vers `recherche.globale` (méthode GET, champ `q`). Retirer le
  branchement spécifique admin/recouvrement (unifié).
- Vue `recherche/index.blade.php` : champ de recherche pré-rempli, filtres
  (type pour l'admin), liste de résultats avec nom + matricule + classe + bouton
  d'action contextuel. Réutiliser le composant `<x-tri>` / le style des cartes si
  pertinent. UI française, responsive, accessible.
- Les pages existantes `admin.recherche.index` et `recouvrement.recherche.index`
  peuvent rester (ne pas casser leurs routes) ou rediriger vers la globale.

## SÉCURITÉ
- Accessible aux rôles personnel uniquement (étudiant exclu).
- Le professeur ne doit voir que les étudiants de ses filières/niveaux
  (réutiliser la logique `InteractsWithEtudiants`/`EmploiDuTemps`).
- Échapper les termes ; pas d'injection (requêtes paramétrées Eloquent).

## TESTS (tests/Feature)
- `/recherche?q=...` renvoie 200 et liste les étudiants correspondants pour un
  rôle personnel ; un étudiant connecté reçoit 403 (ou pas de barre).
- Le professeur ne voit pas les étudiants hors de ses classes.
- L'admin avec `type=personnel` trouve un utilisateur du personnel.
- Requête vide → page 200 sans résultat (message d'invite).
Lancer `php artisan test` (vert hors `RegistrationTest`) + `vendor/bin/pint`.

## LIVRABLES & DoD
- [ ] Recherche globale unique + redirection contextuelle par rôle.
- [ ] Barre supérieure unifiée vers `recherche.globale` (hors étudiant).
- [ ] Filtrage étudiants (+ personnel pour l'admin), périmètre prof restreint.
- [ ] Tests verts, Pint, `view:clear`. `HANDOFF.md` mis à jour.

## CONTRAINTES
- Réutiliser le design system et l'existant. UI française. Commits/PR si demandé.
