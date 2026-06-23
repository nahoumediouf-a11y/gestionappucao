# Cahier des charges UI/UX — SIGE UCAO

Concevoir une interface web moderne, professionnelle et épurée pour une application
universitaire nommée SIGE UCAO (Système Intégré de Gestion de l'École).

> État d'avancement : **phase 1 livrée** (design system + shell sidebar/topbar +
> toasts, cf. HANDOFF §5.10). Phases suivantes listées en bas.

## Style général
- Design inspiré des meilleures applications SaaS modernes.
- Interface minimaliste, élégante et rapide.
- Bootstrap 5. Responsive (mobile, tablette, ordinateur). Mode clair et sombre.
- Animations fluides et discrètes. Coins arrondis (16–20px). Ombres légères.
- Espacement généreux. Icônes Bootstrap Icons. Aucune surcharge visuelle.

## Palette de couleurs
- Principale : Bleu institutionnel `#2563EB`
- Secondaires : Vert succès `#10B981`, Orange avertissement `#F59E0B`, Rouge erreur `#EF4444`
- Fond : Blanc cassé `#F8FAFC` — Texte : Gris foncé `#1E293B`

## Page de connexion
Page premium. À gauche : illustration éducation numérique, logo SIGE UCAO, slogan
« Simplifier la gestion académique et financière. ». À droite : carte centrée,
champs identifiant + mot de passe (afficher/masquer), se souvenir de moi, mot de
passe oublié, bouton connexion (hover moderne), message d'erreur élégant, animation
d'apparition. (Adapté au projet : connexion par **login + captcha** et par espace.)

## Dashboard
Barre latérale : Tableau de bord, Étudiants, Professeurs, Comptabilité, Recouvrement,
Paiements, Emplois du temps, Documents, Messagerie, Paramètres. Icônes modernes,
menu rétractable, effet actif, animation douce.
Barre supérieure : recherche globale, notifications, messages, profil, sélecteur de thème.

Cartes statistiques : nombre d'étudiants, de professeurs, paiements du mois, taux de
recouvrement — chacune avec icône, valeur, variation mensuelle, mini graphique.

Graphiques (Chart.js) : évolution des paiements, répartition des étudiants,
statistiques des absences, recouvrements mensuels.

Tableaux modernes : recherche instantanée, pagination, tri, filtres avancés,
export PDF, export Excel.

Notifications : système moderne (succès, erreur, information, avertissement) avec
toasts élégants.

## Expérience utilisateur
Rapidité, simplicité, lisibilité, accessibilité, peu de clics. Qualité comparable à
Notion, Stripe, Linear, Figma — logiciel professionnel haut de gamme 2026.

---

## Avancement
- [x] **Phase 1** : design tokens (palette/rayons/ombres), shell sidebar + topbar
      (recherche, notifications, thème, profil), toasts, responsive, mode sombre.
- [ ] Phase 2 : cartes statistiques + mini-graphes + graphiques Chart.js (dashboard).
- [ ] Phase 3 : tableaux avancés (tri / filtres / export PDF & Excel) réutilisables.
- [ ] Phase 4 : messagerie interne.
- [ ] Phase 5 : recherche globale cross-modules.
