<?php
/**
 * Meta Box pour les Tours
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

/**
 * Classe pour gérer les meta boxes des tours
 */
class TourMeta extends MetaBox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct(
			'tm_tour_meta',
			__( 'Informations du tour', 'transfertmarrakech' ),
			'tours'
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
		$meta = \TM\Utils\MetaHelper::get_tour_meta( $post->ID );
		
		$location      = $meta['tm_location'] ?? '';
		$duration      = $meta['tm_duration'] ?? '';
		$duration_min  = $meta['tm_duration_minutes'] ?? 0;
		$nights        = $meta['tm_nights'] ?? 0;
		$meals         = $meta['tm_meals'] ?? 0;
		$price         = $meta['tm_price'] ?? '';
		$vehicle_ids   = $meta['tm_vehicles'] ?? [];
		$highlights    = $meta['tm_highlights'] ?? '';
		$meeting_point = $meta['tm_meeting_point'] ?? '';
		
		// Localisation
		$this->text_field( 'tm_location', __( 'Localisation', 'transfertmarrakech' ), $location );
		
		// Durée (affichage) - Temps de route vers la destination
		$this->text_field( 'tm_duration', __( 'Durée (affichage)', 'transfertmarrakech' ), $duration, __( 'Ex: 2h30 (temps de route vers la destination)', 'transfertmarrakech' ) );
		
		// Durée en minutes - Nombre de jours du tour
		$this->number_field( 'tm_duration_minutes', __( 'Nombre de jours du tour', 'transfertmarrakech' ), $duration_min );
		
		// Nombre de nuits
		$this->number_field( 'tm_nights', __( 'Nombre de nuits', 'transfertmarrakech' ), $nights );
		
		// Nombre de repas
		$this->number_field( 'tm_meals', __( 'Nombre de repas', 'transfertmarrakech' ), $meals );
		
		// Prix
		$this->text_field( 'tm_price', __( 'Prix (MAD)', 'transfertmarrakech' ), $price, '0.00' );
		
		// Véhicules associés
		$this->post_select_field( 'tm_vehicles', __( 'Véhicules disponibles', 'transfertmarrakech' ), 'vehicules', $vehicle_ids );
		
		// Points forts
		$this->textarea_field( 'tm_highlights', __( 'Points forts', 'transfertmarrakech' ), $highlights, 5 );
		
		// Point de rendez-vous
		$this->text_field( 'tm_meeting_point', __( 'Point de rendez-vous', 'transfertmarrakech' ), $meeting_point );
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
		
		// Localisation
		if ( isset( $_POST['tm_location'] ) ) {
			\update_post_meta( $post_id, 'tm_location', \sanitize_text_field( $_POST['tm_location'] ) );
		}
		
		// Durée (affichage) - Temps de route vers la destination
		if ( isset( $_POST['tm_duration'] ) ) {
			\update_post_meta( $post_id, 'tm_duration', \sanitize_text_field( $_POST['tm_duration'] ) );
		}
		
		// Nombre de jours du tour
		if ( isset( $_POST['tm_duration_minutes'] ) ) {
			\update_post_meta( $post_id, 'tm_duration_minutes', \absint( $_POST['tm_duration_minutes'] ) );
		}
		
		// Nombre de nuits
		if ( isset( $_POST['tm_nights'] ) ) {
			\update_post_meta( $post_id, 'tm_nights', \absint( $_POST['tm_nights'] ) );
		}
		
		// Nombre de repas
		if ( isset( $_POST['tm_meals'] ) ) {
			\update_post_meta( $post_id, 'tm_meals', \absint( $_POST['tm_meals'] ) );
		}
		
		// Prix
		if ( isset( $_POST['tm_price'] ) ) {
			$price = \floatval( $_POST['tm_price'] );
			\update_post_meta( $post_id, 'tm_price', \number_format( $price, 2, '.', '' ) );
		}
		
		// Véhicules associés
		// Le champ hidden garantit que $_POST['tm_vehicles'] existe toujours
		if ( isset( $_POST['tm_vehicles'] ) && is_array( $_POST['tm_vehicles'] ) ) {
			// Filtre les valeurs vides et convertit en entiers
			$vehicle_ids = array_filter( 
				array_map( 'absint', $_POST['tm_vehicles'] ),
				function( $id ) {
					return $id > 0;
				}
			);
			// Réindexe le tableau pour éviter les trous
			$vehicle_ids = array_values( $vehicle_ids );
			\update_post_meta( $post_id, 'tm_vehicles', $vehicle_ids );
		} else {
			// Si le champ n'existe pas (ne devrait pas arriver avec le champ hidden), on met un tableau vide
			\update_post_meta( $post_id, 'tm_vehicles', [] );
		}
		
		// Points forts
		if ( isset( $_POST['tm_highlights'] ) ) {
			\update_post_meta( $post_id, 'tm_highlights', \sanitize_textarea_field( $_POST['tm_highlights'] ) );
		}
		
		// Point de rendez-vous
		if ( isset( $_POST['tm_meeting_point'] ) ) {
			\update_post_meta( $post_id, 'tm_meeting_point', \sanitize_text_field( $_POST['tm_meeting_point'] ) );
		}
	}
}

