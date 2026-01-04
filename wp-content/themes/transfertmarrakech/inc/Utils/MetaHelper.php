<?php
/**
 * Helper pour normaliser et récupérer les meta
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Utils;

/**
 * Classe helper pour gérer les meta de manière optimisée
 */
class MetaHelper {
	
	/**
	 * Normalise un tableau d'IDs (gallery, vehicles, etc.)
	 * Gère les différents formats : tableau, chaîne séparée par virgules, ID unique
	 * 
	 * @param mixed $value Valeur brute de la meta
	 * @return array Tableau d'IDs normalisés
	 */
	public static function normalize_ids_array( $value ): array {
		if ( empty( $value ) ) {
			return [];
		}
		
		if ( is_array( $value ) ) {
			// Déjà un tableau, on filtre les valeurs valides
			return array_values( array_filter( array_map( 'absint', $value ), function( $id ) {
				return $id > 0;
			} ) );
		}
		
		if ( is_string( $value ) ) {
			$trimmed = trim( $value );
			if ( ! empty( $trimmed ) ) {
				// Si c'est une chaîne, on la convertit en tableau
				$ids = array_filter( array_map( 'absint', explode( ',', $trimmed ) ), function( $id ) {
					return $id > 0;
				} );
				return array_values( $ids );
			}
		}
		
		if ( is_numeric( $value ) ) {
			// Si c'est un seul ID
			$id = absint( $value );
			return $id > 0 ? [ $id ] : [];
		}
		
		return [];
	}
	
	/**
	 * Normalise un tableau de chaînes (pour langues, tags, etc.)
	 * 
	 * @param mixed $value Valeur à normaliser
	 * @return array Tableau de chaînes normalisées
	 */
	public static function normalize_string_array( $value ): array {
		if ( empty( $value ) ) {
			return [];
		}
		
		// Si c'est déjà un tableau, on le nettoie directement
		if ( is_array( $value ) ) {
			// Filtre les valeurs vides (chaînes vides, null, false)
			$filtered = array_filter( $value, function( $item ) {
				return ! empty( $item ) && is_string( $item );
			} );
			// Nettoie et réindexe
			return array_values( array_map( 'trim', $filtered ) );
		}
		
		// Si c'est une chaîne, on essaie de la désérialiser
		if ( is_string( $value ) ) {
			$trimmed = trim( $value );
			if ( empty( $trimmed ) ) {
				return [];
			}
			
			// WordPress stocke les tableaux comme sérialisés PHP
			// On essaie de désérialiser d'abord
			$unserialized = @unserialize( $trimmed );
			if ( $unserialized !== false && is_array( $unserialized ) ) {
				$filtered = array_filter( $unserialized, function( $item ) {
					return ! empty( $item ) && is_string( $item );
				} );
				return array_values( array_map( 'trim', $filtered ) );
			}
			
			// Sinon, on essaie JSON
			$decoded = json_decode( $trimmed, true );
			if ( json_last_error() === JSON_ERROR_NONE && is_array( $decoded ) ) {
				$filtered = array_filter( $decoded, function( $item ) {
					return ! empty( $item ) && is_string( $item );
				} );
				return array_values( array_map( 'trim', $filtered ) );
			}
			
			// En dernier recours, on split par virgule
			$items = array_map( 'trim', explode( ',', $trimmed ) );
			$filtered = array_filter( $items, function( $item ) {
				return ! empty( $item );
			} );
			return array_values( $filtered );
		}
		
		return [];
	}
	
	/**
	 * Récupère toutes les meta d'un post en une seule requête
	 * Plus efficace que plusieurs appels get_post_meta
	 * Utilise update_post_meta_cache pour optimiser les requêtes SQL
	 * 
	 * @param int   $post_id ID du post
	 * @param array $meta_keys Liste des clés meta à récupérer
	 * @return array Tableau associatif [meta_key => value]
	 */
	public static function get_all_meta( int $post_id, array $meta_keys ): array {
		if ( empty( $meta_keys ) || $post_id <= 0 ) {
			return [];
		}
		
		// Optimisation : charge toutes les meta en cache en une fois
		\update_postmeta_cache( [ $post_id ] );
		
		$meta = [];
		foreach ( $meta_keys as $key ) {
			$meta[ $key ] = \get_post_meta( $post_id, $key, true );
		}
		
		return $meta;
	}
	
