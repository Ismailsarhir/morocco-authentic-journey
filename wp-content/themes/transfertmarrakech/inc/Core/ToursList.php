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
		$tours = $this->repository->get_by_args( 'tours', [
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
			
			$thumbnail_url = \get_the_post_thumbnail_url( $tour_id, 'large' );
			if ( ! $thumbnail_url ) {
				continue; // Skip tours without thumbnail
			}
			
			$price = $tour_meta['tm_price'] ?? '';
			
			// Récupération des noms des véhicules associés (optimisé)
			$vehicle_names = [];
			$vehicle_posts = $this->repository->get_related_vehicles_for_tour( $tour_id );
			if ( ! empty( $vehicle_posts ) ) {
				foreach ( $vehicle_posts as $vehicle ) {
					if ( $vehicle instanceof \WP_Post ) {
						$vehicle_names[] = $vehicle->post_title ?: \get_the_title( $vehicle->ID );
					}
				}
			}
			
			// Optimisation : utilise post_title directement
			$tour_title = $tour->post_title ?: \get_the_title( $tour_id );
			
			$featured_tours[] = [
				'tour'         => $tour,
				'tour_id'      => $tour_id,
				'title'        => $tour_title,
				'permalink'    => \get_permalink( $tour_id ),
				'thumbnail'    => $thumbnail_url,
				'duration'     => $tour_meta['tm_duration'] ?? '',
				'days'         => $tour_meta['tm_duration_minutes'] ?? 0,
				'price'        => $price,
				'price_formatted' => ! empty( $price ) ? \number_format( (float) $price, 0, ',', ' ' ) : '',
				'location'     => $tour_meta['tm_location'] ?? '',
				'vehicle_names' => $vehicle_names,
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

