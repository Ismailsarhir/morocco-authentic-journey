==========================================
TRANSFERT MARRAKECH - DOCUMENTATION DÉVELOPPEMENT
==========================================

VERSION: 1.0.0
AUTEUR: Transfert Marrakech
LICENCE: GPL v2 ou supérieure
REQUIREMENTS: WordPress 5.0+, PHP 8.0+

==========================================
ARCHITECTURE POO
==========================================

Ce thème utilise une architecture orientée objet (POO) moderne avec :
- Namespaces PHP (TM\)
- Autoloading PSR-4 simplifié
- Pattern Repository pour les requêtes
- Pattern Singleton pour certaines classes
- Séparation stricte des responsabilités
- Code réutilisable et maintenable

==========================================
STRUCTURE COMPLÈTE DES DOSSIERS
==========================================

transfertmarrakech/
├── inc/
│   ├── Autoloader.php              # Autoloader PSR-4 pour namespace TM\
│   ├── Core/
│   │   └── Theme.php               # Bootstrap principal (Singleton)
│   ├── CPT/
│   │   ├── PostType.php            # Classe abstraite base pour tous les CPT
│   │   ├── VehiclePostType.php     # CPT Véhicules
│   │   ├── TourPostType.php        # CPT Tours
│   │   └── TransferPostType.php    # CPT Transferts
│   ├── Meta/
│   │   ├── MetaBox.php             # Classe abstraite meta box
│   │   ├── VehicleMeta.php         # Meta box véhicules
│   │   ├── TourMeta.php            # Meta box tours
│   │   └── TransferMeta.php        # Meta box transferts
│   ├── REST/
│   │   ├── VehicleRestController.php  # REST API pour véhicules
│   │   ├── TourRestController.php     # REST API pour tours
│   │   └── TransferRestController.php  # REST API pour transferts
│   ├── Repository/
│   │   └── PostRepository.php      # Repository pattern pour requêtes WP_Query
│   ├── Shortcodes/
│   │   ├── BaseShortcode.php       # Classe de base pour shortcodes (instances partagées)
│   │   ├── ShortcodeManager.php   # Gestionnaire d'enregistrement
│   │   ├── VehicleShortcode.php   # Shortcode [tm_vehicules]
│   │   ├── TourShortcode.php      # Shortcode [tm_tours]
│   │   └── TransferShortcode.php   # Shortcode [tm_transferts]
│   ├── Ajax/
│   │   └── AjaxHandlers.php        # Handlers AJAX (booking, contact)
│   ├── Template/
│   │   └── Renderer.php            # Helper pour rendre les template parts
│   └── Utils/
│       └── Sanitizer.php           # Classe utilitaire pour sanitization
├── template-parts/
│   ├── loop-vehicle.php            # Template pour boucle véhicules
│   ├── loop-tour.php               # Template pour boucle tours
│   ├── loop-transfer.php           # Template pour boucle transferts
│   ├── single-vehicle.php          # Template single véhicule
│   ├── single-tour.php             # Template single tour
│   └── single-transfer.php         # Template single transfert
├── assets/
│   └── js/
│       └── main.js                 # Script AJAX principal (jQuery)
├── functions.php                   # Bootstrap WordPress
├── readme-dev.txt                  # Cette documentation
└── starter-data-sample.json        # Exemple de données pour tests

==========================================
INSTALLATION ET CONFIGURATION
==========================================

1. INSTALLATION:
   - Copier le dossier du thème dans wp-content/themes/
   - Activer le thème dans l'administration WordPress (Apparence > Thèmes)
   - Les Custom Post Types seront automatiquement enregistrés

2. CONFIGURATION INITIALE:
   - Les CPT sont prêts à l'emploi après activation
   - Les meta boxes apparaîtront automatiquement dans l'éditeur
   - Les endpoints REST sont disponibles immédiatement
   - Les shortcodes peuvent être utilisés dans n'importe quelle page

