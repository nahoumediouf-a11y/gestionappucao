# PROMPT — Pondération des notes par matière (TP / Examen / TD / CC) — SIGE UCAO
# Laravel 12 · SQLite · Blade · Bootstrap 5 · design system existant

## CONTEXTE (lire HANDOFF.md, réutiliser, ne pas casser)
- `Note` (etudiant_id, professeur_id, matiere, valeur /20, session) regroupées par
  `session` ; bulletin et `Etudiant::moyenne()` font une **moyenne simple**.
- Évaluations (`Projet` projet/devoir/examen + `Soumission`) publient une `Note`
  (session « Contrôle continu ») à la correction.
- Carnet prof : grille étudiants × sessions par matière, saisie inline.
- Ne pas casser : ces flux, l'auth, les routes, les tests.

## OBJECTIF MÉTIER
Le professeur définit, **par matière et par classe (filière+niveau)**, la
composition de la note finale à partir de catégories pondérées : **Examen, TP,
TD, Contrôle continu**. Par défaut **TP = 30 %, Examen = 70 %**. Le prof peut
mettre TP à 60 %, « Examen 100 % », « TP 50 / Examen 50 », « CC 40 / Examen 60 »…
Somme des poids actifs = 100 %.

## DÉCISIONS RETENUES
1. Re-normalisation : si une catégorie active (poids>0) n'a aucune note, son poids
   est redistribué sur les catégories actives **qui ont des notes** (préserve les
   moyennes existantes).
2. Pondération **par classe + matière** (filière+niveau+matiere).
3. Catégorie des évaluations : examen→`examen`, projet/devoir→`tp` à la
   publication ; modifiable dans le carnet.

## MODÈLE DE DONNÉES (migrations additives, SQLite)
1. `notes.categorie` (`examen|tp|td|cc`, défaut `examen` ; backfill des notes
   existantes à `examen`). `Note::CATEGORIES`.
2. Table `ponderations` (`professeur_id`, `filiere`, `niveau`, `matiere`,
   `poids_examen`, `poids_tp`, `poids_td`, `poids_cc`), unique (filiere,niveau,matiere).
   `Ponderation::DEFAUTS = [examen=>70, tp=>30, td=>0, cc=>0]`, `pour()`, `poids()`,
   `valide()` (somme=100).
3. Service `App\Support\CalculMoyenne` : moyenne pondérée par matière (avec
   re-normalisation) + moyenne générale.

## RÈGLES DE CALCUL
- Moyenne catégorie = moyenne simple des notes de la catégorie (matière).
- Poids re-normalisés sur catégories actives ayant des notes.
- Moyenne matière = Σ(moyenne_cat × poids_normalisé)/100, arrondie 0,01.
- Moyenne générale = moyenne des moyennes de matières. Aucune note → « — ».

## INTÉGRATION
- Saisie : `categorie` renseignée au carnet, dans `Professeur\NoteController`, et à
  la publication des évaluations.
- `Etudiant::moyenne()` et tous ses usages (profil, dashboard, fiche classe,
  bulletin) passent par `CalculMoyenne` (pas de double logique).
- Bulletin : détail par catégorie (moyenne + poids) puis moyenne pondérée.
- Carnet : bouton « Pondération » par matière + sélection de la catégorie par colonne.

## UI (design system, FR, responsive, sombre)
- Écran pondération prof : 4 champs %, total live (vert=100), presets rapides.
- Carnet : sélecteur de catégorie. Bulletin : sous-lignes catégories + moyenne pondérée.

## SÉCURITÉ / QUALITÉ / PERF
- Le prof ne pondère que ses matières/classes (`enseigneClasse`).
- Validation : poids 0–100, somme=100 ; catégorie ∈ liste ; `ActivityLogger`.
- Pas de N+1 ; migrations additives ; Pint ; `view:clear`.

## TESTS
- Défaut TP30/Examen70 ; TP60 ; Examen100 ; catégorie active sans note
  (re-normalisation) ; somme≠100 refusée ; 403 matière d'un autre prof ;
  bulletin & `moyenne()` pondérés. `php artisan test` vert (hors RegistrationTest).

## LIVRABLES & DoD
- [ ] Migrations + modèles + service testé.
- [ ] Écran pondération prof + catégorie au carnet.
- [ ] Bulletin + moyennes recalculés pondérés.
- [ ] Tests verts, Pint, HANDOFF mis à jour.