	/**
	 * Récupère et normalise les meta d'un véhicule
	 * 
	 * @param int $vehicle_id ID du véhicule
	 * @return array Tableau des meta normalisées
	 */
	public static function get_vehicle_meta( int $vehicle_id ): array {
		$meta = self::get_all_meta( $vehicle_id, [
			\TM\Core\Constants::META_VEHICLE_TYPE,
			\TM\Core\Constants::META_VEHICLE_SEATS,
			\TM\Core\Constants::META_VEHICLE_BAGGAGE_CAPACITY,
			\TM\Core\Constants::META_VEHICLE_GALLERY,
			\TM\Core\Constants::META_VEHICLE_AVAILABILITY,
			\TM\Core\Constants::META_VEHICLE_DAILY_PRICE,
		] );
		
		// Normalise la galerie
		$meta[ \TM\Core\Constants::META_VEHICLE_GALLERY ] = self::normalize_ids_array( $meta[ \TM\Core\Constants::META_VEHICLE_GALLERY ] ?? null );
		
		// Normalise les booléens
		$meta[ \TM\Core\Constants::META_VEHICLE_AVAILABILITY ] = (bool) ( $meta[ \TM\Core\Constants::META_VEHICLE_AVAILABILITY ] ?? false );
		
		// Normalise les entiers
		$meta[ \TM\Core\Constants::META_VEHICLE_SEATS ] = absint( $meta[ \TM\Core\Constants::META_VEHICLE_SEATS ] ?? 0 );
		
		return $meta;
	}
	
	/**
	 * Récupère et normalise les meta d'un tour
	 * 
	 * @param int $tour_id ID du tour
	 * @return array Tableau des meta normalisées
	 */
	public static function get_tour_meta( int $tour_id ): array {
		$meta = self::get_all_meta( $tour_id, [
			\TM\Core\Constants::META_TOUR_LOCATION,
			\TM\Core\Constants::META_TOUR_DURATION,
			\TM\Core\Constants::META_TOUR_DURATION_UNIT,
			\TM\Core\Constants::META_TOUR_HIGHLIGHTS,
			\TM\Core\Constants::META_TOUR_MEETING_POINT,
			\TM\Core\Constants::META_TOUR_TYPE,
			\TM\Core\Constants::META_TOUR_DIFFICULTY,
			\TM\Core\Constants::META_TOUR_LANGUAGES,
			\TM\Core\Constants::META_TOUR_TAGS,
			\TM\Core\Constants::META_TOUR_ITINERARY_TITLE,
			\TM\Core\Constants::META_TOUR_ITINERARY,
			\TM\Core\Constants::META_TOUR_INCLUDED,
			\TM\Core\Constants::META_TOUR_EXCLUDED,
			\TM\Core\Constants::META_TOUR_CANCELLATION,
			\TM\Core\Constants::META_TOUR_PRICE_TIERS,
			\TM\Core\Constants::META_TOUR_VEHICLES,
		] );
		
		// Normalise les véhicules
		$meta[ \TM\Core\Constants::META_TOUR_VEHICLES ] = self::normalize_ids_array( $meta[ \TM\Core\Constants::META_TOUR_VEHICLES ] ?? null );
		
		// Normalise les langues et tags (arrays de chaînes)
		$meta[ \TM\Core\Constants::META_TOUR_LANGUAGES ] = self::normalize_string_array( $meta[ \TM\Core\Constants::META_TOUR_LANGUAGES ] ?? null );
		$meta[ \TM\Core\Constants::META_TOUR_TAGS ] = self::normalize_string_array( $meta[ \TM\Core\Constants::META_TOUR_TAGS ] ?? null );
		
		// Normalise les price tiers (array)
		$price_tiers = $meta[ \TM\Core\Constants::META_TOUR_PRICE_TIERS ] ?? [];
		$meta[ \TM\Core\Constants::META_TOUR_PRICE_TIERS ] = is_array( $price_tiers ) ? $price_tiers : [];
		
		// Normalise l'unité de durée (par défaut 'hours')
		$duration_unit = $meta[ \TM\Core\Constants::META_TOUR_DURATION_UNIT ] ?? 'hours';
		$meta[ \TM\Core\Constants::META_TOUR_DURATION_UNIT ] = in_array( $duration_unit, [ 'hours', 'days' ], true ) ? $duration_unit : 'hours';
		
		// Normalise les places de l'itinéraire (array)
		$itinerary_places = $meta[ \TM\Core\Constants::META_TOUR_ITINERARY ] ?? [];
		$meta[ \TM\Core\Constants::META_TOUR_ITINERARY ] = is_array( $itinerary_places ) ? $itinerary_places : [];
		
		return $meta;
	}
	
