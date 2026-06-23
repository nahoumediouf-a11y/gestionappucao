# PROMPT — Connexion différenciée par partie prenante (SIGE UCAO)
# Stack : Laravel 12, Blade, Bootstrap 5, design system existant dans layouts/app

> Lis `HANDOFF.md`. Le flux existe déjà : `auth/welcome.blade.php` propose 2 cartes
> (Étudiant / Personnel) → `route('login', ['espace' => ...])` →
> `auth/login.blade.php` qui adapte le panneau de marque via `$espace`
> (`LoginController::showWelcome` / `showLoginForm`). On NE casse pas l'auth, le
> captcha, les rôles ni les routes. On REUTILISE le design system (`.auth-*`,
> `.btn-ucao`, variables CSS, mode sombre).

## RÔLE
Tu es ingénieur Laravel + designer front. Tu transformes l'entrée en une
**connexion différenciée par partie prenante**, chacune avec sa propre identité
(icône, couleur d'accent, accroche, fonctionnalités), tout en gardant un seul
design system et une seule logique d'authentification.

## OBJECTIF
1. Centraliser la définition des **parties prenantes** dans un seul endroit
   (clé `espace` → libellé, icône Bootstrap, couleur, baseline, liste de
   fonctionnalités, rôles concernés). Source de vérité unique, réutilisée par la
   page d'accueil ET la page de login.
2. Parties prenantes à couvrir (réutiliser `App\Enums\Role`) :
   - **Étudiant**
   - **Professeur**
   - **Administration** (Administrateur)
   - **Comptabilité** (Agent comptable)
   - **Recouvrement** (Agent de recouvrement)
   - **Finances** (Responsable financier)
   Regroupe-les visuellement en 2 familles si besoin (Pédagogie : Étudiant,
   Professeur ; Gestion : Administration, Comptabilité, Recouvrement, Finances),
   mais chaque partie prenante a sa carte/identité propre.
3. **Page d'accueil** : grille de cartes par partie prenante (icône + couleur +
   accroche + bouton « Accéder »), animées et responsives, cohérentes avec le
   style actuel des `ucao-choice-card`.
4. **Page de login** : le panneau de marque s'adapte à l'`espace` choisi (titre,
   icône, couleur d'accent, liste de fonctionnalités) à partir de la définition
   centralisée. Le formulaire (login + mot de passe + captcha + « se souvenir »)
   reste identique et fonctionnel.

## IMPLÉMENTATION ATTENDUE
- Définir la carte des espaces dans **un seul point** : soit une méthode statique
  `App\Support\Espaces::all()` / `::get($cle)`, soit `config/espaces.php`. Chaque
  entrée : `cle`, `label`, `icone`, `couleur` (classe Bootstrap ou variable),
  `baseline`, `fonctionnalites[]`, `roles[]` (valeurs de `Role`).
- `LoginController::showWelcome` passe la liste des espaces à la vue d'accueil.
- `LoginController::showLoginForm` lit `?espace=` , récupère la définition (avec
  **fallback propre** si la clé est absente/invalide → espace générique), et la
  passe à `login.blade.php`. Conserver la génération du captcha.
- `welcome.blade.php` : boucle sur les espaces pour générer les cartes (plus de
  HTML dupliqué). `login.blade.php` : panneau de marque piloté par la définition.
- **Sécurité** : `espace` est purement cosmétique/guidage ; l'autorisation reste
  fondée sur le rôle réel de l'utilisateur après authentification. Optionnel :
  message d'aide si le rôle connecté ne correspond pas à l'espace choisi (sans
  bloquer la connexion).
- **Performance / accessibilité** : pas de JS lourd, icônes Bootstrap, contrastes
  AA, navigation clavier, `aria-label` sur les cartes, responsive mobile→desktop.

## TESTS (tests/Feature, style existant)
- `welcome` (`/`) répond 200 et liste toutes les parties prenantes.
- `/login?espace=professeur` répond 200 et affiche la marque « Professeur » ;
  `/login?espace=comptabilite` affiche la sienne ; `/login?espace=xxx` (invalide)
  → 200 avec l'espace générique (pas d'erreur).
- L'authentification et le captcha restent fonctionnels (les tests `AuthTest`
  existants restent verts).
Lancer `php artisan test` (vert hors `RegistrationTest` désactivé) + `vendor/bin/pint`.

## LIVRABLES & DoD
- [ ] Définition centralisée des espaces (un seul fichier/classe).
- [ ] Accueil avec une carte par partie prenante (généré par boucle, sans
      duplication), cohérent avec le design actuel.
- [ ] Login dont le panneau s'adapte à l'espace, captcha conservé, fallback géré.
- [ ] Aucune régression d'auth, tests verts, Pint, `view:clear`.
- [ ] `HANDOFF.md` mis à jour.

## CONTRAINTES
- Réutiliser `layouts/app` (design system, thème) ; ne dupliquer ni le CSS ni le
  HTML des cartes. Étendre `LoginController`/`welcome`/`login` plutôt que réécrire.
- UI 100 % française. Commits/PR seulement si on te le demande.
