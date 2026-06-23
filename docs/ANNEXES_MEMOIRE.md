---
title: "Annexes au mémoire — SIGE UCAO"
subtitle: "Architecture technique · Matrice besoins/réalisation · Guide d'utilisation · Difficultés · Perspectives"
lang: fr
---

# 1. Architecture technique

Le SIGE UCAO repose sur le **patron MVC** du framework Laravel 12. Les requêtes
HTTPS sont filtrées par des **middlewares** (authentification, rôle, CSRF,
limitation de débit) avant d'atteindre les **contrôleurs** organisés par profil.
La logique métier réutilisable est isolée dans des **services / classes de
support**, les données sont manipulées via l'**ORM Eloquent** sur une base
**SQLite**, et les vues sont rendues en **Blade + Bootstrap 5**.

![Figure A.4 : Architecture applicative du SIGE UCAO](docs/captures/uml-architecture.png){width=14cm}

- **Présentation** : Blade, Bootstrap 5, Bootstrap Icons, Chart.js (graphiques),
  thème clair/sombre.
- **Contrôle** : routes `web.php`, middlewares (`auth`, `role:*`, CSRF, `throttle`),
  contrôleurs `Admin/`, `Etudiant/`, `Professeur/`, `Comptabilite/`,
  `Recouvrement/`, `Financier/` + `CompteController`, `MessagerieController`,
  `RechercheGlobaleController`.
- **Services / Support** : `CalculMoyenne` (moyennes pondérées), `Menu`,
  `CsvExport`, `PhotoUtilisateur`, `Espaces`, `AssistantService` (Gemini),
  `PaydunyaService` (paiement mobile).
- **Données** : modèles Eloquent (User, Etudiant, Note, Ponderation, Projet,
  Soumission, CoursEnLigne, EmploiDuTemps, Paiement, Message…), base **SQLite**,
  **stockage public** (photos, copies déposées).
- **Services externes** : **Gemini** (assistant), **PayDunya** (mobile money),
  **Jitsi** (visioconférence), **DomPDF** (bulletins / EDT PDF).

# 2. Matrice besoins → réalisation

| Besoin exprimé | Réalisation dans le SIGE UCAO | État |
|---|---|:--:|
| Authentification sécurisée et différenciée | Espaces Étudiant/Personnel, mot de passe haché, CAPTCHA, *throttling* | ✅ |
| Consultation des notes et bulletins | Notes + bulletin PDF avec **moyennes pondérées** | ✅ |
| Pondération souple des évaluations | Catégories TP/Examen/TD/CC, poids configurables par matière | ✅ |
| Suivi des absences | Saisie (prof) et consultation (étudiant), seuil « situation rouge » | ✅ |
| Emploi du temps | Génération, gestion des salles, détection de conflits, export PDF | ✅ |
| Cours à distance | **Cours en ligne** (visioconférence Jitsi) planifiés/animés | ✅ |
| Remise et correction de travaux | **Dépôt de copie** en ligne + correction + publication de note | ✅ |
| Gestion financière (paiements, recouvrement) | Paiements, validation, débiteurs, impayés, engagements, relances | ✅ |
| Communication interne | **Messagerie** entre utilisateurs | ✅ |
| Recherche transversale | **Recherche globale** avec redirection contextuelle par rôle | ✅ |
| Pilotage / décision | Tableaux de bord, **statistiques et graphiques**, journal d'activité | ✅ |
| Gestion des comptes et rôles | CRUD utilisateurs (tous rôles), activation, **photos** | ✅ |
| Assistance aux utilisateurs | **Assistant intelligent** (Gemini) contextualisé par rôle | ✅ |
| Notifications | Notifications internes + e-mail (rappels d'échéance) | ✅ |
| Salles de visioconférence strictement privées | Authentification JWT (JaaS) | 🔜 Perspective |
| Messagerie en temps réel | WebSockets / diffusion en direct | 🔜 Perspective |
| Export tableur natif | Export CSV (ouvrable Excel) ; export `.xlsx` natif | ✅ / 🔜 |

# 3. Guide d'utilisation et comptes de démonstration

Tous les comptes de démonstration utilisent le mot de passe **`password`**. La page
de connexion affiche un **CAPTCHA arithmétique** (« combien font X + Y »).

| Profil | Identifiant | Espace de connexion |
|---|---|---|
| Administrateur | `admin` | Administration / Personnel |
| Agent comptable | `comptable` | Administration / Personnel |
| Agent de recouvrement | `recouvrement` | Administration / Personnel |
| Responsable financier | `financier` | Administration / Personnel |
| Professeur | `prof` (à `prof5`) | Administration / Personnel |
| Étudiant | `etudiant1`, `etudiant2`, `etudiant3` | Étudiant |

**Parcours type de démonstration**

1. **Professeur** (`prof`) : planifie un cours en ligne, assigne un devoir, puis
   ouvre le carnet de notes et **règle la pondération** (ex. TP 60 % / Examen 40 %).
2. **Étudiant** (`etudiant2`) : rejoint le cours en ligne, **dépose une copie**,
   consulte ses notes et son **bulletin (moyennes pondérées)**.
3. **Professeur** : **corrige la copie** → la note est publiée au bulletin.
4. **Comptable / Recouvrement / Financier** : enregistrent un paiement, suivent les
   impayés, consultent les rapports.
5. **Administrateur** : gère les comptes, consulte les **statistiques** et la
   **recherche globale**.

# 4. Difficultés rencontrées et solutions

| Difficulté | Solution apportée |
|---|---|
| Cohérence du solde de scolarité (donnée stockée parfois désynchronisée) | Calcul à la volée `soldeReel()` = scolarité − paiements validés (source de vérité) |
| Calcul de moyenne tenant compte de catégories sans note | **Re-normalisation** des poids sur les catégories effectivement notées |
| Sécurité de l'upload de photos | Vérification du **MIME réel**, nom aléatoire, suppression des orphelins, repli initiales |
| Accès limité aux données par rôle | Middlewares de rôle + vérifications « ne voit que ses classes / ses données » |
| Lisibilité d'une application multi-rôles | Refonte UI : barre latérale, barre supérieure, design unifié, mode sombre |

# 5. Perspectives d'évolution

- **Visioconférence privée** par jeton **JWT** (Jitsi JaaS) et enregistrement des séances.
- **Messagerie en temps réel** (WebSockets) et notifications **push**.
- **Export `.xlsx` natif** et tableaux de bord analytiques avancés.
- **Suivi de présence** aux cours en ligne et statistiques d'assiduité.
- **Application mobile** (PWA déjà amorcée) et mode hors-ligne.
- **Migration** SQLite → PostgreSQL/MySQL pour un déploiement en production.