3. PREMIÈRES ÉTAPES:
   - Créer des véhicules (Véhicules > Ajouter)
   - Créer des tours (Tours > Ajouter)
   - Créer des transferts (Transferts > Ajouter)
   - Utiliser les shortcodes dans vos pages: [tm_vehicules], [tm_tours], [tm_transferts]

==========================================
ARCHITECTURE DÉTAILLÉE DES CLASSES
==========================================

CORE/Theme.php
--------------
Classe principale du thème (Singleton Pattern)
- Responsabilités:
  * Initialisation de tous les composants
  * Enregistrement des hooks WordPress
  * Chargement des scripts et styles
  * Orchestration de l'initialisation

Méthodes principales:
- init(): Point d'entrée statique
- register_hooks(): Enregistre les hooks WordPress
- enqueue_scripts(): Charge les assets JS/CSS
- init_cpt(): Initialise les Custom Post Types
- init_rest(): Initialise les contrôleurs REST
- init_shortcodes(): Initialise les shortcodes
- init_ajax(): Initialise les handlers AJAX

CPT/PostType.php (Classe abstraite)
-----------------------------------
Classe de base pour tous les Custom Post Types
- Responsabilités:
  * Enregistrement du post type WordPress
  * Enregistrement des taxonomies
  * Enregistrement des champs meta
  * Enregistrement des meta boxes
  * Gestion des hooks de sauvegarde

Méthodes à surcharger:
- get_labels(): Retourne les labels du post type
- get_args(): Retourne les arguments d'enregistrement
- register_taxonomies(): Enregistre les taxonomies
- register_meta_fields(): Enregistre les champs meta
- register_meta_boxes(): Enregistre les meta boxes
- save_meta(): Sauvegarde les meta données

CPT/VehiclePostType.php, TourPostType.php, TransferPostType.php
----------------------------------------------------------------
Implémentations concrètes des CPT
- Chaque classe:
  * Définit les labels spécifiques
  * Définit les arguments d'enregistrement
  * Enregistre les taxonomies associées
  * Enregistre les champs meta spécifiques
  * Instancie le handler de meta box correspondant

Meta/MetaBox.php (Classe abstraite)
-----------------------------------
Classe de base pour toutes les meta boxes
- Responsabilités:
  * Enregistrement de la meta box sur le hook 'add_meta_boxes'
  * Génération des champs de formulaire
  * Vérification des nonces
  * Sauvegarde des données

