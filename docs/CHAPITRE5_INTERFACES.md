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

L'interface du SIGE UCAO a été conçue selon les principes des applications de
gestion modernes : sobriété visuelle, cohérence entre les écrans, navigation en
peu de clics et accessibilité. Chaque utilisateur, après authentification, est
dirigé vers un espace dont le contenu et les menus sont strictement adaptés à son
rôle, conformément à la politique de contrôle d'accès définie en phase de
conception. Les sections suivantes présentent, pour chaque profil, les principales
interfaces réalisées.

## 5.2.1 Interface d'authentification

Le point d'entrée du système propose deux espaces distincts — **Étudiant** et
**Administration / Personnel** — afin d'orienter l'utilisateur dès l'accueil.
L'authentification reste sécurisée par un identifiant, un mot de passe (haché) et
un **CAPTCHA arithmétique** qui limite les tentatives automatisées. Cette
séparation visuelle clarifie le parcours sans modifier le mécanisme de connexion.

![Figure 5.1 : Page d'accueil — choix de l'espace](docs/captures/01-accueil.png){width=15cm}

![Figure 5.2 : Page de connexion — Espace Étudiant (avec CAPTCHA)](docs/captures/02-login-etudiant.png){width=15cm}

![Figure 5.3 : Page de connexion — Espace Administration / Personnel](docs/captures/03-login-personnel.png){width=15cm}

## 5.2.2 Tableau de bord dynamique

Après authentification, chaque utilisateur accède à un tableau de bord
personnalisé. Pour les profils de pilotage (administration, responsable
financier), il agrège des **indicateurs clés** — effectifs, nombre de professeurs,
paiements du mois, taux de recouvrement — et des **graphiques** (évolution des
paiements, répartition des étudiants par filière, absences) générés avec Chart.js.
Ce tableau de bord constitue un véritable outil d'aide à la décision.

![Figure 5.4 : Tableau de bord de pilotage avec indicateurs et graphiques](docs/captures/80-admin-dashboard.png){width=15cm}

## 5.2.3 Interface Étudiant

L'espace étudiant centralise la scolarité, les résultats et les services en ligne.
Dès la page d'accueil, un bandeau de synthèse rappelle le solde restant, la moyenne
générale, la prochaine séance et les travaux à rendre. L'étudiant peut consulter et
mettre à jour son profil (y compris sa **photo**), suivre ses notes et son
**bulletin** (dont les moyennes sont désormais **pondérées par catégorie**), ses
absences et son emploi du temps.

![Figure 5.5 : Tableau de bord étudiant (solde, moyenne, prochaine séance, travaux à rendre)](docs/captures/10-etudiant-dashboard.png){width=15cm}

![Figure 5.6 : Mon compte — informations personnelles et photo de profil](docs/captures/11-etudiant-compte.png){width=15cm}

![Figure 5.7 : Consultation des notes](docs/captures/12-etudiant-notes.png){width=15cm}

![Figure 5.8 : Bulletin de notes (moyennes pondérées par matière)](docs/captures/13-etudiant-bulletin.png){width=15cm}

![Figure 5.9 : Consultation des absences](docs/captures/14-etudiant-absences.png){width=15cm}

![Figure 5.10 : Emploi du temps](docs/captures/15-etudiant-edt.png){width=15cm}

Au-delà de la consultation, l'étudiant accède à de **nouveaux services** : il peut
rejoindre les **cours en ligne** (visioconférence) programmés pour sa classe et
**déposer ses travaux** (fichier et/ou texte) directement depuis la plateforme, le
dépôt étant horodaté et signalé en cas de retard.

![Figure 5.11 : Cours en ligne — séances de visioconférence de la classe (nouveau)](docs/captures/16-etudiant-cours-en-ligne.png){width=15cm}

![Figure 5.12 : Projets, devoirs et examens](docs/captures/17-etudiant-projets.png){width=15cm}

