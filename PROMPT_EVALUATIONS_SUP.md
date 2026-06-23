# PROMPT — Projets, devoirs & examens « modernes » pour l'enseignement supérieur
# Contexte : SIGE UCAO (Laravel 12, PHP 8.2+, SQLite, Blade + Bootstrap 5)

> Colle ce fichier (ou son chemin) dans une session Claude Code pour faire évoluer
> le module d'évaluation. Lis d'abord `HANDOFF.md` pour le contexte global.

## RÔLE
Tu es un ingénieur Laravel senior. Tu fais évoluer le module d'évaluation existant
(`App\Models\Projet` : types projet/devoir/examen ; `Professeur\ProjetController`,
`Etudiant\ProjetController` ; propositions via `PropositionProjet`) en un dispositif
complet de **travaux à rendre et d'examens en ligne**, intégré aux modules Notes,
Emploi du temps et Cours en ligne déjà en place. Lis `HANDOFF.md` d'abord.
Ne casse pas l'existant ; migrations additives uniquement (SQLite).

## OBJECTIF
Permettre, pour un projet / devoir / examen :
1. au **professeur** de définir un travail avec **barème (grille de critères)**,
   pièces jointes (sujet), date limite, et — pour un examen — une **fenêtre
   horaire** et une **durée chronométrée** ;
2. à l'**étudiant** de **rendre en ligne** (upload fichier et/ou texte), avant
   l'échéance, avec accusé de réception et possibilité de re-soumettre tant que
   c'est ouvert ;
3. au **professeur** de **corriger** (note + commentaire + grille remplie) et de
   **publier** les résultats, qui alimentent automatiquement le module **Notes** ;
4. à tous de **suivre l'avancement** (rendus / en retard / corrigés) et de recevoir
   des **rappels** et notifications.

## FONCTIONNALITÉS À LIVRER
### Rendus en ligne (`Soumission`)
- Modèle + migration `soumissions` : `projet_id`, `etudiant_id`, `texte` (nullable),
  `fichier_path` (nullable), `rendu_a`, `en_retard` (bool calculé vs `date_limite`),
  `note` (nullable), `commentaire_correction` (nullable), `corrige_a` (nullable),
  `corrige_par` (FK users). Une soumission par (projet, étudiant) — `updateOrCreate`.
- Upload sécurisé : stockage privé (`storage/app/soumissions`), types autorisés
  (pdf, docx, zip, images), taille max ; téléchargement via route protégée
  (seul l'étudiant propriétaire et le prof du projet).

### Barème / grille de correction (`CritereEvaluation`)
- Critères pondérés rattachés au projet (libellé + points). La somme = note max.
- À la correction, le prof saisit les points par critère → note calculée.

### Examen chronométré & cadrage
- Champs sur `Projet` (examen) : `ouverture_at`, `fermeture_at`, `duree_minutes`.
- L'étudiant ne peut composer qu'entre ouverture et fermeture ; un compte à rebours
  côté client + contrôle serveur ferment la soumission à l'expiration.
- Option « copie unique » : pas de re-soumission pour un examen.

### Workflow & intégrations
- Statuts dérivés : `À venir` / `Ouvert` / `En retard` / `Clôturé` / `Corrigé`.
- **Notes** : à la publication, créer/mettre à jour un `Note` (matière, session,
  professeur) à partir de la note de la soumission — pas de double saisie.
- **Notifications** : rappel J-2 et J-0 avant échéance (réutiliser le mécanisme de
  rappels existant `rappel_envoye`/notifications) ; notification à l'étudiant quand
  sa copie est corrigée.
- **Cohérence assiduité/accès** : un étudiant en situation rouge
  (`Etudiant::enSituationRouge()`) voit l'examen mais l'accès au rendu est bloqué
  avec message explicite.

### Tableau de bord prof (suivi)
- Par travail : nb rendus / attendus, taux de rendu, retards, moyenne, médiane,
  copies non corrigées ; **export CSV** des résultats.

## QUALITÉ, SÉCURITÉ, PERFORMANCE
- Autorisation par **Policy** (`ProjetPolicy`, `SoumissionPolicy`) : prof =
  propriétaire du projet ; étudiant = sa classe (filière+niveau) et sa propre
  soumission ; admin = lecture/supervision.
- Validation dans les contrôleurs ; logique réutilisable sur les models ;
  actions sensibles journalisées via `App\Support\ActivityLogger`.
- Éviter les N+1 (`with`), paginer, indexer `soumissions(projet_id, etudiant_id)`.
- UI 100 % française, accessible (labels, ARIA), responsive Bootstrap.

## TESTS (tests/Feature, style existant)
- Rendu créé avant l'échéance ; marqué `en_retard` après ; re-soumission interdite
  pour un examen « copie unique ».
- Examen : pas de rendu hors fenêtre horaire / après expiration de la durée.
- Correction → publication → `Note` créé avec la bonne valeur.
- Policy : un étudiant ne voit/rend que pour sa classe ; ne lit pas la copie d'un
  autre ; un prof ne corrige que ses projets.
- Téléchargement de fichier protégé (403 si non autorisé).
Lancer `php artisan test` (vert) + `vendor/bin/pint`.

## LIVRABLES & DoD
- [ ] Migrations + modèles (`Soumission`, `CritereEvaluation`) + seed de démo
      (1 devoir avec rendus, 1 examen chronométré, 1 projet corrigé).
- [ ] Rendu en ligne fonctionnel (fichier + texte), accusé de réception.
- [ ] Grille de correction + note + commentaire ; publication → Notes.
- [ ] Examen chronométré opérationnel (fenêtre + durée, contrôle serveur).
- [ ] Suivi prof + export CSV ; rappels et notifications.
- [ ] Policies + tests verts + Pint + `view:clear`/`route:clear`.
- [ ] Vérif live : prof crée → étudiant rend → prof corrige/publie → note visible
      dans le bulletin de l'étudiant.
- [ ] `HANDOFF.md` mis à jour.

## CONTRAINTES
- Étendre l'existant (réutiliser `Projet`, ses TYPES, les vues actuelles) sans le
  régresser. Code et UI en français. Commits/PR seulement si demandé.
