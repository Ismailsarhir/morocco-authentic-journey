<?php
/**
 * Classe principale du thème - Bootstrap
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\CPT\VehiclePostType;
use TM\CPT\TourPostType;
use TM\CPT\TransferPostType;
use TM\CPT\CircuitPostType;
use TM\REST\VehicleRestController;
use TM\REST\TourRestController;
use TM\REST\TransferRestController;
use TM\Shortcodes\ShortcodeManager;
use TM\Meta\PostMeta;
use TM\Meta\TermMeta;
use TM\Admin\FeaturedTextSettings;
use TM\Admin\WhatsAppSettings;
use TM\Admin\ArchiveToursSettings;
use TM\Admin\ArchiveCircuitsSettings;
use TM\Admin\ArchiveTransfersSettings;

/**
 * Classe principale du thème qui initialise tous les composants
 */
class Theme {
	
	/**
	 * Instance unique du thème (Singleton)
	 * 
	 * @var Theme|null
	 */
	private static ?Theme $instance = null;
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		// Empêche l'instanciation directe
	}
	
	/**
	 * Récupère l'instance unique du thème
	 * 
	 * @return Theme
	 */
	public static function get_instance(): Theme {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Initialise le thème et tous ses composants
	 * 
	 * @return void
	 */
	public static function init(): void {
		$theme = self::get_instance();
		$theme->register_hooks();
		$theme->init_cpt();
		$theme->init_rest();
		$theme->init_shortcodes();
		$theme->init_css_injector();
		$theme->init_js_injector();
		$theme->init_meta_boxes();
		$theme->init_admin_pages();
		$theme->init_pagination();
		$theme->init_search();
	}
	
	/**
	 * Enregistre les hooks WordPress
	 * 
	 * @return void
	 */
	private function register_hooks(): void {
		// Hooks pour l'initialisation
		\add_action( 'after_setup_theme', [ $this, 'add_theme_supports' ] );
		\add_action( 'init', [ $this, 'load_textdomain' ] );
		\add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
		
		// Force l'affichage de l'image à la une
		\add_action( 'admin_init', [ $this, 'add_featured_image_support' ] );
		\add_action( 'add_meta_boxes', [ $this, 'ensure_featured_image_meta_box' ], 999 );
		
		// Remplace la classe 'search' par 'search-body' sur la page de recherche
		\add_filter( 'body_class', [ $this, 'modify_search_body_class' ], 10, 2 );
	}
	
	/**
	 * Modifie la classe du body pour la page de recherche
	 * Remplace 'search' par 'search-body' pour éviter les conflits CSS
	 * Optimisé avec vérification précoce
	 * 
	 * @param array $classes Classes du body
	 * @param array $class   Classes additionnelles
	 * @return array
	 */
	public function modify_search_body_class( array $classes, array $class ): array {
		// Vérification précoce
		if ( ! is_search() ) {
			return $classes;
		}
		
		// Remplace 'search' par 'search-body'
		$key = array_search( 'search', $classes, true );
		if ( $key !== false ) {
			$classes[ $key ] = 'search-body';
		}
		
		return $classes;
	}
	
	/**
	 * Ajoute les supports de thème nécessaires
	 * 
	 * @return void
	 */
	public function add_theme_supports(): void {
		// Active le support des images à la une pour tous les post types
		\add_theme_support( 'post-thumbnails' );
		
		// Enregistre les emplacements de menu
		\register_nav_menus( [
			'main-header' => __( 'Menu Principal Header', 'transfertmarrakech' ),
			'footer-quick-links' => __( 'Footer - Liens rapides', 'transfertmarrakech' ),
			'footer-social-links' => __( 'Footer - Suivez-nous', 'transfertmarrakech' ),
		] );
	}
	
	/**
	 * Charge le domaine de traduction
	 * 
	 * @return void
	 */
	public function load_textdomain(): void {
		\load_theme_textdomain( 'transfertmarrakech', \get_template_directory() . '/languages' );
	}
	
	/**
	 * Force l'affichage de l'image à la une dans les options d'écran
	 * 
	 * @return void
	 */
	public function add_featured_image_support(): void {
		$post_types = [ Constants::POST_TYPE_VEHICLE, Constants::POST_TYPE_TOUR, Constants::POST_TYPE_CIRCUIT, Constants::POST_TYPE_TRANSFER ];
		
		foreach ( $post_types as $post_type ) {
			// S'assure que le support thumbnail est activé (déjà fait dans register_post_type, mais on double-vérifie)
			if ( ! \post_type_supports( $post_type, 'thumbnail' ) ) {
				\add_post_type_support( $post_type, 'thumbnail' );
			}
		}
		
		// Force l'activation de l'option dans les options d'écran pour tous les post types
		\add_filter( 'default_hidden_meta_boxes', function( $hidden, $screen ) use ( $post_types ) {
			if ( isset( $screen->post_type ) && \in_array( $screen->post_type, $post_types, true ) ) {
				// Retire 'postimagediv' de la liste des meta boxes cachées
				$key = \array_search( 'postimagediv', $hidden, true );
				if ( $key !== false ) {
					unset( $hidden[ $key ] );
				}
			}
			return $hidden;
		}, 10, 2 );
	}
	
	/**
	 * S'assure que la meta box Image à la une est bien ajoutée
	 * Hook appelé très tard (priorité 999) pour s'assurer que WordPress a déjà enregistré ses meta boxes
	 * 
	 * @return void
	 */
	public function ensure_featured_image_meta_box(): void {
		$post_types = [ Constants::POST_TYPE_VEHICLE, Constants::POST_TYPE_TOUR, Constants::POST_TYPE_CIRCUIT, Constants::POST_TYPE_TRANSFER ];
		$screen = \get_current_screen();
		
		if ( ! $screen || ! isset( $screen->post_type ) ) {
			return;
		}
		
		if ( ! \in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}
		
		// S'assure que le support thumbnail est activé
		if ( ! \post_type_supports( $screen->post_type, 'thumbnail' ) ) {
			\add_post_type_support( $screen->post_type, 'thumbnail' );
		}
		
		// Vérifie si la meta box existe déjà dans les meta boxes enregistrées
		global $wp_meta_boxes;
		$meta_box_exists = false;
		
		if ( isset( $wp_meta_boxes[ $screen->post_type ] ) ) {
			// Vérifie dans tous les contextes et priorités
			foreach ( [ 'side', 'normal', 'advanced' ] as $context ) {
				if ( isset( $wp_meta_boxes[ $screen->post_type ][ $context ] ) ) {
					foreach ( [ 'default', 'high', 'low' ] as $priority ) {
						if ( isset( $wp_meta_boxes[ $screen->post_type ][ $context ][ $priority ]['postimagediv'] ) ) {
							$meta_box_exists = true;
							break 2;
						}
					}
				}
			}
		}
		
		// Si elle n'existe pas, on l'ajoute manuellement
		// WordPress devrait normalement l'ajouter automatiquement, mais on s'assure qu'elle existe
		if ( ! $meta_box_exists ) {
			\add_meta_box(
				'postimagediv',
				\__( 'Image à la une', 'transfertmarrakech' ),
				'post_thumbnail_meta_box',
				$screen->post_type,
				'side',
				'default'
			);
		}
	}
	
	
	/**
	 * Enregistre les scripts et styles admin
	 * 
	 * @param string $hook_suffix Hook suffix de la page admin
	 * @return void
	 */
	public function enqueue_admin_scripts( string $hook_suffix ): void {
		// Charge uniquement sur les pages d'édition des CPT
		$post_types = [ Constants::POST_TYPE_VEHICLE, Constants::POST_TYPE_TOUR, Constants::POST_TYPE_CIRCUIT, Constants::POST_TYPE_TRANSFER ];
		$screen = \get_current_screen();
		
		if ( ! $screen || ! isset( $screen->post_type ) ) {
			return;
		}
		
		if ( ! \in_array( $screen->post_type, $post_types, true ) ) {
			return;
		}
		
		// Charge les scripts WordPress nécessaires pour le sélecteur de médias
		\wp_enqueue_media();
		
		// Charge le script JavaScript pour la gestion de la galerie
		\wp_enqueue_script(
			'tm-admin-gallery',
			\get_template_directory_uri() . '/assets/js/admin-gallery.js',
			[ 'jquery', 'media-upload', 'media-views' ],
			'1.0.0',
			true
		);
		
		// Script pour forcer l'affichage de la meta box Image à la une
		\wp_add_inline_script( 'jquery', '
			jQuery(document).ready(function($) {
				// Force l\'affichage de la meta box Image à la une
				var $featuredImageBox = $("#postimagediv");
				if ($featuredImageBox.length === 0) {
					setTimeout(function() {
						$featuredImageBox = $("#postimagediv");
					}, 500);
				} else {
					$featuredImageBox.show();
				}
				
				// Vérifie les options d\'écran et coche "Image à la une" si nécessaire
				$("#screen-options-link-wrap").on("click", function() {
					setTimeout(function() {
						var $checkbox = $("#postimagediv-hide");
						if ($checkbox.length && $checkbox.is(":checked")) {
							$checkbox.prop("checked", false);
						}
					}, 100);
				});
				
				
			});
		' );
	}
	
	/**
	 * Enregistre les scripts et styles
	 * 
	 * @return void
	 */
	public function enqueue_scripts(): void {
		// Les scripts JS sont maintenant gérés par Separated_Js_Injector
		// On garde seulement la localisation AJAX pour main.js si nécessaire
		// ou on peut la déplacer dans Separated_Js_Injector si besoin
		// Swiper est maintenant importé via npm dans global.js
	}
	
	/**
	 * Initialise les Custom Post Types
	 * 
	 * @return void
	 */
	private function init_cpt(): void {
		$vehicle_cpt = new VehiclePostType();
		$vehicle_cpt->register();
		
		$tour_cpt = new TourPostType();
		$tour_cpt->register();
		
		$circuit_cpt = new CircuitPostType();
		$circuit_cpt->register();
		
		$transfer_cpt = new TransferPostType();
		$transfer_cpt->register();
	}
	
	/**
	 * Initialise les contrôleurs REST
	 * 
	 * @return void
	 */
	private function init_rest(): void {
		\add_action( 'rest_api_init', function() {
			$vehicle_rest = new VehicleRestController();
			$vehicle_rest->register_routes();
			
			$tour_rest = new TourRestController();
			$tour_rest->register_routes();
			
			$transfer_rest = new TransferRestController();
			$transfer_rest->register_routes();
		} );
	}
	
	/**
	 * Initialise les shortcodes
	 * 
	 * @return void
	 */
	private function init_shortcodes(): void {
		$shortcode_manager = new ShortcodeManager();
		$shortcode_manager->register_all();
	}
	

	/**
	 * Initialise l'injecteur de CSS séparé
	 * 
	 * @return void
	 */
	private function init_css_injector(): void {
		Separated_Css_Injector::instance();
	}

	/**
	 * Initialise l'injecteur de JS séparé
	 * 
	 * @return void
	 */
	private function init_js_injector(): void {
		Separated_Js_Injector::instance();
	}
	
	/**
	 * Initialise les meta boxes
	 * 
	 * @return void
	 */
	private function init_meta_boxes(): void {
		$post_meta = new PostMeta();
		$post_meta->register();
		
		// Enregistre le hook de sauvegarde
		\add_action( 'save_post', [ $post_meta, 'save' ] );
		
		// Enregistre les meta fields pour les posts
		$this->register_post_meta_fields();
		
		// Initialise les meta fields pour les termes tour_location
		$term_meta = new TermMeta();
		$term_meta->register();
	}
	
	/**
	 * Initialise les pages d'administration
	 * 
	 * @return void
	 */
	private function init_admin_pages(): void {
		$featured_text_settings = new FeaturedTextSettings();
		$featured_text_settings->register();
		
		$whatsapp_settings = new WhatsAppSettings();
		$whatsapp_settings->register();
		
		$archive_tours_settings = new ArchiveToursSettings();
		$archive_tours_settings->register();
		
		$archive_circuits_settings = new ArchiveCircuitsSettings();
		$archive_circuits_settings->register();
		
		$archive_transfers_settings = new ArchiveTransfersSettings();
		$archive_transfers_settings->register();
	}
	
	/**
	 * Configure la pagination pour l'archive des tours
	 * 
	 * @return void
	 */
	private function init_pagination(): void {
		// Configure la pagination pour l'archive des tours (1 post par page)
		\add_action( 'pre_get_posts', [ $this, 'configure_archive_tours_pagination' ] );
		// Configure la pagination pour l'archive des circuits (1 post par page)
		\add_action( 'pre_get_posts', [ $this, 'configure_archive_circuits_pagination' ] );
		// Configure la pagination pour l'archive des transferts (1 post par page)
		\add_action( 'pre_get_posts', [ $this, 'configure_archive_transferts_pagination' ] );
	}
	
	/**
	 * Initialise la fonctionnalité de recherche
	 * 
	 * @return void
	 */
	private function init_search(): void {
		// Inclut les post types personnalisés dans la recherche
		\add_action( 'pre_get_posts', [ $this, 'modify_search_query' ] );
		// Ajoute la recherche dans les champs personnalisés (meta)
		\add_filter( 'posts_search', [ $this, 'extend_search_to_meta' ], 10, 2 );
	}
	
	/**
	 * Configure la pagination pour l'archive des tours (1 post par page)
	 * Optimisé : vérifications précoces
	 * 
	 * @param \WP_Query $query Requête WordPress
	 * @return void
	 */
	public function configure_archive_tours_pagination( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'tours' ) ) {
			return;
		}
		
		$query->set( 'posts_per_page', 9 );
		$query->set( 'post_status', 'publish' );
		
		// Filtre pour n'inclure que les posts avec une image à la une (évite les pages vides)
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = [];
		}
		$meta_query[] = [
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		];
		$query->set( 'meta_query', $meta_query );
		
		$query->set( 'update_post_meta_cache', true );
		$query->set( 'update_post_term_cache', true );
		$query->set( 'no_found_rows', false ); // Nécessaire pour calculer correctement max_num_pages
	}
	
	/**
	 * Configure la pagination pour l'archive des circuits (1 post par page)
	 * Optimisé : vérifications précoces
	 * 
	 * @param \WP_Query $query Requête WordPress
	 * @return void
	 */
	public function configure_archive_circuits_pagination( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'circuits' ) ) {
			return;
		}
		
		$query->set( 'posts_per_page', 9 );
		$query->set( 'post_status', 'publish' );
		
		// Filtre pour n'inclure que les posts avec une image à la une (évite les pages vides)
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = [];
		}
		$meta_query[] = [
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		];
		$query->set( 'meta_query', $meta_query );
		
		$query->set( 'update_post_meta_cache', true );
		$query->set( 'update_post_term_cache', true );
		$query->set( 'no_found_rows', false ); // Nécessaire pour calculer correctement max_num_pages
	}
	
	/**
	 * Configure la pagination pour l'archive des transferts (1 post par page)
	 * Optimisé : vérifications précoces
	 * 
	 * @param \WP_Query $query Requête WordPress
	 * @return void
	 */
	public function configure_archive_transferts_pagination( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! is_post_type_archive( 'transferts' ) ) {
			return;
		}
		
		$query->set( 'posts_per_page', 9 );
		$query->set( 'post_status', 'publish' );
		
		// Filtre pour n'inclure que les posts avec une image à la une (évite les pages vides)
		$meta_query = $query->get( 'meta_query' );
		if ( ! is_array( $meta_query ) ) {
			$meta_query = [];
		}
		$meta_query[] = [
			'key'     => '_thumbnail_id',
			'compare' => 'EXISTS',
		];
		$query->set( 'meta_query', $meta_query );
		
		$query->set( 'update_post_meta_cache', true );
		$query->set( 'update_post_term_cache', true );
		$query->set( 'no_found_rows', false ); // Nécessaire pour calculer correctement max_num_pages
	}
	
	/**
	 * Modifie la requête de recherche pour inclure les post types personnalisés
	 * Optimisé avec vérifications précoces
	 * 
	 * @param \WP_Query $query Requête WordPress
	 * @return void
	 */
	public function modify_search_query( \WP_Query $query ): void {
		// Vérifications précoces pour éviter les traitements inutiles
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_search() ) {
			return;
		}
		
		// Définit les post types à rechercher
		$query->set( 'post_type', [
			Constants::POST_TYPE_TOUR,
			Constants::POST_TYPE_CIRCUIT,
			Constants::POST_TYPE_TRANSFER,
		] );
		$query->set( 'post_status', 'publish' );
		
		// Optimisation : active les caches pour améliorer les performances
		$query->set( 'update_post_meta_cache', true );
		$query->set( 'update_post_term_cache', true );
	}
	
	/**
	 * Étend la recherche pour inclure les champs personnalisés (meta)
	 * Optimisé pour réduire les opérations SQL et améliorer les performances
	 * 
	 * @param string    $search   Clause de recherche SQL
	 * @param \WP_Query $wp_query Objet de requête WordPress
	 * @return string Clause de recherche modifiée
	 */
	public function extend_search_to_meta( string $search, \WP_Query $wp_query ): string {
		// Vérifications précoces pour éviter les traitements inutiles
		if ( is_admin() || ! $wp_query->is_main_query() || ! $wp_query->is_search() ) {
			return $search;
		}
		
		// Récupère le terme de recherche
		$search_term = $wp_query->get( 's' );
		if ( empty( $search_term ) || strlen( trim( $search_term ) ) < 2 ) {
			return $search;
		}
		
		global $wpdb;
		
		// Échappe le terme de recherche pour la sécurité SQL
		$escaped_search_term = '%' . $wpdb->esc_like( $search_term ) . '%';
		
		// Liste des clés meta à rechercher (définie une seule fois)
		$meta_keys = [
			// Meta pour Tours
			Constants::META_TOUR_LOCATION,
			Constants::META_TOUR_MEETING_POINT,
			Constants::META_TOUR_HIGHLIGHTS,
			// Meta pour Circuits
			Constants::META_CIRCUIT_LOCATION,
			Constants::META_CIRCUIT_MEETING_POINT,
			Constants::META_CIRCUIT_HIGHLIGHTS,
			// Meta pour Transfers
			Constants::META_TRANSFER_PICKUP,
			Constants::META_TRANSFER_DROPOFF,
			Constants::META_TRANSFER_DESCRIPTION,
		];
		
		// Construit la clause OR pour rechercher dans les meta
		// Échappe chaque clé meta pour SQL
		$escaped_meta_keys = array_map( function( $key ) use ( $wpdb ) {
			return '\'' . esc_sql( $key ) . '\'';
		}, $meta_keys );
		
		$meta_keys_list = implode( ',', $escaped_meta_keys );
		
		$meta_search = $wpdb->prepare( "
			OR EXISTS (
				SELECT 1 
				FROM {$wpdb->postmeta} 
				WHERE {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID 
				AND {$wpdb->postmeta}.meta_key IN ($meta_keys_list)
				AND {$wpdb->postmeta}.meta_value LIKE %s
			)
		", $escaped_search_term );
		
		// Ajoute la recherche dans les meta à la clause de recherche existante
		if ( ! empty( $search ) ) {
			// Extrait le contenu entre les parenthèses de la clause WordPress
			$search = trim( $search );
			
			// Enlève le "AND " au début si présent
			if ( strpos( $search, 'AND ' ) === 0 ) {
				$search = substr( $search, 4 );
			}
			
			// Enlève les parenthèses externes
			$search = trim( $search );
			if ( preg_match( '/^\(\((.*?)\)\)\s*$/s', $search, $matches ) ) {
				// Format: ((content))
				$inner_content = trim( $matches[1] );
				$search = 'AND ((' . $inner_content . ') ' . trim( $meta_search ) . ')';
			} elseif ( preg_match( '/^\((.*?)\)\s*$/s', $search, $matches ) ) {
				// Format: (content)
				$inner_content = trim( $matches[1] );
				$search = 'AND ((' . $inner_content . ') ' . trim( $meta_search ) . ')';
			} else {
				// Format inattendu, combine directement
				$search = 'AND ((' . $search . ') ' . trim( $meta_search ) . ')';
			}
		} else {
			// Si pas de recherche dans le contenu, on crée juste la recherche meta
			$search = 'AND ' . trim( $meta_search );
		}
		
		return $search;
	}
	
	/**
	 * Enregistre les champs meta pour les posts
	 * 
	 * @return void
	 */
	private function register_post_meta_fields(): void {
		// Show in Hero (boolean)
		\register_post_meta(
			'post',
			'tm_show_in_hero',
			[
				'type'              => 'boolean',
				'description'       => __( 'Afficher ce post dans le Hero', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Hero Video URL (string)
		\register_post_meta(
			'post',
			'tm_hero_video_url',
			[
				'type'              => 'string',
				'description'       => __( 'URL de la vidéo YouTube pour le Hero', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'esc_url_raw',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
	}
}

