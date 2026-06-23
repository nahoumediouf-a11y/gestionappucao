# PROMPT — Upload fiable de la photo de profil — SIGE UCAO
# Laravel 12 · disque `public` · helper App\Support\PhotoUtilisateur (existant)

## CONTEXTE
La photo de profil existe (`users.photo`, `User::photoUrl()`, helper
`App\Support\PhotoUtilisateur::appliquer()`, upload via « Mon compte » et formulaire
admin, repli initiales dans `partials/_identite`). Objectif : **fiabiliser** cet
upload pour qu'il ne casse jamais et donne des messages clairs.

## PROBLÈMES COUVERTS
1. **Limites PHP** (`upload_max_filesize`/`post_max_size`) : POST vide / 413 →
   message « Fichier trop volumineux » (gérer `PostTooLargeException`). Doc php.ini.
2. **MIME usurpé** : valider `image`+`mimes` ET vérifier le MIME réel ; extension
   dérivée du MIME, **nom de fichier aléatoire** basé sur l'id (pas le nom client).
3. **Dimensions** : `dimensions:min_width=100,min_height=100`.
4. **EXIF / orientation** : corriger si lib dispo, sinon documenter (dégradation).
5. **HEIC iPhone** : refus clair (« convertissez en JPG/PNG »).
6. **Fichiers orphelins** : suppression de l'ancien au remplacement/retrait ;
   suppression de la photo à la suppression de l'utilisateur (hook `deleting`).
7. **`storage:link` manquant** : doc/automatisation ; `photoUrl()` robuste.
8. **Image cassée** : `<img onerror>` → repli sur l'avatar à initiales.
9. **Droits d'écriture** : try/catch storage, message clair, pas de crash.
10. **UX** : aperçu, états, toasts, `accept="image/*"`, `aria`.
11. **Accès** : étudiant = sa photo seulement ; admin toutes ; chemin basé sur l'id.

## IMPLÉMENTATION
- `PhotoUtilisateur` durci : règles réutilisables `regles()`, `appliquer()` qui
  vérifie le MIME réel, nomme aléatoirement (extension réelle), supprime l'ancien,
  try/catch storage, lève une `ValidationException` exploitable en cas de souci.
- Règle photo partagée entre `CompteController` et `Admin\UserController`.
- Hook `User::deleting` → suppression du fichier photo.
- `partials/_identite` : `<img onerror>` bascule sur initiales.
- `PostTooLargeException` mappée (message FR) dans `bootstrap/app.php`.
- Doc : `storage:link`, `php.ini` (upload_max_filesize/post_max_size), droits `storage/`.

## TESTS
- Upload valide (jpg/png/webp) stocké ; refus .pdf, .php→.jpg (MIME réel),
  image trop petite, > 2 Mo ; remplacement/retrait/suppression user nettoient le
  fichier ; étudiant ne modifie pas la photo d'un autre. `php artisan test` + Pint.

## LIVRABLES & DoD
- [ ] `PhotoUtilisateur` durci + règle partagée + hook suppression.
- [ ] `<img onerror>` repli ; `PostTooLargeException` gérée ; doc déploiement.
- [ ] Tests verts (cas d'échec couverts) ; Pint ; HANDOFF mis à jour.

## CONTRAINTE
- Ne pas casser l'upload existant ni `_identite`. Intervention Image **optionnelle**
  (dégradation gracieuse). UI française.
