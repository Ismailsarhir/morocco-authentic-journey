<?php
/**
 * Meta Box pour les Transferts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

use TM\Core\Constants;

/**
 * Classe pour gérer les meta boxes des transferts
 */
class TransferMeta extends MetaBox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct(
			'tm_transfer_meta',
			__( 'Informations du transfert', 'transfertmarrakech' ),
			'transferts'
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
		$meta = \TM\Utils\MetaHelper::get_transfer_meta( $post->ID );
		
		$transfer_type     = $meta[ Constants::META_TRANSFER_TYPE ] ?? '';
		$vehicle_id        = $meta[ Constants::META_TRANSFER_VEHICLE ] ?? '';
		$price             = $meta[ Constants::META_TRANSFER_PRICE ] ?? '';
		$pickup            = $meta[ Constants::META_TRANSFER_PICKUP ] ?? '';
		$dropoff           = $meta[ Constants::META_TRANSFER_DROPOFF ] ?? '';
		$duration_estimate = $meta[ Constants::META_TRANSFER_DURATION_ESTIMATE ] ?? '';
		$description       = $meta[ Constants::META_TRANSFER_DESCRIPTION ] ?? '';
		
		// Type de transfert
		$type_options = [
			'airport'     => __( 'Aéroport', 'transfertmarrakech' ),
			'hotel'       => __( 'Hôtel', 'transfertmarrakech' ),
			'city'        => __( 'Ville', 'transfertmarrakech' ),
			'custom'      => __( 'Personnalisé', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_TRANSFER_TYPE, __( 'Type de transfert', 'transfertmarrakech' ), $type_options, $transfer_type );
		
		// Véhicule associé
		$this->single_post_select_field( Constants::META_TRANSFER_VEHICLE, __( 'Véhicule', 'transfertmarrakech' ), Constants::POST_TYPE_VEHICLE, $vehicle_id );
		
		// Prix
		$this->text_field( Constants::META_TRANSFER_PRICE, __( 'Prix (MAD)', 'transfertmarrakech' ), $price, '0.00' );
		
		// Point de prise en charge
		$this->text_field( Constants::META_TRANSFER_PICKUP, __( 'Point de prise en charge', 'transfertmarrakech' ), $pickup );
		
		// Point de dépose
		$this->text_field( Constants::META_TRANSFER_DROPOFF, __( 'Point de dépose', 'transfertmarrakech' ), $dropoff );
		
		// Estimation de durée
		$this->text_field( Constants::META_TRANSFER_DURATION_ESTIMATE, __( 'Estimation de durée', 'transfertmarrakech' ), $duration_estimate, __( 'Ex: 30 minutes', 'transfertmarrakech' ) );
		
		// Description
		$this->textarea_field( Constants::META_TRANSFER_DESCRIPTION, __( 'Description détaillée', 'transfertmarrakech' ), $description, 5 );
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
		
		// Type de transfert
		if ( isset( $_POST[ Constants::META_TRANSFER_TYPE ] ) ) {
			\update_post_meta( $post_id, Constants::META_TRANSFER_TYPE, \sanitize_text_field( $_POST[ Constants::META_TRANSFER_TYPE ] ) );
		}
		
		// Véhicule associé
		if ( isset( $_POST[ Constants::META_TRANSFER_VEHICLE ] ) ) {
			$vehicle_id = \absint( $_POST[ Constants::META_TRANSFER_VEHICLE ] );
			\update_post_meta( $post_id, Constants::META_TRANSFER_VEHICLE, $vehicle_id > 0 ? $vehicle_id : '' );
		}
		
		// Prix
		if ( isset( $_POST[ Constants::META_TRANSFER_PRICE ] ) ) {
			$price = \TM\Utils\MetaHelper::format_price_for_save( $_POST[ Constants::META_TRANSFER_PRICE ] );
			\update_post_meta( $post_id, Constants::META_TRANSFER_PRICE, $price );
		}
		
		// Point de prise en charge
		if ( isset( $_POST[ Constants::META_TRANSFER_PICKUP ] ) ) {
			\update_post_meta( $post_id, Constants::META_TRANSFER_PICKUP, \sanitize_text_field( $_POST[ Constants::META_TRANSFER_PICKUP ] ) );
		}
		
		// Point de dépose
		if ( isset( $_POST[ Constants::META_TRANSFER_DROPOFF ] ) ) {
			\update_post_meta( $post_id, Constants::META_TRANSFER_DROPOFF, \sanitize_text_field( $_POST[ Constants::META_TRANSFER_DROPOFF ] ) );
		}
		
		// Estimation de durée
		if ( isset( $_POST[ Constants::META_TRANSFER_DURATION_ESTIMATE ] ) ) {
			\update_post_meta( $post_id, Constants::META_TRANSFER_DURATION_ESTIMATE, \sanitize_text_field( $_POST[ Constants::META_TRANSFER_DURATION_ESTIMATE ] ) );
		}
		
		// Description
		if ( isset( $_POST[ Constants::META_TRANSFER_DESCRIPTION ] ) ) {
			\update_post_meta( $post_id, Constants::META_TRANSFER_DESCRIPTION, \sanitize_textarea_field( $_POST[ Constants::META_TRANSFER_DESCRIPTION ] ) );
		}
	}
}

