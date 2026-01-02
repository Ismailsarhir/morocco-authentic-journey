<?php
/**
 * Meta Box pour les Tours
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

use TM\Core\Constants;

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
			Constants::POST_TYPE_TOUR
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
		
		$location      = $meta[ Constants::META_TOUR_LOCATION ] ?? '';
		$duration      = $meta[ Constants::META_TOUR_DURATION ] ?? '';
		$duration_min  = $meta[ Constants::META_TOUR_DURATION_MINUTES ] ?? 0;
		$nights        = $meta[ Constants::META_TOUR_NIGHTS ] ?? 0;
		$meals         = $meta[ Constants::META_TOUR_MEALS ] ?? 0;
		$price         = $meta[ Constants::META_TOUR_PRICE ] ?? '';
		$vehicle_ids   = $meta[ Constants::META_TOUR_VEHICLES ] ?? [];
		$highlights    = $meta[ Constants::META_TOUR_HIGHLIGHTS ] ?? '';
		$meeting_point = $meta[ Constants::META_TOUR_MEETING_POINT ] ?? '';
		
		// Localisation
		$this->text_field( Constants::META_TOUR_LOCATION, __( 'Localisation', 'transfertmarrakech' ), $location );
		
		// Durée (affichage) - Temps de route vers la destination
		$this->text_field( Constants::META_TOUR_DURATION, __( 'Durée (affichage)', 'transfertmarrakech' ), $duration, __( 'Ex: 2h30 (temps de route vers la destination)', 'transfertmarrakech' ) );
		
		// Durée en minutes - Nombre de jours du tour
		$this->number_field( Constants::META_TOUR_DURATION_MINUTES, __( 'Nombre de jours du tour', 'transfertmarrakech' ), $duration_min );
		
		// Nombre de nuits
		$this->number_field( Constants::META_TOUR_NIGHTS, __( 'Nombre de nuits', 'transfertmarrakech' ), $nights );
		
		// Nombre de repas
		$this->number_field( Constants::META_TOUR_MEALS, __( 'Nombre de repas', 'transfertmarrakech' ), $meals );
		
		// Prix
		$this->text_field( Constants::META_TOUR_PRICE, __( 'Prix (MAD)', 'transfertmarrakech' ), $price, '0.00' );
		
		// Véhicules associés
		$this->post_select_field( Constants::META_TOUR_VEHICLES, __( 'Véhicules disponibles', 'transfertmarrakech' ), Constants::POST_TYPE_VEHICLE, $vehicle_ids );
		
		// Points forts
		$this->textarea_field( Constants::META_TOUR_HIGHLIGHTS, __( 'Points forts', 'transfertmarrakech' ), $highlights, 5 );
		
		// Point de rendez-vous
		$this->text_field( Constants::META_TOUR_MEETING_POINT, __( 'Point de rendez-vous', 'transfertmarrakech' ), $meeting_point );
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
		if ( isset( $_POST[ Constants::META_TOUR_LOCATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_LOCATION, \sanitize_text_field( $_POST[ Constants::META_TOUR_LOCATION ] ) );
		}
		
		// Durée (affichage) - Temps de route vers la destination
		if ( isset( $_POST[ Constants::META_TOUR_DURATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_DURATION, \sanitize_text_field( $_POST[ Constants::META_TOUR_DURATION ] ) );
		}
		
		// Nombre de jours du tour
		if ( isset( $_POST[ Constants::META_TOUR_DURATION_MINUTES ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_DURATION_MINUTES, \absint( $_POST[ Constants::META_TOUR_DURATION_MINUTES ] ) );
		}
		
		// Nombre de nuits
		if ( isset( $_POST[ Constants::META_TOUR_NIGHTS ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_NIGHTS, \absint( $_POST[ Constants::META_TOUR_NIGHTS ] ) );
		}
		
		// Nombre de repas
		if ( isset( $_POST[ Constants::META_TOUR_MEALS ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_MEALS, \absint( $_POST[ Constants::META_TOUR_MEALS ] ) );
		}
		
		// Prix
		if ( isset( $_POST[ Constants::META_TOUR_PRICE ] ) ) {
			$price = \TM\Utils\MetaHelper::format_price_for_save( $_POST[ Constants::META_TOUR_PRICE ] );
			\update_post_meta( $post_id, Constants::META_TOUR_PRICE, $price );
		}
		
		// Véhicules associés
		// Le champ hidden garantit que $_POST['tm_vehicles'] existe toujours
		if ( isset( $_POST[ Constants::META_TOUR_VEHICLES ] ) && is_array( $_POST[ Constants::META_TOUR_VEHICLES ] ) ) {
			// Filtre les valeurs vides et convertit en entiers
			$vehicle_ids = array_filter( 
				array_map( 'absint', $_POST[ Constants::META_TOUR_VEHICLES ] ),
				function( $id ) {
					return $id > 0;
				}
			);
			// Réindexe le tableau pour éviter les trous
			$vehicle_ids = array_values( $vehicle_ids );
			\update_post_meta( $post_id, Constants::META_TOUR_VEHICLES, $vehicle_ids );
		} else {
			// Si le champ n'existe pas (ne devrait pas arriver avec le champ hidden), on met un tableau vide
			\update_post_meta( $post_id, Constants::META_TOUR_VEHICLES, [] );
		}
		
		// Points forts
		if ( isset( $_POST[ Constants::META_TOUR_HIGHLIGHTS ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_HIGHLIGHTS, \sanitize_textarea_field( $_POST[ Constants::META_TOUR_HIGHLIGHTS ] ) );
		}
		
		// Point de rendez-vous
		if ( isset( $_POST[ Constants::META_TOUR_MEETING_POINT ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_MEETING_POINT, \sanitize_text_field( $_POST[ Constants::META_TOUR_MEETING_POINT ] ) );
		}
	}
}

