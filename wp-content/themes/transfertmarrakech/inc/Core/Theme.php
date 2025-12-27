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
use TM\REST\VehicleRestController;
use TM\REST\TourRestController;
use TM\REST\TransferRestController;
use TM\Shortcodes\ShortcodeManager;
use TM\Meta\PostMeta;
use TM\Meta\TermMeta;
use TM\Admin\FeaturedTextSettings;

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
		$post_types = [ 'vehicules', 'tours', 'transferts' ];
		
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
		$post_types = [ 'vehicules', 'tours', 'transferts' ];
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
		$post_types = [ 'vehicules', 'tours', 'transferts' ];
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

