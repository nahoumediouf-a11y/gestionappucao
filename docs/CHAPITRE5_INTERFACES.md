---
title: "CHAPITRE 5 — Présentation des interfaces (version actualisée)"
subtitle: "SIGE UCAO — L'e-gouvernance et la digitalisation des services administratifs (UCAO Saint Michel)"
lang: fr
---

# 5.2 Présentation des interfaces

> **Note de version.** Cette section remplace les captures de la version initiale
> du mémoire (anciennes Figures 5.1 à 5.28). Elle présente l'interface **actuelle**
> du SIGE UCAO après refonte : nouvelle identité visuelle (barre latérale
> rétractable, barre supérieure, mode clair/sombre, notifications par *toasts*),
> photos de profil réelles, et de nouveaux modules — **cours en ligne
> (visioconférence)**, **dépôt et correction des évaluations**, **espace
> enseignant** (carnet de notes et **pondération TP / Examen / TD / Contrôle
> continu**), **messagerie interne** et **recherche globale**.

## 5.2.1 Interface d'authentification

Le point d'entrée du système propose deux espaces (Étudiant / Administration &
Personnel). L'authentification reste sécurisée par identifiant, mot de passe et
**CAPTCHA arithmétique**.

![Figure 5.1 : Page d'accueil — choix de l'espace](docs/captures/01-accueil.png){width=15cm}

![Figure 5.2 : Page de connexion — Espace Étudiant (avec CAPTCHA)](docs/captures/02-login-etudiant.png){width=15cm}

![Figure 5.3 : Page de connexion — Espace Administration / Personnel](docs/captures/03-login-personnel.png){width=15cm}

## 5.2.2 Tableau de bord dynamique

Après authentification, chaque utilisateur accède à un tableau de bord adapté à
son rôle. Le tableau de bord de pilotage (administration / responsable financier)
présente des **cartes statistiques** (effectifs, paiements du mois, taux de
recouvrement) et des **graphiques Chart.js** (évolution des paiements, répartition
des étudiants, absences).

![Figure 5.4 : Tableau de bord de pilotage avec indicateurs et graphiques](docs/captures/80-admin-dashboard.png){width=15cm}

## 5.2.3 Interface Étudiant

L'étudiant dispose d'un espace personnel regroupant sa scolarité, ses résultats et
ses services en ligne.

![Figure 5.5 : Tableau de bord étudiant (aperçu : solde, moyenne, prochaine séance, travaux à rendre)](docs/captures/10-etudiant-dashboard.png){width=15cm}

![Figure 5.6 : Mon compte — informations personnelles et photo de profil](docs/captures/11-etudiant-compte.png){width=15cm}

![Figure 5.7 : Consultation des notes](docs/captures/12-etudiant-notes.png){width=15cm}

![Figure 5.8 : Bulletin de notes (moyennes pondérées par matière)](docs/captures/13-etudiant-bulletin.png){width=15cm}

![Figure 5.9 : Consultation des absences](docs/captures/14-etudiant-absences.png){width=15cm}

![Figure 5.10 : Emploi du temps](docs/captures/15-etudiant-edt.png){width=15cm}

![Figure 5.11 : Cours en ligne — séances de visioconférence de la classe (nouveau)](docs/captures/16-etudiant-cours-en-ligne.png){width=15cm}

![Figure 5.12 : Projets, devoirs et examens](docs/captures/17-etudiant-projets.png){width=15cm}

