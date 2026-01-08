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
	 * Formate les données d'un tour pour l'affichage
	 * 
	 * @param \WP_Post $tour Post du tour
	 * @return array|null Données formatées du tour ou null si invalide
	 */
	public function format_tour_data( \WP_Post $tour ): ?array {
		$tour_id = $tour->ID;
		$tour_meta = MetaHelper::get_tour_meta( $tour_id );
		
		$thumbnail_url = MetaHelper::get_post_thumbnail_url_with_fallback( $tour_id );
		if ( ! $thumbnail_url ) {
			return null; // Skip tours without thumbnail
		}
		
		$price_tiers = $tour_meta[ Constants::META_TOUR_PRICE_TIERS ] ?? [];
		
		// Récupère le prix minimum depuis les tiers (gère les valeurs numériques et textuelles)
		$min_price = '';
		$price_text = '';
		if ( ! empty( $price_tiers ) && is_array( $price_tiers ) ) {
			$prices = array_filter( array_column( $price_tiers, 'price' ) );
			if ( ! empty( $prices ) ) {
				$numeric_prices = [];
				foreach ( $prices as $price ) {
					if ( is_numeric( $price ) ) {
						$numeric_prices[] = (float) $price;
					} elseif ( empty( $price_text ) ) {
						// Prend la première valeur textuelle trouvée
						$price_text = $price;
					}
				}
				
				if ( ! empty( $numeric_prices ) ) {
					$min_price = min( $numeric_prices );
				} elseif ( ! empty( $price_text ) ) {
					$min_price = $price_text;
				}
			}
		}
		
		// Récupération des tags/catégories
		$tags = $tour_meta[ Constants::META_TOUR_TAGS ] ?? [];
		$tag_labels = [];
		if ( ! empty( $tags ) && is_array( $tags ) ) {
			// Mapping des valeurs de tags vers leurs labels
			$tag_options = [
				'photography'      => __( 'Photography', 'transfertmarrakech' ),
				'historical'       => __( 'Historical', 'transfertmarrakech' ),
				'sightseeing'      => __( 'Sightseeing', 'transfertmarrakech' ),
				'adventure'        => __( 'Adventure', 'transfertmarrakech' ),
				'adventure sports' => __( 'Adventure Sports', 'transfertmarrakech' ),
				'Paragliding'      => __( 'Paragliding', 'transfertmarrakech' ),
				'ballooning'       => __( 'Ballooning', 'transfertmarrakech' ),
				'architectural'    => __( 'Architectural', 'transfertmarrakech' ),
				'cultural'         => __( 'Cultural', 'transfertmarrakech' ),
				'nature'           => __( 'Nature', 'transfertmarrakech' ),
				'gastronomical'    => __( 'Gastronomical', 'transfertmarrakech' ),
				'Desert'           => __( 'Desert', 'transfertmarrakech' ),
				'atv'              => __( 'ATV', 'transfertmarrakech' ),
			];
			
			foreach ( $tags as $tag_value ) {
				if ( ! empty( $tag_value ) && isset( $tag_options[ $tag_value ] ) ) {
					$tag_labels[] = $tag_options[ $tag_value ];
				}
			}
		}
		
		// Récupération des langues
		$languages = $tour_meta[ Constants::META_TOUR_LANGUAGES ] ?? [];
		$language_labels = [];
		if ( ! empty( $languages ) && is_array( $languages ) ) {
			// Mapping des valeurs de langues vers leurs labels
			$language_options = [
				'english'   => __( 'English', 'transfertmarrakech' ),
				'french'    => __( 'French', 'transfertmarrakech' ),
				'spanish'   => __( 'Spanish', 'transfertmarrakech' ),
				'arabic'    => __( 'Arabic', 'transfertmarrakech' ),
				'german'    => __( 'German', 'transfertmarrakech' ),
				'italian'   => __( 'Italian', 'transfertmarrakech' ),
				'slovenian' => __( 'Slovenian', 'transfertmarrakech' ),
				'dutch'     => __( 'Dutch', 'transfertmarrakech' ),
			];
			
			foreach ( $languages as $lang_value ) {
				if ( ! empty( $lang_value ) && isset( $language_options[ $lang_value ] ) ) {
					$language_labels[] = $language_options[ $lang_value ];
				}
			}
		}
		
		// Récupération de la difficulté
		$difficulty = $tour_meta[ Constants::META_TOUR_DIFFICULTY ] ?? '';
		$difficulty_label = '';
		if ( ! empty( $difficulty ) ) {
			$difficulty_options = [
				'easy'   => __( 'Easy', 'transfertmarrakech' ),
				'medium' => __( 'Medium', 'transfertmarrakech' ),
				'hard'   => __( 'Hard', 'transfertmarrakech' ),
			];
			$difficulty_label = $difficulty_options[ $difficulty ] ?? '';
		}
		
		// Optimisation : utilise post_title directement
		$tour_title = MetaHelper::get_post_title( $tour );
		
		// Formate la durée avec l'unité appropriée
		$duration_raw = $tour_meta[ Constants::META_TOUR_DURATION ] ?? '';
		$duration_unit = $tour_meta[ Constants::META_TOUR_DURATION_UNIT ] ?? 'hours';
		$duration_formatted = ! empty( $duration_raw ) ? MetaHelper::format_duration( $duration_raw, $duration_unit ) : '';
		
		return [
			'tour'         => $tour,
			'tour_id'      => $tour_id,
			'title'        => $tour_title,
			'permalink'    => \get_permalink( $tour_id ),
			'thumbnail'    => $thumbnail_url,
			'duration'     => $duration_formatted,
			'price'        => $min_price,
			'price_formatted' => $min_price ? ( is_numeric( $min_price ) ? MetaHelper::format_price_usd( $min_price ) : $min_price ) : '',
			'location'     => $tour_meta[ Constants::META_TOUR_LOCATION ] ?? '',
			'tag_labels'   => $tag_labels,
			'language_labels' => $language_labels,
			'difficulty'   => $difficulty_label,
		];
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
			
			$tour_data = $this->format_tour_data( $tour );
			if ( $tour_data ) {
				$featured_tours[] = $tour_data;
			}
		}
		
		return $featured_tours;
	}
	
	/**
	 * Récupère tous les tours pour l'archive
	 * 
	 * @param array $args Arguments de requête WordPress supplémentaires
	 * @return array
	 */
	public function get_all_tours( array $args = [] ): array {
		$default_args = [
			'posts_per_page' => -1, // Tous les tours
			'orderby'        => 'date',
			'order'          => 'DESC',
		];
		
		$query_args = array_merge( $default_args, $args );
		
		$tours = $this->repository->get_by_args( Constants::POST_TYPE_TOUR, $query_args );
		
		if ( empty( $tours ) ) {
			return [];
		}
		
		$all_tours = [];
		
		foreach ( $tours as $tour ) {
			if ( ! $tour instanceof \WP_Post ) {
				continue;
			}
			
			$tour_data = $this->format_tour_data( $tour );
			if ( $tour_data ) {
				$all_tours[] = $tour_data;
			}
		}
		
		return $all_tours;
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