	/**
	 * Récupère et normalise les meta d'un transfert
	 * 
	 * @param int $transfer_id ID du transfert
	 * @return array Tableau des meta normalisées
	 */
	public static function get_transfer_meta( int $transfer_id ): array {
		$meta = self::get_all_meta( $transfer_id, [
			\TM\Core\Constants::META_TRANSFER_TYPE,
			\TM\Core\Constants::META_TRANSFER_VEHICLE,
			\TM\Core\Constants::META_TRANSFER_PRICE,
			\TM\Core\Constants::META_TRANSFER_PICKUP,
			\TM\Core\Constants::META_TRANSFER_DROPOFF,
			\TM\Core\Constants::META_TRANSFER_DURATION_ESTIMATE,
			\TM\Core\Constants::META_TRANSFER_DESCRIPTION,
		] );
		
		// Normalise le véhicule (ID unique)
		$meta[ \TM\Core\Constants::META_TRANSFER_VEHICLE ] = absint( $meta[ \TM\Core\Constants::META_TRANSFER_VEHICLE ] ?? 0 );
		
		return $meta;
	}
	
	/**
	 * Récupère le numéro WhatsApp depuis les options
	 * 
	 * @return string
	 */
	public static function get_whatsapp_phone(): string {
		return \get_option( \TM\Core\Constants::OPTION_WHATSAPP_PHONE, \TM\Core\Constants::OPTION_WHATSAPP_PHONE_DEFAULT );
	}
	
	/**
	 * Récupère le post actuel (queried object ou post global)
	 * 
	 * @return \WP_Post|null
	 */
	public static function get_current_post(): ?\WP_Post {
		$post = \get_queried_object();
		if ( ! $post instanceof \WP_Post ) {
			global $post;
		}
		return ( $post instanceof \WP_Post ) ? $post : null;
	}
	
	/**
	 * Formate la durée avec l'unité appropriée (heures/jours)
	 * 
	 * @param string $duration Durée brute (ex: "10", "2")
	 * @param string $unit Unité ('hours' ou 'days')
	 * @return string Durée formatée
	 */
	public static function format_duration( string $duration, string $unit = 'hours' ): string {
		if ( empty( $duration ) ) {
			return '';
		}
		
		$duration_num = (int) $duration;
		if ( $duration_num <= 0 ) {
			return '';
		}
		
		$duration_escaped = esc_html( $duration_num );
		
		if ( $unit === 'days' ) {
			return sprintf(
				'%s %s',
				$duration_escaped,
				esc_html( _n( 'jour', 'jours', $duration_num, 'transfertmarrakech' ) )
			);
		}
		
		// Par défaut, heures
		return sprintf(
			'%s %s',
			$duration_escaped,
			esc_html( _n( 'heure', 'heures', $duration_num, 'transfertmarrakech' ) )
		);
	}
	
