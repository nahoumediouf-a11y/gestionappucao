---
title: "Compléments au mémoire — SIGE UCAO"
subtitle: "Tests · Fonctionnalités par rôle · Modélisation UML actualisée"
lang: fr
---

# 1. Tests du système

Le SIGE UCAO est couvert par une **suite de tests automatisés** (PHPUnit / Laravel)
exécutée via `php artisan test`. Ces tests vérifient à la fois les règles métier
(calcul des moyennes pondérées, accès par rôle, échéances…) et les parcours
fonctionnels (connexion, dépôt de copie, correction, messagerie, etc.).

**Résultat global : 85 tests, 84 réussis** (199 assertions). Le seul test en échec
(`RegistrationTest`) est **attendu** : l'inscription publique a été désactivée
volontairement (la création de compte est réservée à l'administration).

| Domaine testé | Fichier de test | Nb de tests | Résultat |
|---|---|:--:|:--:|
| Authentification (login, mot de passe, CAPTCHA) | AuthTest | 5 | ✓ |
| Connexion par espace (Étudiant / Personnel) | ConnexionEspacesTest | 4 | ✓ |
| Contrôle d'accès par rôle | RoleAccessTest | 4 | ✓ |
| Tableau de bord étudiant | DashboardEtudiantTest | 2 | ✓ |
| Compte unifié (Mon compte, gestion admin) | CompteUnifieTest | 6 | ✓ |
| Emploi du temps et conflits de salle | EmploiDuTempsSalleTest | 4 | ✓ |
| Cours en ligne (visioconférence) | CoursEnLigneTest | 7 | ✓ |
| Évaluations (dépôt + correction) | EvaluationTest | 7 | ✓ |
| Pondération des notes (TP/Examen/TD/CC) | PonderationTest | 6 | ✓ |
| Espace enseignant (classes, carnet) | EspaceEnseignantTest | 6 | ✓ |
| Messagerie interne | MessagerieTest | 5 | ✓ |
| Recherche globale | RechercheGlobaleTest | 5 | ✓ |
| Tableaux (tri, filtres, export CSV) | TableauAvanceTest | 4 | ✓ |
| Photos de profil (upload sécurisé) | PhotoEtudiantTest | 7 | ✓ |
| Bulletin PDF | BulletinPdfTest | 3 | ✓ |
| Rappels d'échéance | RappelEcheanceTest | 5 | ✓ |
| Inscription (désactivée) | RegistrationTest | 3 | 2 ✓ / 1 attendu ✗ |

## 1.1 Exemples de scénarios de test fonctionnels

| # | Scénario | Résultat attendu | Statut |
|---|---|---|:--:|
| T1 | Un étudiant dépose une copie après l'échéance | La copie est acceptée et **marquée « en retard »** | ✓ |
| T2 | Un professeur corrige une copie (note /barème) | La **note est publiée au bulletin**, ramenée sur 20 | ✓ |
| T3 | Le professeur fixe TP = 60 %, Examen = 40 % | La **moyenne de la matière** est recalculée en conséquence | ✓ |
| T4 | Un étudiant ouvre la fiche de classe d'une autre classe | Accès **refusé (403)** | ✓ |
| T5 | Upload d'un fichier non-image renommé en .jpg | **Refusé** (vérification du type MIME réel) | ✓ |
| T6 | Connexion avec une mauvaise réponse au CAPTCHA | Connexion **rejetée** | ✓ |

# 2. Fonctionnalités par rôle

Le tableau ci-dessous synthétise les fonctionnalités accessibles selon le profil
(É = Étudiant, Pr = Professeur, Co = Agent comptable, Re = Agent de recouvrement,
Fi = Responsable financier, Ad = Administrateur).

| Fonctionnalité | É | Pr | Co | Re | Fi | Ad |
|---|:--:|:--:|:--:|:--:|:--:|:--:|
| Authentification par espace + CAPTCHA | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Tableau de bord personnalisé | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Mon compte + photo de profil | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Messagerie interne | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Assistant intelligent (Gemini) | ✓ | ✓ | ✓ | ✓ | ✓ | ✓ |
| Consulter notes & bulletin | ✓ |  |  |  |  |  |
| Consulter absences & emploi du temps | ✓ | ✓ |  |  |  | ✓ |
| Cours en ligne | rejoindre | animer |  |  |  | superviser |
| Projets / devoirs / examens | déposer | assigner/corriger |  |  |  |  |
| Carnet de notes & pondération | | ✓ |  |  |  |  |
| Saisie des notes / absences | | ✓ |  |  |  |  |
| Espace enseignant (classes) | | ✓ |  |  |  |  |
| Paiements | déclarer/suivre | | enregistrer/valider |  | valider/superviser |  |
| Débiteurs / impayés / engagements / relances | | | débiteurs | ✓ |  |  |
| Rapports & statistiques | | | | ✓ | ✓ | ✓ |
| Gestion des utilisateurs & rôles | | | | | | ✓ |
| Gestion EDT & salles | | | | | | ✓ |
| Recherche globale | | ✓ | ✓ | ✓ | ✓ | ✓ |
| Journal d'activité (traçabilité) | | | | | | ✓ |

# 3. Modélisation UML actualisée

Les diagrammes suivants reflètent l'architecture **après l'ajout des nouveaux
modules** (cours en ligne, évaluations avec dépôt/correction, pondération des
notes, messagerie). Ils complètent et mettent à jour les diagrammes du Chapitre 4.

## 3.1 Diagramme de cas d'utilisation (vue actualisée)

![Figure A.1 : Principaux cas d'utilisation par acteur (modules ajoutés inclus)](docs/captures/uml-cas.png){width=16cm}

## 3.2 Diagramme de classes (entités principales)

Outre les entités initiales, le modèle intègre **`CoursEnLigne`**, **`Soumission`**
(copie déposée et corrigée), **`Ponderation`** (poids des catégories de notes) et
**`Message`**.

![Figure A.2 : Diagramme de classes actualisé du SIGE UCAO](docs/captures/uml-classes.png){width=16cm}

## 3.3 Diagramme de séquence — dépôt et correction d'une évaluation

![Figure A.3 : Séquence « dépôt d'une copie → correction → publication de la note pondérée »](docs/captures/uml-sequence.png){width=15cm}

# 4. Mécanismes de sécurité (synthèse)

- **Authentification** : identifiant + mot de passe **haché** (bcrypt) + **CAPTCHA**
  arithmétique ; limitation du nombre de tentatives (*throttling*).
- **Autorisation** : middleware de **rôles** ; un professeur n'accède qu'à **ses
  classes**, un étudiant qu'à **ses propres données** (vérifications côté serveur).
- **Validation** systématique des formulaires côté serveur ; protection **CSRF**.
- **Upload de fichiers** : vérification du **type MIME réel**, nom de fichier
  **aléatoire**, suppression des fichiers orphelins.
- **Traçabilité** : journal des activités sensibles (connexions, modifications,
  corrections, pondérations…).
- **Perspective** : authentification **JWT** des salles de visioconférence (Jitsi
  JaaS) pour des salles strictement privées.