![Figure 5.13 : Détail d'un travail et dépôt en ligne de la copie (nouveau)](docs/captures/18-etudiant-projet-rendu.png){width=15cm}

Enfin, l'étudiant suit ses paiements, échange avec l'administration via la
**messagerie interne** et peut solliciter l'**assistant intelligent** pour toute
question relative à sa scolarité.

![Figure 5.14 : Suivi des paiements](docs/captures/19-etudiant-paiements.png){width=15cm}

![Figure 5.15 : Messagerie interne (nouveau)](docs/captures/20-messagerie.png){width=15cm}

![Figure 5.16 : Assistant intelligent (Gemini)](docs/captures/21-assistant.png){width=15cm}

## 5.2.4 Interface Professeur

L'enseignant dispose d'un véritable **poste de travail pédagogique**. Son espace
réunit, sur un tableau de bord, sa journée (séances et cours en ligne), ses classes
et les tâches en attente (copies à corriger, échéances). Pour chaque classe, une
fiche dédiée présente des indicateurs (effectif, moyenne, taux de rendu, étudiants
à risque) et la liste des étudiants accompagnés de leur photo.

![Figure 5.17 : Mon espace enseignant — journée, classes et tâches à traiter (nouveau)](docs/captures/31-prof-espace.png){width=15cm}

![Figure 5.18 : Fiche de classe — indicateurs, étudiants et photos (nouveau)](docs/captures/32-prof-fiche-classe.png){width=15cm}

La gestion des notes s'effectue au moyen d'un **carnet** où la saisie est rapide et
organisée par catégorie d'évaluation. L'enseignant définit librement la
**pondération** de la matière : par défaut les travaux pratiques comptent pour
30 % et l'examen pour 70 %, mais il peut ajuster ces poids (par exemple TP à 60 %)
ou ne retenir que l'examen ; la moyenne est recalculée automatiquement.

![Figure 5.19 : Carnet de notes — saisie par catégorie et moyenne pondérée (nouveau)](docs/captures/33-prof-carnet.png){width=15cm}

![Figure 5.20 : Pondération des notes — poids TP / Examen / TD / Contrôle continu (nouveau)](docs/captures/34-prof-ponderation.png){width=15cm}

L'enseignant planifie et anime des **cours en ligne**, saisit notes et absences,
assigne des travaux et **corrige les copies** déposées ; la note publiée alimente
directement le bulletin de l'étudiant.

![Figure 5.21 : Cours en ligne — planification et animation des séances (nouveau)](docs/captures/35-prof-cours-en-ligne.png){width=15cm}

![Figure 5.22 : Saisie et gestion des notes](docs/captures/36-prof-notes.png){width=15cm}

![Figure 5.23 : Gestion des absences](docs/captures/37-prof-absences.png){width=15cm}

![Figure 5.24 : Projets, devoirs et examens assignés](docs/captures/38-prof-projets.png){width=15cm}

![Figure 5.25 : Correction des copies déposées et publication des notes (nouveau)](docs/captures/39-prof-copies.png){width=15cm}

![Figure 5.26 : Liste des étudiants enseignés (avec photos)](docs/captures/40-prof-etudiants.png){width=15cm}

## 5.2.5 Interface Comptabilité

L'agent comptable gère le cycle des encaissements : enregistrement des paiements,
génération des reçus, validation des déclarations effectuées par les étudiants et
suivi des étudiants débiteurs. Son tableau de bord met en avant les actions
courantes et les situations à traiter.

![Figure 5.27 : Tableau de bord comptabilité](docs/captures/50-comptable-dashboard.png){width=15cm}

![Figure 5.28 : Liste et historique des paiements](docs/captures/51-comptable-paiements.png){width=15cm}

![Figure 5.29 : Enregistrement d'un paiement](docs/captures/52-comptable-enregistrer-paiement.png){width=15cm}

![Figure 5.30 : Étudiants débiteurs](docs/captures/53-comptable-debiteurs.png){width=15cm}

## 5.2.6 Interface Recouvrement

L'agent de recouvrement dispose des outils nécessaires au suivi des impayés :
consultation des étudiants en situation d'impayé, création d'**engagements de
paiement échelonné**, **relances** et statistiques de recouvrement.

![Figure 5.31 : Tableau de bord recouvrement](docs/captures/60-recouvrement-dashboard.png){width=15cm}

![Figure 5.32 : Étudiants en situation d'impayé](docs/captures/61-recouvrement-impayes.png){width=15cm}

![Figure 5.33 : Engagements de paiement](docs/captures/62-recouvrement-engagements.png){width=15cm}

![Figure 5.34 : Relances](docs/captures/63-recouvrement-relances.png){width=15cm}

![Figure 5.35 : Statistiques de recouvrement](docs/captures/64-recouvrement-stats.png){width=15cm}

## 5.2.7 Interface Responsable Financier

Le responsable financier supervise l'ensemble des opérations : vue consolidée des
paiements, validation des opérations, rapports financiers et statistiques. Ces
écrans offrent une lecture synthétique de la situation financière de
l'établissement.

![Figure 5.36 : Tableau de bord financier](docs/captures/70-financier-dashboard.png){width=15cm}

![Figure 5.37 : Vue d'ensemble des paiements](docs/captures/71-financier-paiements.png){width=15cm}

![Figure 5.38 : Rapports financiers](docs/captures/72-financier-rapports.png){width=15cm}

![Figure 5.39 : Statistiques financières](docs/captures/73-financier-stats.png){width=15cm}

## 5.2.8 Interface Administration

L'administrateur assure la gestion globale : comptes utilisateurs (tous rôles, avec
photos), recherche, tri, filtres et **export CSV** ; gestion des emplois du temps
et des salles ; supervision des cours en ligne ; consultation des statistiques et
du **journal des activités** garantissant la traçabilité. Une **recherche globale**
lui permet de retrouver rapidement un étudiant ou un membre du personnel, avec
redirection contextuelle vers l'action pertinente.

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
- **Cours en ligne** : visioconférence planifiée et animée par le professeur,
  rejointe par les étudiants de la classe.
- **Évaluations** : dépôt en ligne des copies par l'étudiant, **correction** et
  publication des notes par le professeur.
- **Espace enseignant** : tableau de bord, fiche de classe, **carnet de notes** et
  **pondération configurable** (TP / Examen / TD / Contrôle continu).
- **Photos de profil réelles** des étudiants (repli sur des initiales).
- **Messagerie interne** entre tous les utilisateurs et **recherche globale**.
- **Tableaux** enrichis : tri des colonnes, filtres et **export CSV (Excel)**.
- **Authentification** : page d'accueil à deux espaces (Étudiant / Personnel),
  connexion par identifiant + mot de passe + CAPTCHA (inchangée sur le fond).

> Les anciennes captures d'écran (version initiale) sont remplacées par les
> figures ci-dessus, qui reflètent l'état actuel de l'application. La **liste des
> figures** du mémoire doit être régénérée en conséquence (Figures 5.1 à 5.46).
