<?php
/**
 * Custom Post Type : Transferts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

use TM\Core\Constants;
use TM\Meta\TransferMeta;

/**
 * Classe pour gérer le CPT Transferts
 */
class TransferPostType extends PostType {
	
	/**
	 * Handler des meta boxes
	 * 
	 * @var TransferMeta
	 */
	protected TransferMeta $meta_handler;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct( Constants::POST_TYPE_TRANSFER );
		$this->meta_handler = new TransferMeta();
	}
	
	/**
	 * Retourne les labels du post type
	 * 
	 * @return array
	 */
	protected function get_labels(): array {
		return [
			'name'                  => _x( 'Transferts', 'Post Type General Name', 'transfertmarrakech' ),
			'singular_name'         => _x( 'Transfert', 'Post Type Singular Name', 'transfertmarrakech' ),
			'menu_name'             => __( 'Transferts', 'transfertmarrakech' ),
			'name_admin_bar'        => __( 'Transfert', 'transfertmarrakech' ),
			'archives'              => __( 'Archives des transferts', 'transfertmarrakech' ),
			'attributes'            => __( 'Attributs du transfert', 'transfertmarrakech' ),
			'parent_item_colon'     => __( 'Transfert parent:', 'transfertmarrakech' ),
			'all_items'             => __( 'Tous les transferts', 'transfertmarrakech' ),
			'add_new_item'          => __( 'Ajouter un nouveau transfert', 'transfertmarrakech' ),
			'add_new'               => __( 'Ajouter nouveau', 'transfertmarrakech' ),
			'new_item'              => __( 'Nouveau transfert', 'transfertmarrakech' ),
			'edit_item'             => __( 'Modifier le transfert', 'transfertmarrakech' ),
			'update_item'           => __( 'Mettre à jour le transfert', 'transfertmarrakech' ),
			'view_item'             => __( 'Voir le transfert', 'transfertmarrakech' ),
			'view_items'            => __( 'Voir les transferts', 'transfertmarrakech' ),
			'search_items'          => __( 'Rechercher un transfert', 'transfertmarrakech' ),
			'not_found'             => __( 'Aucun transfert trouvé', 'transfertmarrakech' ),
			'not_found_in_trash'    => __( 'Aucun transfert trouvé dans la corbeille', 'transfertmarrakech' ),
			'featured_image'        => __( 'Image du transfert', 'transfertmarrakech' ),
			'set_featured_image'    => __( 'Définir l\'image du transfert', 'transfertmarrakech' ),
			'remove_featured_image' => __( 'Supprimer l\'image du transfert', 'transfertmarrakech' ),
			'use_featured_image'    => __( 'Utiliser comme image du transfert', 'transfertmarrakech' ),
			'insert_into_item'      => __( 'Insérer dans le transfert', 'transfertmarrakech' ),
			'uploaded_to_this_item' => __( 'Téléversé vers ce transfert', 'transfertmarrakech' ),
			'items_list'            => __( 'Liste des transferts', 'transfertmarrakech' ),
			'items_list_navigation' => __( 'Navigation de la liste des transferts', 'transfertmarrakech' ),
			'filter_items_list'     => __( 'Filtrer la liste des transferts', 'transfertmarrakech' ),
		];
	}
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * 
	 * @return array
	 */
	protected function get_args(): array {
		return [
			'label'                 => __( 'Transfert', 'transfertmarrakech' ),
			'description'           => __( 'Services de transfert aéroport et autres', 'transfertmarrakech' ),
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'taxonomies'            => [ 'transfer_type' ],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 22,
			'menu_icon'             => 'dashicons-airplane',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'transferts',
			'rewrite'               => [
				'slug'       => 'transferts',
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
			'transfer_type',
			[ $this->post_type ],
			[
				'labels'            => [
					'name'          => __( 'Types de transfert', 'transfertmarrakech' ),
					'singular_name' => __( 'Type de transfert', 'transfertmarrakech' ),
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
		// Type de transfert (string)
		\register_post_meta(
			$this->post_type,
			'tm_transfer_type',
			[
				'type'              => 'string',
				'description'       => __( 'Type de transfert', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
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
				'description'       => __( 'Prix du transfert', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => [ 'TM\Utils\Sanitizer', 'sanitize_price' ],
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Point de prise en charge (string)
		\register_post_meta(
			$this->post_type,
			'tm_pickup',
			[
				'type'              => 'string',
				'description'       => __( 'Point de prise en charge', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Point de dépose (string)
		\register_post_meta(
			$this->post_type,
			'tm_dropoff',
			[
				'type'              => 'string',
				'description'       => __( 'Point de dépose', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Estimation de durée (string)
		\register_post_meta(
			$this->post_type,
			'tm_duration_estimate',
			[
				'type'              => 'string',
				'description'       => __( 'Estimation de durée', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_text_field',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_posts' );
				},
				'show_in_rest'      => true,
			]
		);
		
		// Description (text)
		\register_post_meta(
			$this->post_type,
			'tm_description',
			[
				'type'              => 'string',
				'description'       => __( 'Description détaillée', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'sanitize_textarea_field',
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

