<?php
/**
 * Meta Box pour les Transferts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

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
		
		$transfer_type     = $meta['tm_transfer_type'] ?? '';
		$vehicle_id        = $meta['tm_vehicle'] ?? '';
		$price             = $meta['tm_price'] ?? '';
		$pickup            = $meta['tm_pickup'] ?? '';
		$dropoff           = $meta['tm_dropoff'] ?? '';
		$duration_estimate = $meta['tm_duration_estimate'] ?? '';
		$description       = $meta['tm_description'] ?? '';
		
		// Type de transfert
		$type_options = [
			'airport'     => __( 'Aéroport', 'transfertmarrakech' ),
			'hotel'       => __( 'Hôtel', 'transfertmarrakech' ),
			'city'        => __( 'Ville', 'transfertmarrakech' ),
			'custom'      => __( 'Personnalisé', 'transfertmarrakech' ),
		];
		$this->select_field( 'tm_transfer_type', __( 'Type de transfert', 'transfertmarrakech' ), $type_options, $transfer_type );
		
		// Véhicule associé
		$this->single_post_select_field( 'tm_vehicle', __( 'Véhicule', 'transfertmarrakech' ), 'vehicules', $vehicle_id );
		
		// Prix
		$this->text_field( 'tm_price', __( 'Prix (MAD)', 'transfertmarrakech' ), $price, '0.00' );
		
		// Point de prise en charge
		$this->text_field( 'tm_pickup', __( 'Point de prise en charge', 'transfertmarrakech' ), $pickup );
		
		// Point de dépose
		$this->text_field( 'tm_dropoff', __( 'Point de dépose', 'transfertmarrakech' ), $dropoff );
		
		// Estimation de durée
		$this->text_field( 'tm_duration_estimate', __( 'Estimation de durée', 'transfertmarrakech' ), $duration_estimate, __( 'Ex: 30 minutes', 'transfertmarrakech' ) );
		
		// Description
		$this->textarea_field( 'tm_description', __( 'Description détaillée', 'transfertmarrakech' ), $description, 5 );
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
		if ( isset( $_POST['tm_transfer_type'] ) ) {
			\update_post_meta( $post_id, 'tm_transfer_type', \sanitize_text_field( $_POST['tm_transfer_type'] ) );
		}
		
		// Véhicule associé
		if ( isset( $_POST['tm_vehicle'] ) ) {
			$vehicle_id = \absint( $_POST['tm_vehicle'] );
			\update_post_meta( $post_id, 'tm_vehicle', $vehicle_id > 0 ? $vehicle_id : '' );
		}
		
		// Prix
		if ( isset( $_POST['tm_price'] ) ) {
			$price = \floatval( $_POST['tm_price'] );
			\update_post_meta( $post_id, 'tm_price', \number_format( $price, 2, '.', '' ) );
		}
		
		// Point de prise en charge
		if ( isset( $_POST['tm_pickup'] ) ) {
			\update_post_meta( $post_id, 'tm_pickup', \sanitize_text_field( $_POST['tm_pickup'] ) );
		}
		
		// Point de dépose
		if ( isset( $_POST['tm_dropoff'] ) ) {
			\update_post_meta( $post_id, 'tm_dropoff', \sanitize_text_field( $_POST['tm_dropoff'] ) );
		}
		
		// Estimation de durée
		if ( isset( $_POST['tm_duration_estimate'] ) ) {
			\update_post_meta( $post_id, 'tm_duration_estimate', \sanitize_text_field( $_POST['tm_duration_estimate'] ) );
		}
		
		// Description
		if ( isset( $_POST['tm_description'] ) ) {
			\update_post_meta( $post_id, 'tm_description', \sanitize_textarea_field( $_POST['tm_description'] ) );
		}
	}
}

