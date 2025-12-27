# Core

## Introduction
Le **thème Core** est un thème WordPress modulaire et extensible, conçu pour être **structuré, maintenable et évolutif**.
Il sépare clairement la logique, la configuration et les templates, facilitant ainsi la personnalisation et la réutilisation du code.

---

## 📁 Structure du répertoire

```bash
core/
├── assets/
│ ├── css/
│ │ ├── home.css
│ │ └── ...
│ ├── fonts/
│ │ ├── roboto/
│ │ │ ├── roboto.woff2
│ │ └── poppins/
│ ├── images/
│ ├── js/
│ │ ├── components/
│ │ │ ├── header.js
│ │ │ └── ...
│ │ ├── archive-test.js
│ │ ├── category.js
│ │ └── ...
│ ├── sass/
│ │ ├── libs/
│ │ │ ├── bootstrap-grid/
│ │ ├── pages/
│ │ │ ├── archive/
│ │ │ │ ├── roboto.woff2
│ │ │ ├── home/
│ │ │ └── ...
│ │ ├── utils/
│ │ ├── global.scss
│ │ └── ...
├── include/
│ ├── classes/
│ │ ├── Asset/
│ │ │ ├── CssInjector.php
│ │ │ └── JsInjector.php
│ │ │
│ │ ├── DataProvider/
│ │ │ └── Home.php
│ │ │
│ │ ├── Hook/
│ │ │ ├── Back/
│ │ │ │ ├── Action.php
│ │ │ │ └── Filter.php
│ │ │ │
│ │ │ ├── Front/
│ │ │ │ ├── Action.php
│ │ │ │ └── Filter.php
│ │ │ │
│ │ │ ├── Action.php
│ │ │ └── Filter.php
│ │ │
│ │ ├── MetaBox/
│ │ │ └── AbstractMetaBox.php
│ │ │
│ │ ├── Shortcode/
│ │ │ └── Shortcode.php
│ │ │
│ │ ├── Trait/
│ │ │ └── UseSingleton.php
│ │ │
│ │ ├── Autoloader.php
│ │ ├── Bootstrap.php
│ │ └── CdnCacheVersion.php
│ │
│ ├── configs/
│ │ ├── boot-config.php
│ │ ├── css-config.php
│ │ ├── js-config.php
│ │ └── site-config.php
│ │
│ ├── functions/
│ │ └── functions.php
│ │
│ └── templates/
│   └── admin/
│
├── functions.php
├── index.php
└── style.css

```

## ⚙️ Présentation des dossiers principaux

### **📁 classes/**
Contient toutes les classes PHP du thème.
Chaque sous-dossier regroupe une famille de fonctionnalités.

- **Asset/** – Gère l’enregistrement et le chargement des fichiers CSS et JS.
- **DataProvider/** – Fournit et formate les données utilisées dans les templates.
- **Hook/** – Enregistre et gère les hooks des actions et filtres (Back / Front).
- **MetaBox/** – Définit les métaboxes personnalisées pour les articles et pages.
- **Shortcode/** – Contient tous les shortcodes du thème.
- **Trait/** – Contient des traits réutilisables dans plusieurs classes.
- **Autoloader.php** – Charge automatiquement les classes PHP.
- **Bootstrap.php** – Initialise les composants du thème.
- **CdnCacheVersion.php** – Gère les versions de cache pour les ressources CSS et JS.

---

### **📁 configs/**
Fichiers de configuration du thème.
Ils centralisent les paramètres utilisés au démarrage et la gestion des assets.

- **boot-config.php** – Définit les classes à instantier lors du démarrage du thème (utilisé par la classe **Bootstrap.php**).
- **css-config.php** – Gère l’enregistrement et le chargement des feuilles de style (utilisé par la classe **CssInjector.php**).
- **js-config.php** – Gère l’enregistrement et le chargement des scripts JavaScript (utilisé par la classe **JsInjector.php**).
- **site-config.php** – Contient les paramètres globaux du thème.

---

### **📁 functions/**
Regroupe les fonctions PHP globales utiles dans tout le thème.

- **functions.php** – Fichier principal des fonctions utilitaires.

---

### **📁 templates/**
Contient les templates du front-end et de l’administration.

- **admin/** – Templates et composants destinés à l’admin.

---

### **📁 assets/**
Regroupe les ressources front-end du thème, depuis les sources jusqu'aux fichiers prêts à être servis.

- **css/** – Feuilles de style compilées, prêtes à être injectées via `CssInjector.php` suivant la configuration déclarée dans `include/configs/css-config.php`.
- **fonts/** – Emplacement réservé aux fontes web (WOFF2, WOFF).
- **images/** – Bibliothèque des médias statiques (SVG, PNG, JPG…) utilisés dans les templates.
- **js/** – Points d'entrée par template `home.js, default.js` et composants partagés dans `js/components/` chargés via `JsInjector.php`.
- **sass/** – Sources Sass modulaires. Les fichiers à la racine servent de points d'entrée compilés vers `css/`. Les sous-dossiers structurent les imports :
  - **libs/** pour centraliser les dépendances externes.
  - **pages/** pour organiser les styles spécifiques à chaque type de page.
  - **utils/** pour les variables, fonctions et mixins réutilisables.

---

## 🚀 Initialisation du thème

Le démarrage du thème se fait via le fichier **functions.php**, qui :

1. Enregistre l’autoloader pour charger automatiquement les classes.
2. Démarre le théme via la classe `Booststrap.php`.

---

## 🚀 Compilations Sass & JS

- **Prérequis** 
    – Node.js `>= 20.0.0`. Depuis `wp/wp-content/themes/core`, exécutez `npm install` pour récupérer les dépendances.
- **Sass**
    – `npm run sass-watch` lance le watcher de développement. Exécutez systématiquement `npm run sass-build` avant de committer pour appliquer `stylelint` et générer les CSS compressés.
- **JavaScript** 
    – `npm run js-watch` lance Webpack en mode développement.
    –`npm run js-build` produit les bundles optimisés.
- **Alternative conteneur** 
    – Si vous n'avez pas Node 20 en local : `docker exec -it reworldmedia-php-1 bash`, `cd wp-content/themes/core`, puis lancez les commandes npm ci-dessus depuis le conteneur PHP.

---

## 🧩 Notes pour les développeurs

- Respecter la convention **PSR-4** pour la déclaration des classes.
- Éviter de placer de la logique métier directement dans les templates.
- Centraliser les réglages et dépendances dans les fichiers de configuration.
- Étendre ou surcharger les fonctionnalités via les hooks ou un thème enfant.

---

## 👤 Collaborateurs et reviewers
Si vous avez des questions concernant cette structure ou tout autre sujet, n'hésitez pas à envoyer un message via Mattermost ou par e-mail aux développeurs concernés.

- **@youness_bouhou** : [bouhou@webpick.net](mailto:bouhou@webpick.net)
- **@brahim.ibrahimi** : [brahim.ibrahimi@webpick.net](mailto:brahim.ibrahimi@webpick.net)
- **@mohamed.yousfi** : [mohamed.yousfi@webpick.net](mailto:mohamed.yousfi@webpick.net)
