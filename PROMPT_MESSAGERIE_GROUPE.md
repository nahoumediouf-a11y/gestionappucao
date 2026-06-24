# Prompt — Messagerie de groupe (diffusion à classes / rôles / multi-destinataires)

> Projet SIGE UCAO (Laravel 12, PHP 8.x, SQLite, Bootstrap 5, Blade).
> Racine : `/Users/nahoumediouf/classproject/recouvrement-ucao`.

## Rôle
Faire évoluer la MESSAGERIE INTERNE (1 expéditeur → 1 destinataire) vers une
messagerie de GROUPE : sélectionner plusieurs étudiants, une/des CLASSES entières
(filière + niveau) ou des RÔLES entiers, dans une interface claire, « rangée » et
performante. Envoi groupé fiable, organisé et sécurisé.

## Fichiers existants à réutiliser (ne pas réécrire de zéro)
- `app/Models/Message.php` (fillable: expediteur_id, destinataire_id, sujet, corps,
  lu_a ; relations expediteur/destinataire ; `scopeNonLusPour`).
- `app/Http/Controllers/MessagerieController.php` (index, envoyes, create, store,
  show, destroy, `destinatairesDisponibles()`).
- `routes/web.php` (groupe `messagerie.*`, ~l.89-94).
- `resources/views/messagerie/{create,index,show}.blade.php`.
- `database/migrations/..._create_messages_table.php` (index [destinataire_id, lu_a]).
- `App\Models\Etudiant` (matricule, filiere, niveau, relation user) ;
  `App\Models\User` (nom, prenom, login, role) ; `App\Enums\Role`.
- `App\Support\Recherche` (normaliser/tokens) + endpoint `recherche.suggestions` :
  RÉUTILISER pour la sélection d'étudiants par recherche (autocomplétion).
- `tests/Feature/MessagerieTest.php` (ne pas casser ses 5 cas).

## Architecture de données (choix imposé : fan-out + diffusion)
- Conserver UN message par destinataire (la table `messages` reste la source de
  l'état lu/non-lu et de la suppression par destinataire — ne pas casser
  `scopeNonLusPour` ni le compteur de non-lus de la topbar).
- Ajouter `diffusion_id` (uuid, nullable, indexée) sur `messages` : tous les
  messages d'un même envoi groupé partagent ce diffusion_id. Migration additive +
  index, pas de backfill destructeur (anciens messages : NULL = envoi individuel).
- Un envoi groupé = N lignes insérées en UNE fois (`Message::insert(...)`, PAS N
  `save()`) avec mêmes expediteur_id, sujet, corps, diffusion_id.

## Sélection des destinataires (cœur « rangé + ultra performant »)
Refondre « Nouveau message » en sélecteur multi-cibles en sections :
1. PAR CLASSE : choisir une/des classes (filière + niveau) → tous les étudiants ;
   afficher l'effectif (« LIG L3 — 28 étudiants »).
2. PAR RÔLE : « Tous les professeurs », « Toute la comptabilité »… (uniquement les
   rôles autorisés à l'expéditeur, cf. RBAC).
3. ÉTUDIANT(S) INDIVIDUEL(S) : recherche avec autocomplétion (réutiliser
   `recherche.suggestions` / `App\Support\Recherche`) → ajout à l'unité.
4. PERSONNEL INDIVIDUEL : idem pour les comptes non-étudiants.

UX : destinataires retenus en « chips » supprimables, COMPTEUR LIVE
(« 34 destinataires »), déduplication auto, Bootstrap 5 + JS vanilla, accessible.
Confirmation si > 20 destinataires. Conserver l'envoi à UN destinataire
(rétrocompatibilité, « Répondre »).

## Côté serveur (sécurité = priorité absolue)
- `store()` accepte des CIBLES, pas une liste d'IDs de confiance : ex. `classes[]`
  (filiere|niveau), `roles[]`, `etudiants[]` (ids), `users[]` (ids).
- Le serveur RÉSOUT les cibles en utilisateurs, puis FILTRE selon le droit de
  l'expéditeur (ne jamais faire confiance au client) :
  - Administrateur : tout le monde.
  - Professeur : uniquement les étudiants de SES classes (logique EmploiDuTemps
    filiere+niveau, cf. `RechercheGlobaleController::limiterAuxClasses`) + personnel.
  - Agent comptable / recouvrement / responsable financier : étudiants + personnel.
  - Étudiant : PAS d'envoi groupé à d'autres étudiants ; au plus le personnel (ou
    désactiver l'envoi groupé pour ce rôle — à expliciter).
  - Exclure l'expéditeur ; comptes actifs uniquement ; DÉDUPLIQUER.
- Validation : ≥ 1 destinataire résolu après filtrage, sujet (max 255), corps
  (max 5000). Cibles interdites ignorées proprement (ne pas fuiter).
- Insertion en masse + chunk (ex. 500), timestamps renseignés.

## Organisation de l'affichage (« plus rangé »)
- « Envoyés » : REGROUPER par diffusion_id → une ligne par envoi groupé
  (« Sujet — 34 destinataires — date »), dépliable ; les envois individuels restent
  une ligne classique (grouper en base, sans N+1).
- « Reçus » : inchangé côté destinataire (message normal). Option : badge
  « Diffusion ».
- Compteur de non-lus (topbar), `show()`/`destroy()` restent par destinataire.

## Performance
- Résolution classes/rôles en requêtes ensemblistes (`whereIn`), pas de boucle par
  étudiant. Eager loading (anti N+1). Pagination conservée. `Message::insert()` en
  masse + chunk. Index sur `diffusion_id`.

## Tests (tests/Feature ; NE PAS casser MessagerieTest)
1. Envoi à une CLASSE → N messages, chaque étudiant de la classe en reçoit 1.
2. Envoi à un RÔLE (tous les professeurs) → chacun reçoit 1.
3. Multi-sélection individuelle + doublons → déduplication.
4. RBAC : prof ne peut pas toucher un étudiant hors de ses classes ; étudiant ne
   peut pas diffuser à d'autres étudiants.
5. « Envoyés » regroupe la diffusion en une ligne (diffusion_id partagé).
6. État lu/non-lu INDÉPENDANT par destinataire.

## Livrable
- Migration additive (diffusion_id + index), Message (relations/scopes diffusion),
  MessagerieController (store groupe + résolution/RBAC + « envoyés » regroupés),
  routes si besoin, vue create refondue (sections + chips + compteur + JS), vue
  « envoyés » groupée, tests verts (`php artisan test`), Pint OK, récap fichiers +
  marche à suivre (migrate).

## Exigences
- Ne casser AUCUNE fonctionnalité existante (envoi individuel, non-lus, show,
  destroy, MessagerieTest). Pas de duplication (réutiliser Recherche + logique
  classes du prof). Code/UI en français, style cohérent. Sécurité d'abord : la
  liste finale des destinataires est TOUJOURS recalculée et filtrée côté serveur.
