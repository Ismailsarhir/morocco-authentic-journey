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
		$duration_unit = $meta[ Constants::META_TOUR_DURATION_UNIT ] ?? 'hours';
		$highlights    = $meta[ Constants::META_TOUR_HIGHLIGHTS ] ?? '';
		$meeting_point = $meta[ Constants::META_TOUR_MEETING_POINT ] ?? '';
		$tour_type     = $meta[ Constants::META_TOUR_TYPE ] ?? '';
		$difficulty    = $meta[ Constants::META_TOUR_DIFFICULTY ] ?? '';
		$languages         = $meta[ Constants::META_TOUR_LANGUAGES ] ?? [];
		$tags              = $meta[ Constants::META_TOUR_TAGS ] ?? [];
		$itinerary_title   = $meta[ Constants::META_TOUR_ITINERARY_TITLE ] ?? '';
		$itinerary_places   = $meta[ Constants::META_TOUR_ITINERARY ] ?? [];
		$included          = $meta[ Constants::META_TOUR_INCLUDED ] ?? '';
		$excluded      = $meta[ Constants::META_TOUR_EXCLUDED ] ?? '';
		$cancellation  = $meta[ Constants::META_TOUR_CANCELLATION ] ?? '';
		$price_tiers   = $meta[ Constants::META_TOUR_PRICE_TIERS ] ?? [];
		$vehicle_ids   = $meta[ Constants::META_TOUR_VEHICLES ] ?? [];
		
		// Localisation
		$this->text_field( Constants::META_TOUR_LOCATION, __( 'Localisation', 'transfertmarrakech' ), $location, __( 'Ex: Essaouira, Marrakech', 'transfertmarrakech' ) );
		
		// Durée
		$this->text_field( Constants::META_TOUR_DURATION, __( 'Durée (nombre)', 'transfertmarrakech' ), $duration, __( 'Ex: 10', 'transfertmarrakech' ) );
		
		// Unité de durée
		$duration_unit_options = [
			'hours' => __( 'Heures', 'transfertmarrakech' ),
			'days'  => __( 'Jours', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_TOUR_DURATION_UNIT, __( 'Unité de durée', 'transfertmarrakech' ), $duration_unit_options, $duration_unit );
		
		// Type de tour
		$tour_type_options = [
			'group'   => __( 'Group Tour', 'transfertmarrakech' ),
			'private' => __( 'Private Tour', 'transfertmarrakech' ),
			'shared'  => __( 'Shared Group', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_TOUR_TYPE, __( 'Type de tour', 'transfertmarrakech' ), $tour_type_options, $tour_type );
		
		// Difficulté
		$difficulty_options = [
			'easy'   => __( 'Easy', 'transfertmarrakech' ),
			'medium' => __( 'Medium', 'transfertmarrakech' ),
			'hard'   => __( 'Hard', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_TOUR_DIFFICULTY, __( 'Difficulté', 'transfertmarrakech' ), $difficulty_options, $difficulty );
		
		// Langues (multi-select)
		$language_options = [
			'english' => __( 'English', 'transfertmarrakech' ),
			'french'  => __( 'French', 'transfertmarrakech' ),
			'spanish' => __( 'Spanish', 'transfertmarrakech' ),
			'arabic'  => __( 'Arabic', 'transfertmarrakech' ),
			'german'  => __( 'German', 'transfertmarrakech' ),
			'italian' => __( 'Italian', 'transfertmarrakech' ),
		];
		$this->multi_checkbox_field( Constants::META_TOUR_LANGUAGES, __( 'Langues disponibles', 'transfertmarrakech' ), $language_options, $languages );
		
		// Tags/Catégories (multi-select)
		$tag_options = [
			'photography' => __( 'Photography', 'transfertmarrakech' ),
			'historical'  => __( 'Historical', 'transfertmarrakech' ),
			'sightseeing' => __( 'Sightseeing', 'transfertmarrakech' ),
			'adventure'   => __( 'Adventure', 'transfertmarrakech' ),
			'cultural'    => __( 'Cultural', 'transfertmarrakech' ),
			'nature'     => __( 'Nature', 'transfertmarrakech' ),
		];
		$this->multi_checkbox_field( Constants::META_TOUR_TAGS, __( 'Tags/Catégories', 'transfertmarrakech' ), $tag_options, $tags );
		
		// Points forts (Highlights)
		$this->textarea_field( Constants::META_TOUR_HIGHLIGHTS, __( 'Highlights (une ligne par point fort)', 'transfertmarrakech' ), $highlights, 5 );
		
		// Point de rendez-vous (Meeting Point)
		$this->text_field( Constants::META_TOUR_MEETING_POINT, __( 'Meeting Point', 'transfertmarrakech' ), $meeting_point, __( 'Ex: Marrakech, Maroc', 'transfertmarrakech' ) );
		
		// Prix par nombre de personnes
		$this->price_tiers_field( Constants::META_TOUR_PRICE_TIERS, __( 'Prix par nombre de personnes', 'transfertmarrakech' ), $price_tiers );
		
		// Véhicules associés
		$this->post_select_field( Constants::META_TOUR_VEHICLES, __( 'Véhicules disponibles', 'transfertmarrakech' ), Constants::POST_TYPE_VEHICLE, $vehicle_ids );
		
		// Itinéraire
		$this->itinerary_field( 
			Constants::META_TOUR_ITINERARY_TITLE, 
			Constants::META_TOUR_ITINERARY, 
			__( 'Itinerary', 'transfertmarrakech' ), 
			$itinerary_title, 
			$itinerary_places 
		);
		
		// Inclus (What's Included)
		$this->textarea_field( Constants::META_TOUR_INCLUDED, __( 'What\'s Included (une ligne par item)', 'transfertmarrakech' ), $included, 5 );
		
		// Exclus (What's Excluded)
		$this->textarea_field( Constants::META_TOUR_EXCLUDED, __( 'What\'s Excluded (une ligne par item)', 'transfertmarrakech' ), $excluded, 5 );
		
		// Politique d'annulation (Cancellation Policy)
		$this->textarea_field( Constants::META_TOUR_CANCELLATION, __( 'Cancellation Policy', 'transfertmarrakech' ), $cancellation, 3 );
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
		
		// Durée
		if ( isset( $_POST[ Constants::META_TOUR_DURATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_DURATION, \sanitize_text_field( $_POST[ Constants::META_TOUR_DURATION ] ) );
		}
		
		// Unité de durée
		if ( isset( $_POST[ Constants::META_TOUR_DURATION_UNIT ] ) ) {
			$duration_unit = \sanitize_text_field( $_POST[ Constants::META_TOUR_DURATION_UNIT ] );
			// Valide que c'est soit 'hours' soit 'days'
			if ( in_array( $duration_unit, [ 'hours', 'days' ], true ) ) {
				\update_post_meta( $post_id, Constants::META_TOUR_DURATION_UNIT, $duration_unit );
			} else {
				\update_post_meta( $post_id, Constants::META_TOUR_DURATION_UNIT, 'hours' );
			}
		}
		
		// Points forts (Highlights)
		if ( isset( $_POST[ Constants::META_TOUR_HIGHLIGHTS ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_HIGHLIGHTS, \sanitize_textarea_field( $_POST[ Constants::META_TOUR_HIGHLIGHTS ] ) );
		}
		
		// Véhicules associés
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
		
		// Point de rendez-vous
		if ( isset( $_POST[ Constants::META_TOUR_MEETING_POINT ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_MEETING_POINT, \sanitize_text_field( $_POST[ Constants::META_TOUR_MEETING_POINT ] ) );
		}
		
		// Type de tour
		if ( isset( $_POST[ Constants::META_TOUR_TYPE ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_TYPE, \sanitize_text_field( $_POST[ Constants::META_TOUR_TYPE ] ) );
		}
		
		// Difficulté
		if ( isset( $_POST[ Constants::META_TOUR_DIFFICULTY ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_DIFFICULTY, \sanitize_text_field( $_POST[ Constants::META_TOUR_DIFFICULTY ] ) );
		}
		
		// Langues
		if ( isset( $_POST[ Constants::META_TOUR_LANGUAGES ] ) && is_array( $_POST[ Constants::META_TOUR_LANGUAGES ] ) ) {
			$languages = array_map( 'sanitize_text_field', $_POST[ Constants::META_TOUR_LANGUAGES ] );
			// Filtre les valeurs vides (chaînes vides, null, false)
			$languages = array_values( array_filter( $languages, function( $item ) {
				return ! empty( $item ) && is_string( $item );
			} ) );
			\update_post_meta( $post_id, Constants::META_TOUR_LANGUAGES, $languages );
		} else {
			\update_post_meta( $post_id, Constants::META_TOUR_LANGUAGES, [] );
		}
		
		// Tags
		if ( isset( $_POST[ Constants::META_TOUR_TAGS ] ) && is_array( $_POST[ Constants::META_TOUR_TAGS ] ) ) {
			$tags = array_map( 'sanitize_text_field', $_POST[ Constants::META_TOUR_TAGS ] );
			// Filtre les valeurs vides (chaînes vides, null, false)
			$tags = array_values( array_filter( $tags, function( $item ) {
				return ! empty( $item ) && is_string( $item );
			} ) );
			\update_post_meta( $post_id, Constants::META_TOUR_TAGS, $tags );
		} else {
			\update_post_meta( $post_id, Constants::META_TOUR_TAGS, [] );
		}
		
		// Titre de l'itinéraire
		if ( isset( $_POST[ Constants::META_TOUR_ITINERARY_TITLE ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_ITINERARY_TITLE, \sanitize_text_field( $_POST[ Constants::META_TOUR_ITINERARY_TITLE ] ) );
		}
		
		// Places de l'itinéraire
		if ( isset( $_POST[ Constants::META_TOUR_ITINERARY ] ) && is_array( $_POST[ Constants::META_TOUR_ITINERARY ] ) ) {
			$places = [];
			foreach ( $_POST[ Constants::META_TOUR_ITINERARY ] as $place ) {
				if ( ! is_array( $place ) ) {
					continue;
				}
				
				$time = isset( $place['time'] ) ? \sanitize_text_field( $place['time'] ) : '';
				$title = isset( $place['title'] ) ? \sanitize_text_field( $place['title'] ) : '';
				$description = isset( $place['description'] ) ? \sanitize_textarea_field( $place['description'] ) : '';
				
				// Ne garde que les places avec au moins un titre ou une description
				if ( ! empty( $title ) || ! empty( $description ) ) {
					$places[] = [
						'time'        => $time,
						'title'       => $title,
						'description' => $description,
					];
				}
			}
			\update_post_meta( $post_id, Constants::META_TOUR_ITINERARY, $places );
		} else {
			\update_post_meta( $post_id, Constants::META_TOUR_ITINERARY, [] );
		}
		
		// Inclus
		if ( isset( $_POST[ Constants::META_TOUR_INCLUDED ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_INCLUDED, \sanitize_textarea_field( $_POST[ Constants::META_TOUR_INCLUDED ] ) );
		}
		
		// Exclus
		if ( isset( $_POST[ Constants::META_TOUR_EXCLUDED ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_EXCLUDED, \sanitize_textarea_field( $_POST[ Constants::META_TOUR_EXCLUDED ] ) );
		}
		
		// Politique d'annulation
		if ( isset( $_POST[ Constants::META_TOUR_CANCELLATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_TOUR_CANCELLATION, \sanitize_textarea_field( $_POST[ Constants::META_TOUR_CANCELLATION ] ) );
		}
		
		// Prix par nombre de personnes (flexible)
		if ( isset( $_POST[ Constants::META_TOUR_PRICE_TIERS ] ) && is_array( $_POST[ Constants::META_TOUR_PRICE_TIERS ] ) ) {
			$tiers = [];
			foreach ( $_POST[ Constants::META_TOUR_PRICE_TIERS ] as $tier ) {
				if ( ! is_array( $tier ) ) {
					continue;
				}
				
				$min_persons = isset( $tier['min_persons'] ) ? absint( $tier['min_persons'] ) : 0;
				$max_persons = isset( $tier['max_persons'] ) ? absint( $tier['max_persons'] ) : 0;
				$price_value = isset( $tier['price'] ) ? \TM\Utils\MetaHelper::format_price_for_save( $tier['price'] ) : '';
				$tier_type = isset( $tier['type'] ) ? \sanitize_text_field( $tier['type'] ) : '';
				
				// Valide que min <= max et que le prix est défini
				if ( $min_persons > 0 && $max_persons >= $min_persons && ! empty( $price_value ) ) {
					$tiers[] = [
						'min_persons' => $min_persons,
						'max_persons' => $max_persons,
						'price'       => $price_value,
						'type'        => $tier_type,
					];
				}
			}
			\update_post_meta( $post_id, Constants::META_TOUR_PRICE_TIERS, $tiers );
		} else {
			\update_post_meta( $post_id, Constants::META_TOUR_PRICE_TIERS, [] );
		}
	}
}