![Figure 5.13 : Détail d'un travail et dépôt en ligne de la copie (nouveau)](docs/captures/18-etudiant-projet-rendu.png){width=15cm}

![Figure 5.14 : Suivi des paiements](docs/captures/19-etudiant-paiements.png){width=15cm}

![Figure 5.15 : Messagerie interne (nouveau)](docs/captures/20-messagerie.png){width=15cm}

![Figure 5.16 : Assistant intelligent (Gemini)](docs/captures/21-assistant.png){width=15cm}

## 5.2.4 Interface Professeur

L'enseignant dispose d'un véritable poste de travail pédagogique reliant ses
classes, ses notes, ses cours et ses corrections.

![Figure 5.17 : Mon espace enseignant — journée, classes et tâches à traiter (nouveau)](docs/captures/31-prof-espace.png){width=15cm}

![Figure 5.18 : Fiche de classe — indicateurs, étudiants et photos (nouveau)](docs/captures/32-prof-fiche-classe.png){width=15cm}

![Figure 5.19 : Carnet de notes — saisie par catégorie et moyenne pondérée (nouveau)](docs/captures/33-prof-carnet.png){width=15cm}

![Figure 5.20 : Pondération des notes — poids TP / Examen / TD / Contrôle continu (nouveau)](docs/captures/34-prof-ponderation.png){width=15cm}

![Figure 5.21 : Cours en ligne — planification et animation des séances (nouveau)](docs/captures/35-prof-cours-en-ligne.png){width=15cm}

![Figure 5.22 : Saisie et gestion des notes](docs/captures/36-prof-notes.png){width=15cm}

![Figure 5.23 : Gestion des absences](docs/captures/37-prof-absences.png){width=15cm}

![Figure 5.24 : Projets, devoirs et examens assignés](docs/captures/38-prof-projets.png){width=15cm}

![Figure 5.25 : Correction des copies déposées et publication des notes (nouveau)](docs/captures/39-prof-copies.png){width=15cm}

![Figure 5.26 : Liste des étudiants enseignés (avec photos)](docs/captures/40-prof-etudiants.png){width=15cm}

## 5.2.5 Interface Comptabilité

L'agent comptable gère les encaissements, les reçus et le suivi des débiteurs.

![Figure 5.27 : Tableau de bord comptabilité](docs/captures/50-comptable-dashboard.png){width=15cm}

![Figure 5.28 : Liste et historique des paiements](docs/captures/51-comptable-paiements.png){width=15cm}

![Figure 5.29 : Enregistrement d'un paiement](docs/captures/52-comptable-enregistrer-paiement.png){width=15cm}

![Figure 5.30 : Étudiants débiteurs](docs/captures/53-comptable-debiteurs.png){width=15cm}

## 5.2.6 Interface Recouvrement

L'agent de recouvrement suit les impayés, les engagements de paiement et les
relances.

![Figure 5.31 : Tableau de bord recouvrement](docs/captures/60-recouvrement-dashboard.png){width=15cm}

![Figure 5.32 : Étudiants en situation d'impayé](docs/captures/61-recouvrement-impayes.png){width=15cm}

![Figure 5.33 : Engagements de paiement](docs/captures/62-recouvrement-engagements.png){width=15cm}

![Figure 5.34 : Relances](docs/captures/63-recouvrement-relances.png){width=15cm}

![Figure 5.35 : Statistiques de recouvrement](docs/captures/64-recouvrement-stats.png){width=15cm}

## 5.2.7 Interface Responsable Financier

Le responsable financier supervise l'ensemble des paiements, valide les opérations
et consulte les rapports.

![Figure 5.36 : Tableau de bord financier](docs/captures/70-financier-dashboard.png){width=15cm}

![Figure 5.37 : Vue d'ensemble des paiements](docs/captures/71-financier-paiements.png){width=15cm}

![Figure 5.38 : Rapports financiers](docs/captures/72-financier-rapports.png){width=15cm}

![Figure 5.39 : Statistiques financières](docs/captures/73-financier-stats.png){width=15cm}

## 5.2.8 Interface Administration

L'administrateur gère les comptes (tous rôles, avec photos), les emplois du temps,
les cours en ligne, les statistiques et la traçabilité.

![Figure 5.40 : Gestion des utilisateurs — recherche, tri, filtres, export CSV (avec photos)](docs/captures/81-admin-utilisateurs.png){width=15cm}

![Figure 5.41 : Création / modification d'un compte (avec photo de profil)](docs/captures/82-admin-creer-utilisateur.png){width=15cm}

![Figure 5.42 : Gestion des emplois du temps et des salles](docs/captures/83-admin-edt.png){width=15cm}

![Figure 5.43 : Supervision des cours en ligne (nouveau)](docs/captures/84-admin-cours-en-ligne.png){width=15cm}

![Figure 5.44 : Tableau de bord statistique](docs/captures/85-admin-statistiques.png){width=15cm}

![Figure 5.45 : Journal des activités (traçabilité)](docs/captures/86-admin-journal.png){width=15cm}

![Figure 5.46 : Recherche globale avec redirection contextuelle par rôle (nouveau)](docs/captures/87-admin-recherche-globale.png){width=15cm}

# Principales évolutions par rapport à la version initiale

- **Refonte de l'interface** : barre latérale rétractable + barre supérieure
  (recherche, notifications, messagerie, thème, profil), design moderne (coins
  arrondis, ombres douces), **mode clair/sombre** et notifications par *toasts*.
- **Cours en ligne** : visioconférence (Jitsi) planifiée et animée par le
  professeur, rejointe par les étudiants de la classe.
- **Évaluations** : dépôt en ligne des copies par l'étudiant, **correction** et
  publication des notes par le professeur.
- **Espace enseignant** : tableau de bord, fiche de classe, **carnet de notes** et
  **pondération configurable** (TP / Examen / TD / Contrôle continu — TP à 30 %
  par défaut, modifiable).
- **Photos de profil réelles** des étudiants (repli sur des initiales).
- **Messagerie interne** entre tous les utilisateurs et **recherche globale**.
- **Tableaux** enrichis : tri des colonnes, filtres et **export CSV (Excel)**.
- **Authentification** : page d'accueil à deux espaces (Étudiant / Personnel),
  connexion par identifiant + mot de passe + CAPTCHA (inchangée sur le fond).

> Les anciennes captures d'écran (version initiale) sont remplacées par les
> figures ci-dessus, qui reflètent l'état actuel de l'application.
