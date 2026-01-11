<?php
/**
 * Custom Post Type : Véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

use TM\Core\Constants;
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
		parent::__construct( Constants::POST_TYPE_VEHICLE );
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
			'menu_name'             => __( 'Vehicles', 'transfertmarrakech' ),
			'name_admin_bar'        => __( 'Vehicle', 'transfertmarrakech' ),
			'archives'              => __( 'Vehicle Archives', 'transfertmarrakech' ),
			'attributes'            => __( 'Vehicle Attributes', 'transfertmarrakech' ),
			'parent_item_colon'     => __( 'Parent Vehicle:', 'transfertmarrakech' ),
			'all_items'             => __( 'All Vehicles', 'transfertmarrakech' ),
			'add_new_item'          => __( 'Add New Vehicle', 'transfertmarrakech' ),
			'add_new'               => __( 'Add New', 'transfertmarrakech' ),
			'new_item'              => __( 'New Vehicle', 'transfertmarrakech' ),
			'edit_item'             => __( 'Edit Vehicle', 'transfertmarrakech' ),
			'update_item'           => __( 'Update Vehicle', 'transfertmarrakech' ),
			'view_item'             => __( 'View Vehicle', 'transfertmarrakech' ),
			'view_items'            => __( 'View Vehicles', 'transfertmarrakech' ),
			'search_items'          => __( 'Search Vehicles', 'transfertmarrakech' ),
			'not_found'             => __( 'No vehicles found', 'transfertmarrakech' ),
			'not_found_in_trash'    => __( 'No vehicles found in Trash', 'transfertmarrakech' ),
			'featured_image'        => __( 'Vehicle Image', 'transfertmarrakech' ),
			'set_featured_image'    => __( 'Set vehicle image', 'transfertmarrakech' ),
			'remove_featured_image' => __( 'Remove vehicle image', 'transfertmarrakech' ),
			'use_featured_image'    => __( 'Use as vehicle image', 'transfertmarrakech' ),
			'insert_into_item'      => __( 'Insert into vehicle', 'transfertmarrakech' ),
			'uploaded_to_this_item' => __( 'Uploaded to this vehicle', 'transfertmarrakech' ),
			'items_list'            => __( 'Vehicles list', 'transfertmarrakech' ),
			'items_list_navigation' => __( 'Vehicles list navigation', 'transfertmarrakech' ),
			'filter_items_list'     => __( 'Filter vehicles list', 'transfertmarrakech' ),
		];
	}
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * 
	 * @return array
	 */
	protected function get_args(): array {
		return [
			'label'                 => __( 'Vehicle', 'transfertmarrakech' ),
			'description'           => __( 'Vehicles available for transfers and tours', 'transfertmarrakech' ),
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
					'name'          => __( 'Vehicle Types', 'transfertmarrakech' ),
					'singular_name' => __( 'Vehicle Type', 'transfertmarrakech' ),
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
				'description'       => __( 'Vehicle type', 'transfertmarrakech' ),
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
				'description'       => __( 'Number of seats', 'transfertmarrakech' ),
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
				'description'       => __( 'Baggage capacity', 'transfertmarrakech' ),
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
				'description'       => __( 'Image gallery', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_gallery' ],
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => [
					'schema' => [
						'items' => [
							'type' => 'integer',
						],
					],
				],
			]
		);
		
		// Disponibilité (boolean)
		\register_post_meta(
			$this->post_type,
			'tm_availability',
			[
				'type'              => 'boolean',
				'description'       => __( 'Availability', 'transfertmarrakech' ),
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
				'description'       => __( 'Daily price', 'transfertmarrakech' ),
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

