<?php
/**
 * Tours List class for rendering the featured tours section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;
use TM\Repository\PostRepository;
use TM\Utils\MetaHelper;

/**
 * Classe pour gérer le rendu de la liste des tours vedettes
 */
class ToursList {
	
	/**
	 * Nombre maximum de tours à afficher
	 * 
	 * @var int
	 */
	private const MAX_TOURS = 6;
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var ToursList|null
	 */
	private static ?ToursList $instance = null;
	
	/**
	 * Renderer pour les templates
	 * 
	 * @var Renderer
	 */
	private Renderer $renderer;
	
	/**
	 * Repository pour récupérer les posts
	 * 
	 * @var PostRepository
	 */
	private PostRepository $repository;
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		$this->renderer = new Renderer();
		$this->repository = PostRepository::get_instance();
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return ToursList
	 */
	public static function get_instance(): ToursList {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère les tours pour la liste des tours vedettes
	 * 
	 * @param int $limit Nombre de tours à récupérer
	 * @return array
	 */
	private function get_featured_tours( int $limit = self::MAX_TOURS ): array {
		$tours = $this->repository->get_by_args( Constants::POST_TYPE_TOUR, [
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		
		if ( empty( $tours ) ) {
			return [];
		}
		
		$featured_tours = [];
		
		foreach ( $tours as $tour ) {
			if ( ! $tour instanceof \WP_Post ) {
				continue;
			}
			
			$tour_id = $tour->ID;
			$tour_meta = MetaHelper::get_tour_meta( $tour_id );
			
			$thumbnail_url = MetaHelper::get_post_thumbnail_url_with_fallback( $tour_id );
			if ( ! $thumbnail_url ) {
				continue; // Skip tours without thumbnail
			}
			
			$price_tiers = $tour_meta[ Constants::META_TOUR_PRICE_TIERS ] ?? [];
			
			// Récupère le prix minimum depuis les tiers
			$min_price = '';
			if ( ! empty( $price_tiers ) && is_array( $price_tiers ) ) {
				$prices = array_filter( array_column( $price_tiers, 'price' ) );
				if ( ! empty( $prices ) ) {
					$min_price = min( array_map( 'floatval', $prices ) );
				}
			}
			
			// Récupération des tags/catégories
			$tags = $tour_meta[ Constants::META_TOUR_TAGS ] ?? [];
			$tag_labels = [];
			if ( ! empty( $tags ) && is_array( $tags ) ) {
				// Mapping des valeurs de tags vers leurs labels
				$tag_options = [
					'photography' => __( 'Photography', 'transfertmarrakech' ),
					'historical'  => __( 'Historical', 'transfertmarrakech' ),
					'sightseeing' => __( 'Sightseeing', 'transfertmarrakech' ),
					'adventure'   => __( 'Adventure', 'transfertmarrakech' ),
					'cultural'    => __( 'Cultural', 'transfertmarrakech' ),
					'nature'      => __( 'Nature', 'transfertmarrakech' ),
				];
				
				foreach ( $tags as $tag_value ) {
					if ( ! empty( $tag_value ) && isset( $tag_options[ $tag_value ] ) ) {
						$tag_labels[] = $tag_options[ $tag_value ];
					}
				}
			}
			
			// Optimisation : utilise post_title directement
			$tour_title = MetaHelper::get_post_title( $tour );
			
			// Formate la durée avec l'unité appropriée
			$duration_raw = $tour_meta[ Constants::META_TOUR_DURATION ] ?? '';
			$duration_unit = $tour_meta[ Constants::META_TOUR_DURATION_UNIT ] ?? 'hours';
			$duration_formatted = ! empty( $duration_raw ) ? MetaHelper::format_duration( $duration_raw, $duration_unit ) : '';
			
			$featured_tours[] = [
				'tour'         => $tour,
				'tour_id'      => $tour_id,
				'title'        => $tour_title,
				'permalink'    => \get_permalink( $tour_id ),
				'thumbnail'    => $thumbnail_url,
				'duration'     => $duration_formatted,
				'price'        => $min_price,
				'price_formatted' => $min_price ? MetaHelper::format_price_usd( $min_price ) : '',
				'location'     => $tour_meta[ Constants::META_TOUR_LOCATION ] ?? '',
				'tag_labels'   => $tag_labels,
			];
		}
		
		return $featured_tours;
	}
	
	/**
	 * Rend la liste des tours vedettes
	 * 
	 * @return void
	 */
	public function render(): void {
		$tours = $this->get_featured_tours( self::MAX_TOURS );
		
		if ( empty( $tours ) ) {
			return;
		}
		
		$this->renderer->render( 'tours-list', [
			'tours' => $tours,
		] );
	}
	
	/**
	 * Vérifie si des tours doivent être affichés
	 * 
	 * @return bool
	 */
	public function has_tours(): bool {
		$tours = $this->get_featured_tours( 1 );
		return ! empty( $tours );
	}
}
