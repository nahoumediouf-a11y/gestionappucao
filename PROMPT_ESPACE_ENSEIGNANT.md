# PROMPT — Espace enseignant performant pour SIGE UCAO
# Contexte : Laravel 12, PHP 8.2+, SQLite, Blade + Bootstrap 5

> Colle ce fichier (ou son chemin) dans une session Claude Code. Lis d'abord
> `HANDOFF.md` pour le contexte global du projet.

## RÔLE
Tu es un ingénieur Laravel senior. Tu fais évoluer l'**espace professeur** de
SIGE UCAO en un véritable poste de travail pédagogique, en t'appuyant sur les
modules déjà en place (lis `HANDOFF.md`) :
- Emploi du temps (`EmploiDuTemps`), Cours en ligne (`CoursEnLigne`),
- Notes (`Note`), Absences (`Absence`),
- Projets / devoirs / examens (`Projet`) avec rendu/correction (`Soumission`),
- Documents de cours (`DocumentCours`), propositions d'étudiants.
Tu n'ajoutes pas un module isolé : tu **relies** l'existant et tu combles les
manques de pilotage. Ne casse rien ; migrations additives uniquement (SQLite).

## OBJECTIF
Donner au professeur, en un coup d'œil et en peu de clics :
1. une **vue d'ensemble de sa journée et de sa charge** (prochaines séances EDT +
   cours en ligne, copies à corriger, échéances proches) ;
2. un **carnet de notes** par classe/matière (tableau étudiants × évaluations,
   moyennes, saisie/édition rapide) ;
3. un **suivi de classe** exploitable (moyenne, taux de présence, taux de rendu,
   **étudiants à risque**) ;
4. des **actions rapides** contextualisées depuis ses classes.

## FONCTIONNALITÉS À LIVRER
### 1. Tableau de bord enseignant (`professeur.espace`)
- « Aujourd'hui » : séances EDT du jour + cours en ligne `en_cours`/`planifie`
  proches, avec accès direct (rejoindre / démarrer).
- « À traiter » : nombre de **copies non corrigées** (`Soumission` sans `corrige_a`),
  **échéances** de travaux à venir, propositions d'étudiants en attente.
- « Mes classes » : liste dédupliquée des couples filière+niveau enseignés
  (déduits de `EmploiDuTemps` du prof), chacune cliquable vers sa fiche classe.

### 2. Fiche classe (`professeur.classes.show` : filiere + niveau)
- Effectif, liste des étudiants (réutiliser `InteractsWithEtudiants`).
- **Indicateurs** : moyenne de classe (sur les `Note` de ses matières), taux de
  présence (via `Absence`), taux de rendu des derniers travaux.
- **Étudiants à risque** : surligner ceux en situation rouge
  (`Etudiant::enSituationRouge()`) ou en dessous de la moyenne.
- Boutons d'action : saisir une note, marquer une absence, planifier un cours en
  ligne, assigner un devoir, publier un document — pré-remplis pour cette classe.

### 3. Carnet de notes (`professeur.carnet` : par classe/matière)
- Tableau **étudiants en lignes × évaluations/sessions en colonnes**, moyenne par
  étudiant et par colonne, code couleur (<10 rouge).
- **Saisie/édition rapide** en place (inline) avec enregistrement par requête
  légère (validation 0–20). Réutilise/complète `Professeur\NoteController`.
- Export CSV du carnet.

## QUALITÉ, SÉCURITÉ, PERFORMANCE
- **Sécurité** : un prof n'accède qu'aux classes qu'il enseigne et à ses propres
  données (vérif via ses créneaux EDT / `professeur_id`). `abort_unless`/Policy.
- **Performance** : éliminer les N+1 (`with`, `withCount`, agrégations SQL plutôt
  que boucles PHP), paginer les longues listes, mémoïser les « mes classes ».
- Validation dans les contrôleurs, logique réutilisable sur les models, actions
  sensibles journalisées via `ActivityLogger`.
- UI 100 % française, responsive Bootstrap, cartes/tableaux cohérents avec
  l'existant (`layouts.dashboard`).

## TESTS (tests/Feature, style existant)
- Le tableau de bord enseignant affiche les bons compteurs (copies à corriger,
  séances du jour) pour le prof connecté, et 200.
- Un prof n'accède pas à la fiche d'une classe qu'il n'enseigne pas (403).
- Saisie rapide d'une note : crée/maj la `Note`, refuse une valeur hors 0–20.
- Indicateurs de classe corrects sur un petit jeu de données.
Lancer `php artisan test` (vert) + `vendor/bin/pint`.

## LIVRABLES & DoD
- [ ] Tableau de bord enseignant + fiche classe + carnet de notes, reliés aux
      modules existants, avec actions rapides pré-remplies.
- [ ] Indicateurs (moyenne, présence, rendu, à risque) calculés sans N+1.
- [ ] Carnet éditable + export CSV.
- [ ] Sécurité par classe enseignée, tests verts, Pint, `view:clear`/`route:clear`.
- [ ] Entrée « Mon espace » ajoutée au tableau de bord professeur
      (`DashboardController`).
- [ ] `HANDOFF.md` mis à jour.

## CONTRAINTES
- Réutiliser et relier l'existant (ne pas dupliquer les CRUD déjà faits). Étendre
  les contrôleurs `Professeur\*` plutôt que tout réécrire.
- Code et UI en français. Commits/PR seulement si on te le demande.
