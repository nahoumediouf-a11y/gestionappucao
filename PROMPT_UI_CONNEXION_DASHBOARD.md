# PROMPT — Interface unifiée : connexion + tableau de bord étudiant (SIGE UCAO)
# Stack : Laravel 12, Blade, Bootstrap 5 (Bootstrap Icons), thème clair/sombre existant

> Lis d'abord `HANDOFF.md`. NE PAS repartir de zéro : on refond le rendu visuel en
> réutilisant l'existant (`layouts/app`, `layouts/dashboard`, `auth/welcome.blade.php`,
> la vue de login, `dashboard/index.blade.php`, `DashboardController`). Aucune
> régression fonctionnelle (captcha, rôles, routes, paiement, thème).

## RÔLE
Tu es un ingénieur Laravel + designer front. Tu crées une **expérience visuelle
unifiée** entre la **page de connexion** et le **tableau de bord étudiant** : même
identité de marque, mêmes couleurs, même typographie, mêmes composants (cartes,
boutons, badges). Les deux écrans doivent donner l'impression d'un seul produit.
« Ne pas les séparer » = un **design system commun**, pas deux maquettes isolées.

## OBJECTIF
1. Définir un mini **design system** UCAO partagé (variables CSS : couleurs primaire/
   secondaire, rayons, ombres, espacements, états clair/sombre) dans un seul endroit
   réutilisé par les deux écrans.
2. Refondre la **page de connexion** : accueil de marque (logo, nom, baseline),
   formulaire clair, captcha intégré proprement, lien mot de passe oublié, message
   d'erreur lisible, responsive et accessible.
3. Refondre le **tableau de bord étudiant** : en-tête personnalisé (bonjour + nom,
   filière/niveau), **bandeau d'état** (solde à payer mis en avant si > 0, moyenne,
   prochaine séance, prochaine échéance), puis les modules en **cartes cohérentes**
   avec la connexion. Conserver la logique de `DashboardController`.

## DESIGN SYSTEM (à centraliser)
- Palette UCAO (primaire, accent, succès/alerte/danger), neutres, fond clair & sombre.
- Typo lisible, hiérarchie titres/sous-titres, tailles cohérentes.
- Composants : `.ucao-card`, `.ucao-btn`, badges d'état, champ de formulaire,
  en-tête de page — définis une fois (CSS dans les assets ou `<style>` partagé) et
  utilisés par connexion ET dashboard.
- **Mode sombre** : réutiliser le toggle existant (`ucaoToggleTheme`) et faire en
  sorte que les deux écrans le respectent via les mêmes variables.

## PAGE DE CONNEXION (auth)
- Mise en page deux temps : panneau de marque (gauche/haut) + carte formulaire.
- Champs login + mot de passe, captcha arithmétique conservé, bouton « Se connecter »
  pleine largeur, lien « Mot de passe oublié ».
- États : focus visibles, erreurs de validation claires, message si identifiants
  invalides. Aucune fuite d'identifiants de démo.
- Performant : pas de framework JS lourd, pas d'image lourde ; SVG/icônes Bootstrap.

## TABLEAU DE BORD ÉTUDIANT
- **En-tête** : « Bonjour {prénom} » + filière/niveau + matricule, et le sélecteur de
  thème.
- **Bandeau d'aperçu** (cartes synthèse, données réelles) :
  - Solde restant `Etudiant::soldeReel()` (CTA « Payer » mis en avant si > 0),
  - Moyenne générale (`moyenne()`),
  - Prochaine séance (EDT du jour / à venir) + cours en ligne en cours,
  - Prochaine échéance de travail (`Projet`) / copies à rendre.
- **Modules** : reprendre les entrées de `DashboardController` (Role::Etudiant) en
  cartes homogènes (icône, libellé, badge), grille responsive, état highlight pour
  le paiement conservé.
- Accessibilité : contrastes AA, navigation clavier, `aria-label` sur les actions,
  responsive mobile→desktop.

## CONTRAINTES & QUALITÉ
- Réutiliser/raffiner `layouts/app` (assets, thème) ; ne pas dupliquer le CSS :
  un seul fichier/section de styles partagée.
- Ne touche pas à la logique (auth, captcha, rôles, paiement, routes). Les données
  du bandeau viennent des models existants, sans N+1.
- UI 100 % française. Formatter avec `vendor/bin/pint`. `view:clear` après refonte.

## TESTS / VÉRIFICATION
- La page de connexion s'affiche (200), le login fonctionne toujours (test auth
  existant vert), le captcha reste requis.
- Le dashboard étudiant s'affiche (200) et montre le bon solde/moyenne pour un
  étudiant seedé (ex. `etudiant1`).
- Lancer `php artisan test` (suite au vert, hors `RegistrationTest` désactivé) + Pint.

## LIVRABLES & DoD
- [ ] Design system commun centralisé (couleurs/typo/composants, clair+sombre).
- [ ] Page de connexion refondue, cohérente avec le dashboard, captcha conservé.
- [ ] Dashboard étudiant : en-tête + bandeau d'aperçu (données réelles) + cartes.
- [ ] Responsive + accessible, aucune régression, tests verts, Pint, `view:clear`.
- [ ] `HANDOFF.md` mis à jour.

## CONTRAINTES DE PÉRIMÈTRE
- Ne change QUE la connexion et le tableau de bord étudiant (les autres rôles gardent
  l'affichage actuel, mais profitent du design system s'il est dans le layout commun).
- Commits/PR seulement si on te le demande.
