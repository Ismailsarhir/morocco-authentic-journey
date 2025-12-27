<?php
/**
 * Meta Box pour les Véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

/**
 * Classe pour gérer les meta boxes des véhicules
 */
class VehicleMeta extends MetaBox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct(
			'tm_vehicle_meta',
			__( 'Informations du véhicule', 'transfertmarrakech' ),
			'vehicules'
		);
	}
	
	/**
	 * Affiche le contenu de la meta box
	 * 
	 * @param WP_Post $post Objet post
	 * @return void
	 */
	public function render( $post ): void {
		$this->nonce_field();
		
		// Utilise le helper pour récupérer toutes les meta en une fois (plus efficace)
		$meta = \TM\Utils\MetaHelper::get_vehicle_meta( $post->ID );
		
		$type            = $meta['tm_vehicle_type'] ?? '';
		$seats           = $meta['tm_seats'] ?? 0;
		$baggage_capacity = $meta['tm_baggage_capacity'] ?? '';
		$gallery_ids     = $meta['tm_gallery'] ?? [];
		$availability    = $meta['tm_availability'] ?? false;
		$daily_price     = $meta['tm_daily_price'] ?? '';
		
		// Type de véhicule
		$type_options = [
			'van'     => __( 'Van', 'transfertmarrakech' ),
			'4x4'     => __( '4x4', 'transfertmarrakech' ),
			'minibus' => __( 'Minibus', 'transfertmarrakech' ),
		];
		$this->select_field( 'tm_vehicle_type', __( 'Type de véhicule', 'transfertmarrakech' ), $type_options, $type );
		
		// Nombre de places
		$this->number_field( 'tm_seats', __( 'Nombre de places', 'transfertmarrakech' ), $seats );
		
		// Capacité bagages
		$this->text_field( 'tm_baggage_capacity', __( 'Capacité bagages', 'transfertmarrakech' ), $baggage_capacity, __( 'Ex: 3 valises', 'transfertmarrakech' ) );
		
		// Galerie
		$this->gallery_field( 'tm_gallery', __( 'Galerie d\'images', 'transfertmarrakech' ), $gallery_ids );
		
		// Disponibilité
		$this->checkbox_field( 'tm_availability', __( 'Disponible', 'transfertmarrakech' ), (bool) $availability );
		
		// Prix journalier
		$this->text_field( 'tm_daily_price', __( 'Prix journalier (MAD)', 'transfertmarrakech' ), $daily_price, '0.00' );
	}
	
	/**
	 * Sauvegarde les données de la meta box
	 * 
	 * @param int $post_id ID du post
	 * @return void
	 */
	public function save( int $post_id ): void {
		// Vérifications de sécurité
		if ( ! $this->verify_nonce() ) {
			return;
		}
		
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Type de véhicule
		if ( isset( $_POST['tm_vehicle_type'] ) ) {
			\update_post_meta( $post_id, 'tm_vehicle_type', \sanitize_text_field( $_POST['tm_vehicle_type'] ) );
		}
		
		// Nombre de places
		if ( isset( $_POST['tm_seats'] ) ) {
			\update_post_meta( $post_id, 'tm_seats', \absint( $_POST['tm_seats'] ) );
		}
		
		// Capacité bagages
		if ( isset( $_POST['tm_baggage_capacity'] ) ) {
			\update_post_meta( $post_id, 'tm_baggage_capacity', \sanitize_text_field( $_POST['tm_baggage_capacity'] ) );
		}
		
		// Galerie
		if ( isset( $_POST['tm_gallery'] ) ) {
			$gallery_string = \sanitize_text_field( $_POST['tm_gallery'] );
			$gallery_ids = ! empty( $gallery_string ) ? array_map( 'absint', explode( ',', $gallery_string ) ) : [];
			\update_post_meta( $post_id, 'tm_gallery', $gallery_ids );
		} else {
			\update_post_meta( $post_id, 'tm_gallery', [] );
		}
		
		// Disponibilité
		$availability = isset( $_POST['tm_availability'] ) && $_POST['tm_availability'] === '1';
		\update_post_meta( $post_id, 'tm_availability', $availability );
		
		// Prix journalier
		if ( isset( $_POST['tm_daily_price'] ) ) {
			$price = \floatval( $_POST['tm_daily_price'] );
			\update_post_meta( $post_id, 'tm_daily_price', \number_format( $price, 2, '.', '' ) );
		}
	}
}

