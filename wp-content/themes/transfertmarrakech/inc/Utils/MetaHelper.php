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
		
		if ( is_string( $value ) && ! empty( trim( $value ) ) ) {
			// Si c'est une chaîne, on la convertit en tableau
			$ids = array_filter( array_map( 'absint', explode( ',', trim( $value ) ) ), function( $id ) {
				return $id > 0;
			} );
			return array_values( $ids );
		}
		
		if ( is_numeric( $value ) ) {
			// Si c'est un seul ID
			$id = absint( $value );
			return $id > 0 ? [ $id ] : [];
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
			'tm_vehicle_type',
			'tm_seats',
			'tm_baggage_capacity',
			'tm_gallery',
			'tm_availability',
			'tm_daily_price',
		] );
		
		// Normalise la galerie
		$meta['tm_gallery'] = self::normalize_ids_array( $meta['tm_gallery'] ?? null );
		
		// Normalise les booléens
		$meta['tm_availability'] = (bool) ( $meta['tm_availability'] ?? false );
		
		// Normalise les entiers
		$meta['tm_seats'] = ! empty( $meta['tm_seats'] ) ? absint( $meta['tm_seats'] ) : 0;
		
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
			'tm_location',
			'tm_duration',
			'tm_duration_minutes',
			'tm_price',
			'tm_vehicles',
			'tm_highlights',
			'tm_meeting_point',
		] );
		
		// Normalise les véhicules
		$meta['tm_vehicles'] = self::normalize_ids_array( $meta['tm_vehicles'] ?? null );
		
		// Normalise les entiers
		$meta['tm_duration_minutes'] = ! empty( $meta['tm_duration_minutes'] ) ? absint( $meta['tm_duration_minutes'] ) : 0;
		
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
			'tm_transfer_type',
			'tm_vehicle',
			'tm_price',
			'tm_pickup',
			'tm_dropoff',
			'tm_duration_estimate',
			'tm_description',
		] );
		
		// Normalise le véhicule (ID unique)
		$meta['tm_vehicle'] = ! empty( $meta['tm_vehicle'] ) ? absint( $meta['tm_vehicle'] ) : 0;
		
		return $meta;
	}
}

