# Pack de correctifs — Facturation SaaS

Ce zip contient uniquement les fichiers **modifiés ou créés** (même arborescence
que votre projet : copiez-les par-dessus, ou appliquez `CHANGES.patch` avec
`git apply CHANGES.patch` depuis la racine du repo). `EmailVerifier.php`
n'est pas inclus : il était déjà modifié dans votre zip d'origine,
indépendamment de ce travail.

## 1. Sécurité (issues #80 à #84)
- `config/packages/security.yaml` : ajout de `ROLE_USER` sur `/client`,
  `/invoice`, `/product`, `/mail` (routes non protégées du tout auparavant).
- `ClientController.php` : IDOR corrigé — chaque utilisateur ne voit/modifie
  plus que **ses propres** clients (c'était `findAll()` avant, sans filtre).
- `InvoiceController.php` + `templates/invoice/show.html.twig` : jeton CSRF
  ajouté sur `app_invoice_validate` et `app_invoice_paid`.
- `ProductController.php`, `MailController.php` : `#[IsGranted('ROLE_USER')]`.

## 2. Conformité / légal
- `LegalController.php` + `templates/legal/{privacy,terms}.html.twig` :
  pages CGU et politique de confidentialité. **⚠️ Complétez les variables
  placeholder dans `config/packages/twig.yaml`** (raison sociale, SIRET,
  hébergeur...) et faites relire par un juriste avant mise en prod.
- `components/CookieConsent.html.twig` + `cookie_consent_controller.js` :
  bandeau de consentement cookies (localStorage, RGPD).
- `HttpsRedirectSubscriber.php` + `framework.yaml` : HTTPS forcé en
  production + en-tête HSTS (aucun effet en dev).

## 3. SEO
- `base.html.twig` : meta title/description par page, Open Graph, Twitter
  Card, favicon réel (`public/favicon.*`, `apple-touch-icon.png`).
- `public/img/og-image.jpg` : image de partage réseaux sociaux (placeholder
  à vos couleurs, à remplacer par votre vrai visuel de marque).
- `SeoController.php` : `/robots.txt` et `/sitemap.xml` générés
  dynamiquement à partir des routes publiques réelles.
- `templates/bundles/TwigBundle/Exception/error{404,403}.html.twig` : pages
  d'erreur personnalisées (actives uniquement quand `debug: false`).

## 4. Bugs fonctionnels trouvés et corrigés
- **Liens de suppression cassés** : les boutons supprimer de `Client` et
  `Product` étaient des `<a href>` GET vers des routes POST protégées CSRF —
  ils ne fonctionnaient tout simplement pas. Remplacés par des formulaires
  POST avec jeton CSRF (factorisés dans `_row_actions.html.twig`).
- **Icône SVG corrompue** dans `invoice/index.html.twig` (caractères
  Unicode pleine largeur mélangés dans un path) → icône invisible. Corrigée.
- **`</div>` surnuméraire** dans `invoice/index.html.twig` qui fermait la
  mise en page avant `</main>` (balisage invalide). Retiré.
- **Balises `<body>` imbriquées** sur login/logout/register/user (HTML
  invalide) → remplacées par des `<div>`.
- **Email non unique** en base (`User.email` n'avait aucune contrainte
  d'unicité !) → migration `Version20260919120000.php` ajoutée.

## 5. Validation des formulaires
- Contraintes `Assert` ajoutées sur `User`, `Client`, `Product` (email,
  champs requis, IBAN, SIRET, prix positif...) — il n'y en avait **aucune**
  avant.
- Affichage des erreurs (`form_errors`) ajouté sur tous les formulaires
  (client, produit, inscription, profil) : les erreurs de validation
  étaient calculées mais **jamais affichées à l'utilisateur**.

## 6. Anti-spam
- `RegistrationFormType.php` : champ honeypot invisible (`Assert\Blank`).
- `RegistrationController.php` : délai minimum de soumission (anti-bot) +
  limite de tentatives par IP (cache Symfony, pas de dépendance externe).

## 7. Accessibilité (équivalent "texte alternatif")
Le site n'utilise aucune balise `<img>` (tout est en SVG inline) : le texte
alternatif ici, c'est `aria-label`/`aria-hidden` sur les icônes. Ajoutés sur
toutes les icônes d'action (voir/modifier/supprimer) dans client, produit,
facture.

## 8. Contraste des couleurs
`text-gray-400` (ratio ~2.85:1, sous le seuil WCAG AA 4.5:1) remplacé par
`text-gray-500` (~4.6:1) sur tous les textes de contenu réel (messages
d'état vide, dates, emails, liens légaux...).

## 9. Responsive
- **Menu latéral** : entièrement fixe (224px, toujours visible) avant →
  devient un tiroir mobile (hamburger, fond assombri, fermeture au clic sur
  un lien) via `sidebar_controller.js`, visible en permanence dès `md:`.
- Conteneurs de pages : `flex` → `flex flex-col md:flex-row` (13 fichiers).
- Login/inscription/profil : largeur fixe `w-[480px]` (débordait sous
  480px) → `w-full max-w-[480px]` + padding horizontal.
- Dashboard : grille de stats `grid-cols-4` fixe → `grid-cols-1 sm:2 lg:4` ;
  graphique mensuel (12 barres) → défilement horizontal sur petit écran.
- Tableaux clients/produits/factures : wrapper `overflow-x-auto` ajouté
  (au lieu de casser la mise en page sur mobile).

## 10. CTA unique
Dashboard : 4 cartes d'action de même poids visuel (dont une non
fonctionnelle, "Relancer tous les clients", en `href="#"` et sans feature
derrière) → 1 CTA principal clair ("Créer une facture") + 2 raccourcis
secondaires sobres et fonctionnels. Les liens `href="#"` cassés ont été
soit corrigés (Ajouter un client/produit), soit retirés (relance clients,
qui n'était pas implémentée).

## 11. Vitesse de chargement
Analyse statique (pas d'environnement Lighthouse disponible ici) :
Tailwind compilé (pas de CDN), 46 icônes en SVG inline (0 requête réseau),
image OG 76 Ko chargée uniquement par les réseaux sociaux. Point mineur
observé : `font-family: 'Arimo'` est déclaré dans `app.css` mais cette
police n'est chargée nulle part (ni Google Fonts, ni fichier local) — le
navigateur retombe silencieusement sur la police système. Sans impact sur
la vitesse, mais autant le savoir si vous vouliez vraiment Arimo.

## Ce qui reste à faire de votre côté
- Remplacer les placeholders légaux (raison sociale, SIRET, hébergeur...).
- Remplacer `og-image.jpg` et les favicons par votre vraie identité visuelle
  si "FacturSaas" n'est qu'un nom de travail.
- Renseigner `GA_MEASUREMENT_ID` dans `.env` si vous voulez activer
  l'analytics (désactivé par défaut).
- Vérifier qu'il n'existe pas de doublons d'e-mail en base avant d'exécuter
  la migration d'unicité.
- Tester le tiroir mobile et la validation de formulaires en conditions
  réelles (je n'ai pas pu lancer l'app dans cet environnement — pas de PHP
  ni de base de données disponibles ici).
