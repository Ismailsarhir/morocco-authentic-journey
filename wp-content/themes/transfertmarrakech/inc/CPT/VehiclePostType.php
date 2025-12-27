<?php
/**
 * Custom Post Type : Véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

use TM\Meta\VehicleMeta;

/**
 * Classe pour gérer le CPT Véhicules
 */
class VehiclePostType extends PostType {
	
	/**
	 * Handler des meta boxes
	 * 
	 * @var VehicleMeta
	 */
	protected VehicleMeta $meta_handler;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct( 'vehicules' );
		$this->meta_handler = new VehicleMeta();
	}
	
	/**
	 * Retourne les labels du post type
	 * 
	 * @return array
	 */
	protected function get_labels(): array {
		return [
			'name'                  => _x( 'Véhicules', 'Post Type General Name', 'transfertmarrakech' ),
			'singular_name'         => _x( 'Véhicule', 'Post Type Singular Name', 'transfertmarrakech' ),
			'menu_name'             => __( 'Véhicules', 'transfertmarrakech' ),
			'name_admin_bar'        => __( 'Véhicule', 'transfertmarrakech' ),
			'archives'              => __( 'Archives des véhicules', 'transfertmarrakech' ),
			'attributes'            => __( 'Attributs du véhicule', 'transfertmarrakech' ),
			'parent_item_colon'     => __( 'Véhicule parent:', 'transfertmarrakech' ),
			'all_items'             => __( 'Tous les véhicules', 'transfertmarrakech' ),
			'add_new_item'          => __( 'Ajouter un nouveau véhicule', 'transfertmarrakech' ),
			'add_new'               => __( 'Ajouter nouveau', 'transfertmarrakech' ),
			'new_item'              => __( 'Nouveau véhicule', 'transfertmarrakech' ),
			'edit_item'             => __( 'Modifier le véhicule', 'transfertmarrakech' ),
			'update_item'           => __( 'Mettre à jour le véhicule', 'transfertmarrakech' ),
			'view_item'             => __( 'Voir le véhicule', 'transfertmarrakech' ),
			'view_items'            => __( 'Voir les véhicules', 'transfertmarrakech' ),
			'search_items'          => __( 'Rechercher un véhicule', 'transfertmarrakech' ),
			'not_found'             => __( 'Aucun véhicule trouvé', 'transfertmarrakech' ),
			'not_found_in_trash'    => __( 'Aucun véhicule trouvé dans la corbeille', 'transfertmarrakech' ),
			'featured_image'        => __( 'Image du véhicule', 'transfertmarrakech' ),
			'set_featured_image'    => __( 'Définir l\'image du véhicule', 'transfertmarrakech' ),
			'remove_featured_image' => __( 'Supprimer l\'image du véhicule', 'transfertmarrakech' ),
			'use_featured_image'    => __( 'Utiliser comme image du véhicule', 'transfertmarrakech' ),
			'insert_into_item'      => __( 'Insérer dans le véhicule', 'transfertmarrakech' ),
			'uploaded_to_this_item' => __( 'Téléversé vers ce véhicule', 'transfertmarrakech' ),
			'items_list'            => __( 'Liste des véhicules', 'transfertmarrakech' ),
			'items_list_navigation' => __( 'Navigation de la liste des véhicules', 'transfertmarrakech' ),
			'filter_items_list'     => __( 'Filtrer la liste des véhicules', 'transfertmarrakech' ),
		];
	}
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * 
	 * @return array
	 */
	protected function get_args(): array {
		return [
			'label'                 => __( 'Véhicule', 'transfertmarrakech' ),
			'description'           => __( 'Véhicules disponibles pour les transferts et tours', 'transfertmarrakech' ),
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'taxonomies'            => [ 'vehicle_type' ],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 20,
			'menu_icon'             => 'dashicons-car',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'vehicules',
			'rewrite'               => [
				'slug'       => 'vehicules',
				'with_front' => false, // Enlève le préfixe du permalink (comme /blog/)
				'feeds'      => true,
				'pages'      => true,
			],
		];
	}
	
	/**
	 * Enregistre les taxonomies associées
	 * 
	 * @return void
	 */
	protected function register_taxonomies(): void {
		\register_taxonomy(
			'vehicle_type',
			[ $this->post_type ],
			[
				'labels'            => [
					'name'          => __( 'Types de véhicules', 'transfertmarrakech' ),
					'singular_name' => __( 'Type de véhicule', 'transfertmarrakech' ),
					'menu_name'     => __( 'Types', 'transfertmarrakech' ),
				],
				'hierarchical'      => true,
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'show_tagcloud'     => true,
				'show_in_rest'      => true,
			]
		);
	}
	
	/**
	 * Enregistre les champs meta
	 * 
	 * @return void
	 */
	protected function register_meta_fields(): void {
		// Type de véhicule (string)
		\register_post_meta(
			$this->post_type,
			'tm_vehicle_type',
			[
				'type'              => 'string',
				'description'       => __( 'Type de véhicule', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Nombre de places (integer)
		\register_post_meta(
			$this->post_type,
			'tm_seats',
			[
				'type'              => 'integer',
				'description'       => __( 'Nombre de places', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Capacité bagages (string)
		\register_post_meta(
			$this->post_type,
			'tm_baggage_capacity',
			[
				'type'              => 'string',
				'description'       => __( 'Capacité bagages', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Galerie (array d'IDs)
		\register_post_meta(
			$this->post_type,
			'tm_gallery',
			[
				'type'              => 'array',
				'description'       => __( 'Galerie d\'images', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_gallery' ],
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Disponibilité (boolean)
		\register_post_meta(
			$this->post_type,
			'tm_availability',
			[
				'type'              => 'boolean',
				'description'       => __( 'Disponibilité', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'rest_sanitize_boolean',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Prix journalier (string pour décimal)
		\register_post_meta(
			$this->post_type,
			'tm_daily_price',
			[
				'type'              => 'string',
				'description'       => __( 'Prix journalier', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_price' ],
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
	}
	
	/**
	 * Enregistre les meta boxes
	 * 
	 * @return void
	 */
	protected function register_meta_boxes(): void {
		$this->meta_handler->register();
	}
	
	/**
	 * Sauvegarde les meta données
	 * 
	 * @param int     $post_id ID du post
	 * @param WP_Post $post    Objet post
	 * @return void
	 */
	public function save_meta( int $post_id, $post ): void {
		parent::save_meta( $post_id, $post );
		$this->meta_handler->save( $post_id );
	}
}