Méthodes utilitaires disponibles:
- text_field(): Champ texte
- number_field(): Champ nombre
- textarea_field(): Champ textarea
- select_field(): Champ select
- checkbox_field(): Champ checkbox
- gallery_field(): Champ galerie (IDs d'attachments)
- post_select_field(): Sélection multiple de posts

Meta/VehicleMeta.php, TourMeta.php, TransferMeta.php
-----------------------------------------------------
Implémentations concrètes des meta boxes
- Chaque classe:
  * Définit les champs spécifiques au CPT
  * Affiche les champs dans render()
  * Sauvegarde les données dans save()

REST/*RestController.php
------------------------
Contrôleurs REST API (héritent de WP_REST_Controller)
- Responsabilités:
  * Enregistrement des routes REST
  * Gestion des permissions
  * Validation et sanitization des données
  * Formatage des réponses

Méthodes principales:
- register_routes(): Enregistre les routes REST
- get_items(): Liste les éléments
- get_item(): Récupère un élément spécifique
- create_item(): Crée un nouvel élément
- update_item(): Met à jour un élément
- delete_item(): Supprime un élément (véhicules uniquement)
- prepare_item_for_response(): Formate les données pour la réponse

Repository/PostRepository.php
-----------------------------
Pattern Repository pour les requêtes de posts
- Responsabilités:
  * Centralisation des requêtes WP_Query
  * Méthodes réutilisables pour récupérer des posts
  * Formatage des données

Méthodes disponibles:
- get_by_args(): Récupère des posts par arguments
- get_by_id(): Récupère un post par ID
- get_related_vehicles_for_tour(): Véhicules liés à un tour
- get_available_vehicles(): Véhicules disponibles
- get_tours_by_location(): Tours par localisation
- get_transfers_by_type(): Transferts par type
- format_post(): Formate un post pour l'affichage

Shortcodes/BaseShortcode.php (Classe abstraite)
------------------------------------------------
Classe de base pour tous les shortcodes
- Optimisations:
  * Instances partagées de PostRepository et Renderer (singleton)
  * Méthode helper build_meta_query() pour construire les meta queries
  * Réduction de la consommation mémoire

Shortcodes/VehicleShortcode.php, TourShortcode.php, TransferShortcode.php
---------------------------------------------------------------------------
Implémentations concrètes des shortcodes
- Chaque classe:
  * Enregistre le shortcode WordPress
  * Parse les attributs
  * Construit les arguments de requête
  * Récupère les posts via Repository
  * Rend les templates via Renderer

Utils/Sanitizer.php
-------------------
Classe utilitaire statique pour la sanitization
- Méthodes disponibles:
  * sanitize_price(): Sanitize un prix décimal
  * sanitize_gallery(): Sanitize un array d'IDs d'attachments
  * sanitize_post_ids(): Sanitize un array d'IDs de posts
  * sanitize_positive_int(): Sanitize un entier positif
  * sanitize_boolean(): Sanitize un booléen

Template/Renderer.php
---------------------
Helper pour rendre les template parts
- Responsabilités:
  * Inclusion des fichiers template
  * Passage des données aux templates
  * Gestion des fallbacks

Ajax/AjaxHandlers.php
---------------------
Gestionnaire des requêtes AJAX
- Handlers disponibles:
  * tm_booking: Formulaires de réservation
  * tm_contact: Formulaires de contact
- Fonctionnalités:
  * Vérification des nonces
  * Validation des données
  * Envoi d'emails via wp_mail()
  * Réponses JSON formatées

==========================================
ENDPOINTS REST API
==========================================

Tous les endpoints sont sous le namespace: /wp-json/tm/v1/

VÉHICULES (/wp-json/tm/v1/vehicules)
------------------------------------
GET    /vehicules                    # Liste des véhicules
GET    /vehicules/{id}               # Détails d'un véhicule
POST   /vehicules                    # Créer un véhicule (auth requis)
PUT    /vehicules/{id}               # Modifier un véhicule (auth requis)
DELETE /vehicules/{id}               # Supprimer un véhicule (auth requis)

Paramètres GET /vehicules:
- per_page (int): Nombre d'éléments par page (défaut: 10)
- page (int): Numéro de page (défaut: 1)
- available (string): 'true' pour filtrer les disponibles

Exemple de réponse GET /vehicules/1:
{
  "id": 1,
  "title": "Mercedes Vito",
  "content": "...",
  "permalink": "http://...",
  "meta": {
    "vehicle_type": "van",
    "seats": 8,
    "baggage_capacity": "3 valises",
    "gallery": [123, 124],
    "availability": true,
    "daily_price": "500.00"
  }
}

TOURS (/wp-json/tm/v1/tours)
----------------------------
GET    /tours                        # Liste des tours
GET    /tours/{id}                   # Détails d'un tour
POST   /tours                        # Créer un tour (auth requis)
PUT    /tours/{id}                   # Modifier un tour (auth requis)

TRANSFERTS (/wp-json/tm/v1/transferts)
--------------------------------------
GET    /transferts                   # Liste des transferts
GET    /transferts/{id}              # Détails d'un transfert
POST   /transferts                   # Créer un transfert (auth requis)
PUT    /transferts/{id}              # Modifier un transfert (auth requis)

==========================================
SHORTCODES
==========================================

[tm_vehicules]
--------------
Affiche une liste de véhicules

Attributs:
- limit (int): Nombre de véhicules à afficher (-1 pour tous, défaut: -1)
- available (string): 'true' pour afficher uniquement les disponibles (défaut: 'false')
- type (string): Filtrer par type (van, 4x4, minibus)

Exemples:
[tm_vehicules]
[tm_vehicules limit="5"]
[tm_vehicules limit="5" available="true"]
[tm_vehicules type="van" available="true"]

Template utilisé: template-parts/loop-vehicle.php

[tm_tours]
----------
Affiche une liste de tours

Attributs:
- limit (int): Nombre de tours à afficher (-1 pour tous, défaut: -1)
- location (string): Filtrer par localisation

Exemples:
[tm_tours]
[tm_tours limit="3"]
[tm_tours location="Marrakech"]

Template utilisé: template-parts/loop-tour.php

[tm_transferts]
---------------
Affiche une liste de transferts

Attributs:
- limit (int): Nombre de transferts à afficher (-1 pour tous, défaut: -1)
- type (string): Filtrer par type (airport, hotel, city, custom)

Exemples:
[tm_transferts]
[tm_transferts limit="5"]
[tm_transferts type="airport"]

Template utilisé: template-parts/loop-transfer.php

==========================================
TESTS DES ENDPOINTS REST
==========================================

1. LISTER LES VÉHICULES:
   curl -X GET "http://votre-site.local/wp-json/tm/v1/vehicules"

2. LISTER AVEC FILTRES:
   curl -X GET "http://votre-site.local/wp-json/tm/v1/vehicules?available=true&per_page=5"

3. RÉCUPÉRER UN VÉHICULE:
   curl -X GET "http://votre-site.local/wp-json/tm/v1/vehicules/1"

4. CRÉER UN VÉHICULE (nécessite authentification):
   curl -X POST "http://votre-site.local/wp-json/tm/v1/vehicules" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer VOTRE_TOKEN" \
     -d '{
       "title": "Mercedes Vito",
       "content": "Description du véhicule",
       "tm_vehicle_type": "van",
       "tm_seats": 8,
       "tm_baggage_capacity": "3 valises",
       "tm_daily_price": "500.00",
       "tm_availability": true
     }'

5. MODIFIER UN VÉHICULE:
   curl -X PUT "http://votre-site.local/wp-json/tm/v1/vehicules/1" \
     -H "Content-Type: application/json" \
     -H "Authorization: Bearer VOTRE_TOKEN" \
     -d '{
       "tm_daily_price": "550.00"
     }'

6. SUPPRIMER UN VÉHICULE:
   curl -X DELETE "http://votre-site.local/wp-json/tm/v1/vehicules/1" \
     -H "Authorization: Bearer VOTRE_TOKEN"

==========================================
HANDLERS AJAX
==========================================

Deux handlers AJAX sont disponibles pour les formulaires frontend:

1. tm_booking (Réservations)
----------------------------
Action: wp_ajax_tm_booking / wp_ajax_nopriv_tm_booking

Champs requis:
- name (string): Nom du client
- email (string): Email du client
- phone (string): Téléphone du client
- service (string): Nom du service
- service_id (int): ID du service réservé
- date (string): Date de réservation
- message (string): Message optionnel

Nonce: tm_ajax_nonce

Exemple JavaScript:
jQuery.ajax({
  url: tmAjax.ajaxurl,
  type: 'POST',
  data: {
    action: 'tm_booking',
    nonce: tmAjax.nonce,
    name: 'John Doe',
    email: 'john@example.com',
    phone: '+212612345678',
    service: 'Tour désert',
    service_id: 123,
    date: '2024-12-25',
    message: 'Message optionnel'
  },
  success: function(response) {
    console.log(response);
  }
});

2. tm_contact (Contact)
-----------------------
Action: wp_ajax_tm_contact / wp_ajax_nopriv_tm_contact

Champs requis:
- name (string): Nom
- email (string): Email
- message (string): Message

Champs optionnels:
- subject (string): Sujet

Nonce: tm_ajax_nonce

==========================================
CHAMPS META DISPONIBLES
==========================================

VÉHICULES (post_type: vehicules)
--------------------------------
- tm_vehicle_type (string): Type de véhicule (van, 4x4, minibus)
- tm_seats (int): Nombre de places
- tm_baggage_capacity (string): Capacité bagages (ex: "3 valises")
- tm_gallery (array): IDs des images de la galerie
- tm_availability (bool): Disponibilité (true/false)
- tm_daily_price (string): Prix journalier formaté (ex: "500.00")

TOURS (post_type: tours)
------------------------
- tm_location (string): Localisation du tour
- tm_duration (string): Durée affichée (ex: "4h", "6h")
- tm_duration_minutes (int): Durée en minutes
- tm_price (string): Prix formaté (ex: "450.00")
- tm_vehicles (array): IDs des véhicules associés
- tm_highlights (string): Points forts du tour
- tm_meeting_point (string): Point de rendez-vous

TRANSFERTS (post_type: transferts)
----------------------------------
- tm_transfer_type (string): Type de transfert (airport, hotel, city, custom)
- tm_price (string): Prix formaté (ex: "150.00")
- tm_pickup (string): Point de prise en charge
- tm_dropoff (string): Point de dépose
- tm_duration_estimate (string): Estimation de durée (ex: "20 minutes")
- tm_description (string): Description détaillée

==========================================
TAXONOMIES
==========================================

vehicle_type (pour post_type: vehicules)
----------------------------------------
Taxonomie hiérarchique pour catégoriser les véhicules
- Termes suggérés: van, 4x4, minibus
- Accessible via REST API
- Utilisable dans les requêtes WP_Query

tour_location (pour post_type: tours)
--------------------------------------
Taxonomie hiérarchique pour les localisations
- Termes suggérés: Marrakech, Désert d'Agafay, Cascades d'Ouzoud
- Accessible via REST API

transfer_type (pour post_type: transferts)
-------------------------------------------
Taxonomie hiérarchique pour les types de transferts
- Termes suggérés: airport, hotel, city, custom
- Accessible via REST API

==========================================
SÉCURITÉ
==========================================

MESURES DE SÉCURITÉ IMPLÉMENTÉES:
----------------------------------
1. Sanitization:
   - Tous les inputs utilisateur sont sanitizés
   - Utilisation de sanitize_text_field(), sanitize_email(), etc.
   - Classe Sanitizer pour centraliser la sanitization

2. Validation:
   - Validation des emails avec is_email()
   - Validation des IDs avec absint()
   - Validation des types de données

3. Nonces:
   - Vérification des nonces pour tous les formulaires
   - Vérification des nonces pour les requêtes AJAX
   - Nonce: tm_ajax_nonce pour AJAX

4. Capabilities:
   - Vérification de current_user_can() pour toutes les actions d'écriture
   - Permissions requises: edit_posts, edit_post, delete_post

5. REST API:
   - Permission callbacks pour chaque endpoint
   - Lecture publique, écriture authentifiée
   - Validation et sanitization des paramètres

6. Échappement:
   - Utilisation de esc_html(), esc_attr(), esc_url() dans les templates
   - Utilisation de wp_kses_post() pour le contenu

==========================================
OPTIMISATIONS ET BONNES PRATIQUES
==========================================

ARCHITECTURE:
-------------
✓ Séparation stricte des responsabilités
✓ Pattern Repository pour les requêtes
✓ Pattern Singleton pour certaines classes
✓ Classes abstraites pour éviter la duplication
✓ Instances partagées pour Repository et Renderer dans shortcodes

CODE:
-----
✓ Namespaces PHP (TM\)
✓ Autoloading PSR-4 simplifié
✓ Types stricts PHP 8+ (propriétés typées)
✓ Commentaires en français pour la documentation
✓ Code DRY (Don't Repeat Yourself)

PERFORMANCE:
------------
✓ Instances partagées de Repository et Renderer
✓ Utilisation de la constante TM_VERSION pour le cache busting
✓ Classe Sanitizer statique (pas d'instanciation)
✓ Requêtes WP_Query optimisées

MAINTENABILITÉ:
---------------
✓ Code modulaire et réutilisable
✓ Méthodes courtes et focalisées
✓ Noms de classes et méthodes explicites
✓ Documentation complète

==========================================
EXTENSION DU THÈME
==========================================

AJOUTER UN NOUVEAU CPT:
-----------------------
1. Créer une classe dans inc/CPT/ qui hérite de PostType
2. Implémenter get_labels() et get_args()
3. Surcharger register_taxonomies() si nécessaire
4. Surcharger register_meta_fields() pour les champs meta
5. Créer une classe Meta dans inc/Meta/ qui hérite de MetaBox
6. Enregistrer le CPT dans Theme::init_cpt()

AJOUTER UN NOUVEAU SHORTCODE:
-----------------------------
1. Créer une classe dans inc/Shortcodes/ qui hérite de BaseShortcode
2. Implémenter register() et render()
3. Enregistrer dans ShortcodeManager::register_all()

AJOUTER UN NOUVEAU HANDLER AJAX:
--------------------------------
1. Ajouter la méthode dans AjaxHandlers
2. Enregistrer avec add_action('wp_ajax_...') et add_action('wp_ajax_nopriv_...')

==========================================
DÉBOGAGE
==========================================

ACTIVER LE MODE DÉBOGAGE:
--------------------------
Dans wp-config.php:
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

Les erreurs seront loggées dans wp-content/debug.log

PROBLÈMES COURANTS:
-------------------
1. Erreur "Call to undefined function":
   → Vérifier que les fonctions WordPress ont le préfixe \ dans les namespaces

2. Meta boxes non visibles:
   → Vérifier que le hook 'add_meta_boxes' est utilisé (pas d'appel direct à add_meta_box())

3. Shortcodes ne s'affichent pas:
   → Vérifier que les template parts existent dans template-parts/

4. REST API retourne 404:
   → Vérifier que les permalinks sont régénérés (Réglages > Permaliens > Enregistrer)

==========================================
VERSION PHP REQUISE
==========================================

PHP 8.0 ou supérieur

Fonctionnalités PHP utilisées:
- Types stricts (type hints)
- Propriétés typées (typed properties)
- Return types
- Nullable types (?Type)
- Namespaces
- Classes abstraites
- Traits (si nécessaire)

==========================================
LIENS UTILES
==========================================

Documentation WordPress:
- Custom Post Types: https://developer.wordpress.org/reference/functions/register_post_type/
- REST API: https://developer.wordpress.org/rest-api/
- Meta Boxes: https://developer.wordpress.org/reference/functions/add_meta_box/
- Shortcodes: https://developer.wordpress.org/reference/functions/add_shortcode/

==========================================
CHANGELOG
==========================================

Version 1.0.0 (2024)
--------------------
- Architecture POO complète
- 3 Custom Post Types (Véhicules, Tours, Transferts)
- REST API complète pour les 3 CPT
- Shortcodes pour affichage
- Handlers AJAX pour formulaires
- Classe Sanitizer pour centraliser la sanitization
- Classe BaseShortcode pour optimiser les shortcodes
- Documentation complète

==========================================
SUPPORT
==========================================

Pour toute question ou problème:
- Consulter cette documentation
- Vérifier les logs WordPress (wp-content/debug.log)
- Vérifier les logs serveur
- Tester les endpoints REST avec curl ou Postman