	/**
	 * Formate un prix avec séparateurs de milliers
	 * 
	 * @param mixed $price Prix brut
	 * @return string Prix formaté
	 */
	/**
	 * Formate un prix en USD (sans décimales si entier)
	 * 
	 * @param mixed $price Prix à formater
	 * @return string Prix formaté avec USD
	 */
	public static function format_price( $price ): string {
		if ( empty( $price ) ) {
			return '';
		}
		$float_price = (float) $price;
		// Si c'est un nombre entier, pas de décimales
		if ( $float_price == floor( $float_price ) ) {
			return \number_format( $float_price, 0, '.', ' ' ) . ' USD';
		}
		return \number_format( $float_price, 2, '.', ' ' ) . ' USD';
	}
	
	/**
	 * Formate un prix en USD pour les tours (sans décimales si entier)
	 * 
	 * @param mixed $price Prix à formater
	 * @return string Prix formaté avec USD
	 */
	public static function format_price_usd( $price ): string {
		if ( empty( $price ) ) {
			return '';
		}
		$float_price = (float) $price;
		// Si c'est un nombre entier, pas de décimales
		if ( $float_price == floor( $float_price ) ) {
			return \number_format( $float_price, 0, '.', ' ' ) . ' USD';
		}
		return \number_format( $float_price, 2, '.', ' ' ) . ' USD';
	}
	
	/**
	 * Construit l'URL WhatsApp avec message pré-rempli
	 * 
	 * @param string $message Message à envoyer
	 * @return string URL WhatsApp
	 */
	public static function build_whatsapp_url( string $message ): string {
		$phone_number = self::get_whatsapp_phone();
		$encoded = urlencode( $message );
		return 'https://wa.me/' . $phone_number . '?text=' . $encoded;
	}
	
	/**
	 * Récupère le lien et nom de destination pour le backlink
	 * 
	 * @param int $post_id ID du post
	 * @return array ['link' => string, 'name' => string]
	 */
	public static function get_destination_backlink( int $post_id ): array {
		$destinations = \get_the_terms( $post_id, \TM\Core\Constants::TAXONOMY_DESTINATION );
		$destination_link = '#';
		$destination_name = '';
		
		if ( ! empty( $destinations ) && ! \is_wp_error( $destinations ) ) {
			$destination = \reset( $destinations );
			if ( $destination instanceof \WP_Term ) {
				$destination_link = \get_term_link( $destination );
				$destination_name = $destination->name;
			}
		}
		
		return [
			'link' => $destination_link,
			'name' => $destination_name,
		];
	}
	
	/**
	 * Récupère l'URL de l'image mise en avant avec fallback sur différentes tailles
	 * 
	 * @param int $post_id ID du post
	 * @return string URL de l'image ou chaîne vide
	 */
	public static function get_post_thumbnail_url_with_fallback( int $post_id ): string {
		$thumbnail_url = \get_the_post_thumbnail_url( $post_id, 'large' )
			?: \get_the_post_thumbnail_url( $post_id, 'medium' )
			?: \get_the_post_thumbnail_url( $post_id, 'thumbnail' );
		
		return $thumbnail_url ?: '';
	}
	
	/**
	 * Récupère le titre d'un post avec fallback
	 * 
	 * @param \WP_Post $post Objet post WordPress
	 * @return string Titre du post
	 */
	public static function get_post_title( \WP_Post $post ): string {
		return $post->post_title ?: \get_the_title( $post->ID );
	}
	
	/**
	 * Formate un prix pour la sauvegarde en base (2 décimales avec point)
	 * 
	 * @param mixed $price Prix brut
	 * @return string Prix formaté pour la sauvegarde
	 */
	public static function format_price_for_save( $price ): string {
		// Si c'est une valeur numérique, formater comme prix
		if ( is_numeric( $price ) ) {
			$price = \floatval( $price );
			return \number_format( $price, 2, '.', '' );
		}
		// Sinon, retourner la chaîne telle quelle (ex: "Ask walid")
		return \sanitize_text_field( $price );
	}
}

