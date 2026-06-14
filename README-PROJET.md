# Recouvrement UCAO — Application Laravel

Système de contrôle de recouvrement des frais scolaires (projet de soutenance).

## Démarrage

```bash
cd recouvrement-ucao
php artisan migrate:fresh --seed
php artisan serve
```

Ouvrir : **http://127.0.0.1:8000/login**

## Comptes de démonstration

| Login | Mot de passe | Rôle |
|---|---|---|
| `admin` | `password` | Administrateur |
| `comptable` | `password` | Agent comptable |
| `recouvrement` | `password` | Agent de recouvrement |
| `financier` | `password` | Responsable financier |
| `prof` | `password` | Professeur |
| `etudiant1` | `password` | Étudiant (UCAO-2024-001, Informatique L3) |
| `etudiant2` | `password` | Étudiant (UCAO-2024-002, Informatique L3) |
| `etudiant3` | `password` | Étudiant (UCAO-2024-003, Informatique L3) — Fatoumata Sidibe |

## Authentification (diagramme UML)

- **UC1** : S'authentifier → `/login`
- **UC10** : Se déconnecter → bouton sur le tableau de bord
- Les modules affichés après connexion respectent les permissions de chaque rôle (`app/Enums/Role.php`)

## Modules par rôle

- **Administrateur** : gestion des comptes utilisateurs (CRUD), statistiques globales (`/admin/utilisateurs`, `/admin/statistiques`)
- **Agent comptable** : enregistrement et modification des paiements, reçus imprimables, liste des débiteurs (`/comptabilite/paiements`, `/comptabilite/debiteurs`)
- **Agent de recouvrement** : suivi des impayés, recherche d'étudiants, gestion des engagements de paiement, relances, statistiques (`/recouvrement/*`)
- **Responsable financier** : consultation et validation des paiements, rapports, statistiques (`/financier/*`)
- **Professeur** : emploi du temps, liste des étudiants de ses filières/niveaux, saisie/modification des notes et absences (`/professeur/*`)
- **Étudiant** : profil, notes et moyenne, absences, emploi du temps, suivi des paiements/solde et reçus (`/etudiant/*`)

## Stack

- Laravel 12, SQLite (dev) / MySQL (prod)
- Bootstrap 5, HTML5, CSS3, JavaScript
- Mots de passe chiffrés (bcrypt)

## Sécurité

- CSRF, CAPTCHA (question mathématique) et limitation du débit (`throttle:5,1`) sur connexion/inscription
- Session et cookies chiffrés, cookies `Secure` automatiques en HTTPS
- `APP_DEBUG=false` : aucune trace technique exposée en cas d'erreur
- Accès aux notes/absences/profils strictement filtrés par utilisateur connecté (pas d'IDOR)

### Sauvegarde de la base de données (anti-ransomware)

```bash
php artisan backup:database
```

Copie `database/database.sqlite` vers `storage/app/backups/` avec horodatage et ne conserve que les 10 dernières
sauvegardes (option `--keep=N`). Une tâche planifiée quotidienne est définie dans `routes/console.php`
(nécessite que `php artisan schedule:work` ou un cron `schedule:run` tourne en continu).

⚠️ Pensez à copier régulièrement `storage/app/backups/` vers un emplacement **hors-ligne / hors du serveur**
(disque externe, cloud) : une sauvegarde restée sur la même machine ne protège pas contre un ransomware qui
chiffrerait tout le disque.

### Anti-phishing

- N'utilisez **que** l'URL officielle de l'application pour vous connecter ; ne cliquez jamais sur un lien de
  connexion reçu par email/SMS non sollicité.
- Le tunnel `trycloudflare.com` utilisé en démo change d'adresse à chaque redémarrage — en production, utiliser
  un nom de domaine fixe avec certificat HTTPS pour que les utilisateurs reconnaissent l'URL légitime.
- Ne jamais saisir son login/mot de passe UCAO sur un site autre que l'application officielle, même s'il en a
  l'apparence.
