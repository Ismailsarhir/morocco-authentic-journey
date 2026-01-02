<?php
/**
 * Meta Box pour les Véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

use TM\Core\Constants;

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
			Constants::POST_TYPE_VEHICLE
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
		
		$type            = $meta[ Constants::META_VEHICLE_TYPE ] ?? '';
		$seats           = $meta[ Constants::META_VEHICLE_SEATS ] ?? 0;
		$baggage_capacity = $meta[ Constants::META_VEHICLE_BAGGAGE_CAPACITY ] ?? '';
		$gallery_ids     = $meta[ Constants::META_VEHICLE_GALLERY ] ?? [];
		$availability    = $meta[ Constants::META_VEHICLE_AVAILABILITY ] ?? false;
		$daily_price     = $meta[ Constants::META_VEHICLE_DAILY_PRICE ] ?? '';
		
		// Type de véhicule
		$type_options = [
			'van'     => __( 'Van', 'transfertmarrakech' ),
			'4x4'     => __( '4x4', 'transfertmarrakech' ),
			'minibus' => __( 'Minibus', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_VEHICLE_TYPE, __( 'Type de véhicule', 'transfertmarrakech' ), $type_options, $type );
		
		// Nombre de places
		$this->number_field( Constants::META_VEHICLE_SEATS, __( 'Nombre de places', 'transfertmarrakech' ), $seats );
		
		// Capacité bagages
		$this->text_field( Constants::META_VEHICLE_BAGGAGE_CAPACITY, __( 'Capacité bagages', 'transfertmarrakech' ), $baggage_capacity, __( 'Ex: 3 valises', 'transfertmarrakech' ) );
		
		// Galerie
		$this->gallery_field( Constants::META_VEHICLE_GALLERY, __( 'Galerie d\'images', 'transfertmarrakech' ), $gallery_ids );
		
		// Disponibilité
		$this->checkbox_field( Constants::META_VEHICLE_AVAILABILITY, __( 'Disponible', 'transfertmarrakech' ), (bool) $availability );
		
		// Prix journalier
		$this->text_field( Constants::META_VEHICLE_DAILY_PRICE, __( 'Prix journalier (MAD)', 'transfertmarrakech' ), $daily_price, '0.00' );
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
		if ( isset( $_POST[ Constants::META_VEHICLE_TYPE ] ) ) {
			\update_post_meta( $post_id, Constants::META_VEHICLE_TYPE, \sanitize_text_field( $_POST[ Constants::META_VEHICLE_TYPE ] ) );
		}
		
		// Nombre de places
		if ( isset( $_POST[ Constants::META_VEHICLE_SEATS ] ) ) {
			\update_post_meta( $post_id, Constants::META_VEHICLE_SEATS, \absint( $_POST[ Constants::META_VEHICLE_SEATS ] ) );
		}
		
		// Capacité bagages
		if ( isset( $_POST[ Constants::META_VEHICLE_BAGGAGE_CAPACITY ] ) ) {
			\update_post_meta( $post_id, Constants::META_VEHICLE_BAGGAGE_CAPACITY, \sanitize_text_field( $_POST[ Constants::META_VEHICLE_BAGGAGE_CAPACITY ] ) );
		}
		
		// Galerie
		if ( isset( $_POST[ Constants::META_VEHICLE_GALLERY ] ) ) {
			$gallery_string = \sanitize_text_field( $_POST[ Constants::META_VEHICLE_GALLERY ] );
			$gallery_ids = ! empty( $gallery_string ) ? array_map( 'absint', explode( ',', $gallery_string ) ) : [];
			\update_post_meta( $post_id, Constants::META_VEHICLE_GALLERY, $gallery_ids );
		} else {
			\update_post_meta( $post_id, Constants::META_VEHICLE_GALLERY, [] );
		}
		
		// Disponibilité
		$availability = isset( $_POST[ Constants::META_VEHICLE_AVAILABILITY ] ) && $_POST[ Constants::META_VEHICLE_AVAILABILITY ] === '1';
		\update_post_meta( $post_id, Constants::META_VEHICLE_AVAILABILITY, $availability );
		
		// Prix journalier
		if ( isset( $_POST[ Constants::META_VEHICLE_DAILY_PRICE ] ) ) {
			$price = \TM\Utils\MetaHelper::format_price_for_save( $_POST[ Constants::META_VEHICLE_DAILY_PRICE ] );
			\update_post_meta( $post_id, Constants::META_VEHICLE_DAILY_PRICE, $price );
		}
	}
}

