<?php
/**
 * Transfers List class for rendering the featured transfers section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;
use TM\Repository\PostRepository;
use TM\Utils\MetaHelper;

/**
 * Classe pour gérer le rendu de la liste des transferts vedettes
 */
class TransfersList {
	
	/**
	 * Nombre maximum de transferts à afficher
	 * 
	 * @var int
	 */
	private const MAX_TRANSFERS = 4;
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var TransfersList|null
	 */
	private static ?TransfersList $instance = null;
	
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
	 * @return TransfersList
	 */
	public static function get_instance(): TransfersList {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère les transferts pour la liste des transferts vedettes
	 * 
	 * @param int $limit Nombre de transferts à récupérer
	 * @return array
	 */
	private function get_featured_transfers( int $limit = self::MAX_TRANSFERS ): array {
		// Request more transfers to account for those without images
		$request_limit = $limit * 3;
		
		$transfers = $this->repository->get_by_args( 'transferts', [
			'posts_per_page' => $request_limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'post_status'    => 'publish',
		] );
		
		if ( empty( $transfers ) ) {
			return [];
		}
		
		$featured_transfers = [];
		
		foreach ( $transfers as $transfer ) {
			if ( count( $featured_transfers ) >= $limit ) {
				break;
			}
			
			if ( ! $transfer instanceof \WP_Post || $transfer->post_status !== 'publish' ) {
				continue;
			}
			
			$transfer_id = $transfer->ID;
			$transfer_meta = MetaHelper::get_transfer_meta( $transfer_id );
			
			$thumbnail_url = \get_the_post_thumbnail_url( $transfer_id, 'large' )
				?: \get_the_post_thumbnail_url( $transfer_id, 'medium' )
				?: \get_the_post_thumbnail_url( $transfer_id, 'thumbnail' );
			
			// Skip transfers without thumbnail
			if ( ! $thumbnail_url ) {
				continue;
			}
			
			$title = \get_the_title( $transfer_id );
			$permalink = \get_permalink( $transfer_id );
			
			if ( empty( $title ) || empty( $permalink ) ) {
				continue;
			}
			
			// Récupération du véhicule associé
			$vehicle_id = $transfer_meta['tm_vehicle'] ?? 0;
			$vehicle_name = '';
			if ( $vehicle_id > 0 ) {
				$vehicle = $this->repository->get_by_id( (int) $vehicle_id );
				if ( $vehicle ) {
					$vehicle_name = \get_the_title( $vehicle->ID );
				}
			}
			
			$price = $transfer_meta['tm_price'] ?? '';
			
			$featured_transfers[] = [
				'transfer'      => $transfer,
				'transfer_id'   => $transfer_id,
				'title'         => $title,
				'permalink'     => $permalink,
				'thumbnail'     => $thumbnail_url,
				'type'          => $transfer_meta['tm_transfer_type'] ?? '',
				'pickup'        => $transfer_meta['tm_pickup'] ?? '',
				'dropoff'       => $transfer_meta['tm_dropoff'] ?? '',
				'duration'      => $transfer_meta['tm_duration_estimate'] ?? '',
				'price'         => $price,
				'price_formatted' => ! empty( $price ) ? number_format( floatval( $price ), 0, ',', ' ' ) : '',
				'vehicle_id'    => $vehicle_id,
				'vehicle_name'  => $vehicle_name,
			];
		}
		
		return $featured_transfers;
	}
	
	/**
	 * Rend la liste des transferts vedettes
	 * 
	 * @return void
	 */
	public function render(): void {
		$transfers = $this->get_featured_transfers( self::MAX_TRANSFERS );
		
		if ( empty( $transfers ) ) {
			return;
		}
		
		$this->renderer->render( 'transfers-list', [
			'transfers' => $transfers,
		] );
	}
	
	/**
	 * Vérifie si des transferts doivent être affichés
	 * 
	 * @return bool
	 */
	public function has_transfers(): bool {
		$transfers = $this->get_featured_transfers( 1 );
		return ! empty( $transfers );
	}
}

