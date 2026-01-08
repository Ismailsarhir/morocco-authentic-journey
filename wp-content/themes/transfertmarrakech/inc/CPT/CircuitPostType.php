<?php
/**
 * Custom Post Type : Circuits
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

use TM\Core\Constants;
use TM\Meta\CircuitMeta;

/**
 * Classe pour gérer le CPT Circuits
 */
class CircuitPostType extends PostType {
	
	/**
	 * Handler des meta boxes
	 * 
	 * @var CircuitMeta
	 */
	protected CircuitMeta $meta_handler;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct( Constants::POST_TYPE_CIRCUIT );
		$this->meta_handler = new CircuitMeta();
	}
	
	/**
	 * Retourne les labels du post type
	 * 
	 * @return array
	 */
	protected function get_labels(): array {
		return [
			'name'                  => _x( 'Circuits', 'Post Type General Name', 'transfertmarrakech' ),
			'singular_name'         => _x( 'Circuit', 'Post Type Singular Name', 'transfertmarrakech' ),
			'menu_name'             => __( 'Circuits', 'transfertmarrakech' ),
			'name_admin_bar'        => __( 'Circuit', 'transfertmarrakech' ),
			'archives'              => __( 'Archives des circuits', 'transfertmarrakech' ),
			'attributes'            => __( 'Attributs du circuit', 'transfertmarrakech' ),
			'parent_item_colon'     => __( 'Circuit parent:', 'transfertmarrakech' ),
			'all_items'             => __( 'Tous les circuits', 'transfertmarrakech' ),
			'add_new_item'          => __( 'Ajouter un nouveau circuit', 'transfertmarrakech' ),
			'add_new'               => __( 'Ajouter nouveau', 'transfertmarrakech' ),
			'new_item'              => __( 'Nouveau circuit', 'transfertmarrakech' ),
			'edit_item'             => __( 'Modifier le circuit', 'transfertmarrakech' ),
			'update_item'           => __( 'Mettre à jour le circuit', 'transfertmarrakech' ),
			'view_item'             => __( 'Voir le circuit', 'transfertmarrakech' ),
			'view_items'            => __( 'Voir les circuits', 'transfertmarrakech' ),
			'search_items'          => __( 'Rechercher un circuit', 'transfertmarrakech' ),
			'not_found'             => __( 'Aucun circuit trouvé', 'transfertmarrakech' ),
			'not_found_in_trash'    => __( 'Aucun circuit trouvé dans la corbeille', 'transfertmarrakech' ),
			'featured_image'        => __( 'Image du circuit', 'transfertmarrakech' ),
			'set_featured_image'    => __( 'Définir l\'image du circuit', 'transfertmarrakech' ),
			'remove_featured_image' => __( 'Supprimer l\'image du circuit', 'transfertmarrakech' ),
			'use_featured_image'    => __( 'Utiliser comme image du circuit', 'transfertmarrakech' ),
			'insert_into_item'      => __( 'Insérer dans le circuit', 'transfertmarrakech' ),
			'uploaded_to_this_item' => __( 'Téléversé vers ce circuit', 'transfertmarrakech' ),
			'items_list'            => __( 'Liste des circuits', 'transfertmarrakech' ),
			'items_list_navigation' => __( 'Navigation de la liste des circuits', 'transfertmarrakech' ),
			'filter_items_list'     => __( 'Filtrer la liste des circuits', 'transfertmarrakech' ),
		];
	}
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * 
	 * @return array
	 */
	protected function get_args(): array {
		return [
			'label'                 => __( 'Circuit', 'transfertmarrakech' ),
			'description'           => __( 'Circuits multi-jours disponibles', 'transfertmarrakech' ),
			'supports'              => [ 'title', 'editor', 'thumbnail', 'excerpt', 'custom-fields' ],
			'taxonomies'            => [],
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 22,
			'menu_icon'             => 'dashicons-location-alt',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'post',
			'show_in_rest'          => true,
			'rest_base'             => 'circuits',
			'rewrite'               => [
				'slug'       => 'circuits',
				'with_front' => false,
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
		// No taxonomies for now
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

