<?php
/**
 * Repository pour récupérer les posts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Repository;

/**
 * Classe Repository pour les requêtes de posts
 * Utilise le pattern Singleton pour partager une instance unique
 */
class PostRepository {
	
	/**
	 * Instance unique du repository (Singleton)
	 * 
	 * @var PostRepository|null
	 */
	private static ?PostRepository $instance = null;
	
	/**
	 * Récupère l'instance unique du repository
	 * 
	 * @return PostRepository
	 */
	public static function get_instance(): PostRepository {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		// Empêche l'instanciation directe
	}
	
	/**
	 * Récupère les posts par arguments
	 * 
	 * @param string $post_type Post type
	 * @param array  $args      Arguments WP_Query
	 * @return array
	 */
	public function get_by_args( string $post_type, array $args = [] ): array {
		$defaults = [
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true, // Optimisation : ne compte pas le total (économise une requête SQL)
			'update_post_meta_cache' => true, // Optimisation : charge les meta en une fois
			'update_post_term_cache' => false, // Optimisation : pas besoin des termes ici
		];
		
		$query_args = \wp_parse_args( $args, $defaults );
		$query      = new \WP_Query( $query_args );
		
		$posts = $query->posts;
		
		// Nettoie la requête pour libérer la mémoire
		\wp_reset_postdata();
		
		return $posts;
	}
	
	/**
	 * Récupère un post par ID
	 * 
	 * @param int $post_id ID du post
	 * @return WP_Post|null
	 */
	public function get_by_id( int $post_id ) {
		$post = \get_post( $post_id );
		return $post && $post->post_status === 'publish' ? $post : null;
	}
	
	/**
	 * Récupère les véhicules liés à un tour
	 * 
	 * @param int $tour_id ID du tour
	 * @return array
	 */
	public function get_related_vehicles_for_tour( int $tour_id ): array {
		$tour_meta = \TM\Utils\MetaHelper::get_tour_meta( $tour_id );
		$vehicle_ids = $tour_meta['tm_vehicles'] ?? [];
		
		if ( empty( $vehicle_ids ) || ! is_array( $vehicle_ids ) ) {
			return [];
		}
		
		// Normalise et filtre les IDs valides
		$vehicle_ids = array_values( array_filter( array_map( 'absint', $vehicle_ids ), function( $id ) {
			return $id > 0;
		} ) );
		
		if ( empty( $vehicle_ids ) ) {
			return [];
		}
		
		return $this->get_by_args( 'vehicules', [
			'post__in' => $vehicle_ids,
			'orderby'  => 'post__in',
		] );
	}
	
	/**
	 * Récupère les véhicules disponibles
	 * 
	 * @param int $limit Limite de résultats
	 * @return array
	 */
	public function get_available_vehicles( int $limit = -1 ): array {
		return $this->get_by_args( 'vehicules', [
			'posts_per_page' => $limit,
			'meta_query'      => [
				[
					'key'   => 'tm_availability',
					'value' => '1',
					'compare' => '=',
				],
			],
		] );
	}
	
	/**
	 * Récupère les tours par localisation
	 * 
	 * @param string $location Localisation
	 * @return array
	 */
	public function get_tours_by_location( string $location ): array {
		return $this->get_by_args( 'tours', [
			'meta_query' => [
				[
					'key'     => 'tm_location',
					'value'   => $location,
					'compare' => 'LIKE',
				],
			],
		] );
	}
	
	/**
	 * Récupère les transferts par type
	 * 
	 * @param string $type Type de transfert
	 * @return array
	 */
	public function get_transfers_by_type( string $type ): array {
		return $this->get_by_args( 'transferts', [
			'meta_query' => [
				[
					'key'     => 'tm_transfer_type',
					'value'   => $type,
					'compare' => '=',
				],
			],
		] );
	}
	
	/**
	 * Formate un post pour l'affichage
	 * Optimisé pour réduire les appels de fonctions WordPress
	 * 
	 * @param WP_Post $post Objet post
	 * @return array
	 */
	public function format_post( $post ): array {
		if ( ! $post instanceof \WP_Post ) {
			return [];
		}
		
		// Optimisation : utilise les propriétés de l'objet post directement
		$post_id = $post->ID;
		$title = $post->post_title;
		$excerpt = $post->post_excerpt;
		
		return [
			'id'          => $post_id,
			'title'       => $title ?: \get_the_title( $post_id ),
			'content'     => \apply_filters( 'the_content', $post->post_content ),
			'excerpt'     => $excerpt ?: \get_the_excerpt( $post_id ),
			'permalink'   => \get_permalink( $post_id ),
			'thumbnail'   => \get_the_post_thumbnail_url( $post_id, 'medium' ),
			'date'        => \get_the_date( '', $post_id ),
			'modified'    => \get_the_modified_date( '', $post_id ),
		];
	}
}

