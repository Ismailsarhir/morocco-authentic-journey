<?php
/**
 * Vehicles List class for rendering the featured vehicles section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;
use TM\Repository\PostRepository;
use TM\Utils\MetaHelper;

/**
 * Classe pour gérer le rendu de la liste des véhicules vedettes
 */
class VehiclesList {
	
	/**
	 * Nombre maximum de véhicules à afficher
	 * 
	 * @var int
	 */
	private const MAX_VEHICLES = 6;
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var VehiclesList|null
	 */
	private static ?VehiclesList $instance = null;
	
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
	 * @return VehiclesList
	 */
	public static function get_instance(): VehiclesList {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère les véhicules pour la liste des véhicules vedettes
	 * 
	 * @param int $limit Nombre de véhicules à récupérer
	 * @return array
	 */
	private function get_featured_vehicles( int $limit = self::MAX_VEHICLES ): array {
		// Request more vehicles to account for those without images
		$request_limit = $limit * 3;
		
		$vehicles = $this->repository->get_by_args( 'vehicules', [
			'posts_per_page' => $request_limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		] );
		
		if ( empty( $vehicles ) ) {
			return [];
		}
		
		$featured_vehicles = [];
		
		foreach ( $vehicles as $vehicle ) {
			if ( count( $featured_vehicles ) >= $limit ) {
				break;
			}
			
			if ( ! $vehicle instanceof \WP_Post || $vehicle->post_status !== 'publish' ) {
				continue;
			}
			
			$vehicle_id = $vehicle->ID;
			
			$vehicle_meta = MetaHelper::get_vehicle_meta( $vehicle_id );
			
			$thumbnail_url = \get_the_post_thumbnail_url( $vehicle_id, 'large' )
				?: \get_the_post_thumbnail_url( $vehicle_id, 'medium' )
				?: \get_the_post_thumbnail_url( $vehicle_id, 'thumbnail' );
			
			// Skip vehicles without featured image
			if ( ! $thumbnail_url ) {
				continue;
			}
			
			$title = \get_the_title( $vehicle_id );
			$permalink = \get_permalink( $vehicle_id );
			
			if ( empty( $title ) || empty( $permalink ) ) {
				continue;
			}
			
			$daily_price = $vehicle_meta['tm_daily_price'] ?? '';
			$gallery_ids = $vehicle_meta['tm_gallery'] ?? [];
			
			// Récupère les URLs de la galerie
			$gallery_urls = [];
			if ( ! empty( $gallery_ids ) && is_array( $gallery_ids ) ) {
				foreach ( $gallery_ids as $gallery_id ) {
					$gallery_id = (int) $gallery_id;
					if ( $gallery_id > 0 ) {
						$image_url = \wp_get_attachment_image_url( $gallery_id, 'large' );
						if ( $image_url ) {
							$gallery_urls[] = $image_url;
						}
					}
				}
			}
			
			$featured_vehicles[] = [
				'vehicle_id'      => $vehicle_id,
				'title'           => $title,
				'permalink'       => $permalink,
				'thumbnail'       => $thumbnail_url,
				'type'            => $vehicle_meta['tm_vehicle_type'] ?? '',
				'seats'           => $vehicle_meta['tm_seats'] ?? 0,
				'baggage_capacity' => $vehicle_meta['tm_baggage_capacity'] ?? '',
				'daily_price'     => $daily_price,
				'daily_price_formatted' => ! empty( $daily_price ) ? number_format( floatval( $daily_price ), 0, ',', ' ' ) : '',
				'availability'    => $vehicle_meta['tm_availability'] ?? false,
				'gallery'         => $gallery_urls,
			];
		}
		
		return $featured_vehicles;
	}
	
	/**
	 * Rend la liste des véhicules vedettes
	 * 
	 * @return void
	 */
	public function render(): void {
		$vehicles = $this->get_featured_vehicles( self::MAX_VEHICLES );
		
		if ( empty( $vehicles ) ) {
			return;
		}
		
		$this->renderer->render( 'vehicles-list', [
			'vehicles' => $vehicles,
		] );
	}
	
	/**
	 * Vérifie si des véhicules doivent être affichés
	 * 
	 * @return bool
	 */
	public function has_vehicles(): bool {
		$vehicles = $this->get_featured_vehicles( 1 );
		return ! empty( $vehicles );
	}
}

