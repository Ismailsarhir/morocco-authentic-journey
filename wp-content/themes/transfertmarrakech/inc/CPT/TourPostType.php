<?php
/**
 * Custom Post Type : Tours
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

use TM\Meta\TourMeta;

/**
 * Classe pour gérer le CPT Tours
 */
class TourPostType extends PostType {
	
	/**
	 * Handler des meta boxes
	 * 
	 * @var TourMeta
	 */
	protected TourMeta $meta_handler;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct( 'tours' );
		$this->meta_handler = new TourMeta();
	}
	
	/**
	 * Retourne les labels du post type
	 * 
	 * @return array
	 */
	protected function get_labels(): array {
		return [
			'name'                  => _x( 'Tours', 'Post Type General Name', 'transfertmarrakech' ),
			'singular_name'         => _x( 'Tour', 'Post Type Singular Name', 'transfertmarrakech' ),
			'menu_name'             => __( 'Tours', 'transfertmarrakech' ),
			'name_admin_bar'        => __( 'Tour', 'transfertmarrakech' ),
			'archives'              => __( 'Archives des tours', 'transfertmarrakech' ),
			'attributes'            => __( 'Attributs du tour', 'transfertmarrakech' ),
			'parent_item_colon'     => __( 'Tour parent:', 'transfertmarrakech' ),
			'all_items'             => __( 'Tous les tours', 'transfertmarrakech' ),
			'add_new_item'          => __( 'Ajouter un nouveau tour', 'transfertmarrakech' ),
			'add_new'               => __( 'Ajouter nouveau', 'transfertmarrakech' ),
			'new_item'              => __( 'Nouveau tour', 'transfertmarrakech' ),
			'edit_item'             => __( 'Modifier le tour', 'transfertmarrakech' ),
			'update_item'           => __( 'Mettre à jour le tour', 'transfertmarrakech' ),
			'view_item'             => __( 'Voir le tour', 'transfertmarrakech' ),
			'view_items'            => __( 'Voir les tours', 'transfertmarrakech' ),
			'search_items'          => __( 'Rechercher un tour', 'transfertmarrakech' ),
			'not_found'             => __( 'Aucun tour trouvé', 'transfertmarrakech' ),
			'not_found_in_trash'    => __( 'Aucun tour trouvé dans la corbeille', 'transfertmarrakech' ),
			'featured_image'        => __( 'Image du tour', 'transfertmarrakech' ),
			'set_featured_image'    => __( 'Définir l\'image du tour', 'transfertmarrakech' ),
			'remove_featured_image' => __( 'Supprimer l\'image du tour', 'transfertmarrakech' ),
			'use_featured_image'    => __( 'Utiliser comme image du tour', 'transfertmarrakech' ),
			'insert_into_item'      => __( 'Insérer dans le tour', 'transfertmarrakech' ),
			'uploaded_to_this_item' => __( 'Téléversé vers ce tour', 'transfertmarrakech' ),
			'items_list'            => __( 'Liste des tours', 'transfertmarrakech' ),
			'items_list_navigation' => __( 'Navigation de la liste des tours', 'transfertmarrakech' ),
			'filter_items_list'     => __( 'Filtrer la liste des tours', 'transfertmarrakech' ),
		];
	}
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * 
	 * @return array
	 */
	protected function get_args(): array {
		return [
			'label'                 => __( 'Tour', 'transfertmarrakech' ),
			'description'           => __( 'Tours et excursions disponibles', 'transfertmarrakech' ),
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'taxonomies'            => [ 'tour_location' ],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 21,
			'menu_icon'             => 'dashicons-palmtree',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'tours',
			'rewrite'               => [
				'slug'       => 'tours',
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
			'tour_location',
			[ $this->post_type ],
			[
				'labels'            => [
					'name'          => __( 'Localisations', 'transfertmarrakech' ),
					'singular_name' => __( 'Localisation', 'transfertmarrakech' ),
					'menu_name'     => __( 'Localisations', 'transfertmarrakech' ),
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
		// Localisation (string)
		\register_post_meta(
			$this->post_type,
			'tm_location',
			[
				'type'              => 'string',
				'description'       => __( 'Localisation du tour', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Durée (string) - Temps de route vers la destination
		\register_post_meta(
			$this->post_type,
			'tm_duration',
			[
				'type'              => 'string',
				'description'       => __( 'Temps de route vers la destination', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Durée en minutes (integer) - Nombre de jours du tour
		\register_post_meta(
			$this->post_type,
			'tm_duration_minutes',
			[
				'type'              => 'integer',
				'description'       => __( 'Nombre de jours du tour', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Prix (string pour décimal)
		\register_post_meta(
			$this->post_type,
			'tm_price',
			[
				'type'              => 'string',
				'description'       => __( 'Prix du tour', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_price' ],
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Véhicules associés (array d'IDs)
		\register_post_meta(
			$this->post_type,
			'tm_vehicles',
			[
				'type'              => 'array',
				'description'       => __( 'Véhicules disponibles pour ce tour', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_post_ids' ],
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
		
		// Points forts (text)
		\register_post_meta(
			$this->post_type,
			'tm_highlights',
			[
				'type'              => 'string',
				'description'       => __( 'Points forts du tour', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Point de rendez-vous (string)
		\register_post_meta(
			$this->post_type,
			'tm_meeting_point',
			[
				'type'              => 'string',
				'description'       => __( 'Point de rendez-vous', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
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

