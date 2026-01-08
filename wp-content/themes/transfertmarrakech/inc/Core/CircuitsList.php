<?php
/**
 * Circuits List class for rendering the featured circuits section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;
use TM\Repository\PostRepository;
use TM\Utils\MetaHelper;

/**
 * Classe pour gérer le rendu de la liste des circuits vedettes
 */
class CircuitsList {
	
	/**
	 * Nombre maximum de circuits à afficher
	 * 
	 * @var int
	 */
	private const MAX_CIRCUITS = 6;
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var CircuitsList|null
	 */
	private static ?CircuitsList $instance = null;
	
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
	 * @return CircuitsList
	 */
	public static function get_instance(): CircuitsList {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Formate les données d'un circuit pour l'affichage
	 * 
	 * @param \WP_Post $circuit Post du circuit
	 * @return array|null Données formatées du circuit ou null si invalide
	 */
	public function format_circuit_data( \WP_Post $circuit ): ?array {
		$circuit_id = $circuit->ID;
		$circuit_meta = MetaHelper::get_circuit_meta( $circuit_id );
		
		$thumbnail_url = MetaHelper::get_post_thumbnail_url_with_fallback( $circuit_id );
		if ( ! $thumbnail_url ) {
			return null; // Skip circuits without thumbnail
		}
		
		$price_tiers = $circuit_meta[ Constants::META_CIRCUIT_PRICE_TIERS ] ?? [];
		
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
		
		// Optimisation : utilise post_title directement
		$circuit_title = MetaHelper::get_post_title( $circuit );
		
		// Formate la durée en jours
		$duration_days = $circuit_meta[ Constants::META_CIRCUIT_DURATION_DAYS ] ?? '';
		$duration_formatted = ! empty( $duration_days ) ? sprintf( 
			_n( '%d jour', '%d jours', (int) $duration_days, 'transfertmarrakech' ), 
			(int) $duration_days 
		) : '';
		
		// Récupération des langues
		$languages = $circuit_meta[ Constants::META_CIRCUIT_LANGUAGES ] ?? [];
		$language_labels = [];
		if ( ! empty( $languages ) && is_array( $languages ) ) {
			// Mapping des valeurs de langues vers leurs labels
			$language_options = [
				'english'   => __( 'English', 'transfertmarrakech' ),
				'french'    => __( 'French', 'transfertmarrakech' ),
				'spanish'   => __( 'Spanish', 'transfertmarrakech' ),
				'portuguese' => __( 'Portuguese', 'transfertmarrakech' ),
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
		
		// Récupération des tags/catégories
		$tags = $circuit_meta[ Constants::META_CIRCUIT_TAGS ] ?? [];
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
		
		// Récupération de la difficulté
		$difficulty = $circuit_meta[ Constants::META_CIRCUIT_DIFFICULTY ] ?? '';
		$difficulty_label = '';
		if ( ! empty( $difficulty ) ) {
			$difficulty_options = [
				'easy'   => __( 'Easy', 'transfertmarrakech' ),
				'medium' => __( 'Medium', 'transfertmarrakech' ),
				'hard'   => __( 'Hard', 'transfertmarrakech' ),
			];
			$difficulty_label = $difficulty_options[ $difficulty ] ?? '';
		}
		
		return [
			'circuit'         => $circuit,
			'circuit_id'      => $circuit_id,
			'title'           => $circuit_title,
			'permalink'       => \get_permalink( $circuit_id ),
			'thumbnail'       => $thumbnail_url,
			'duration'        => $duration_formatted,
			'price'           => $min_price,
			'price_formatted' => $min_price ? ( is_numeric( $min_price ) ? MetaHelper::format_price_usd( $min_price ) : $min_price ) : '',
			'location'        => $circuit_meta[ Constants::META_CIRCUIT_LOCATION ] ?? '',
			'language_labels' => $language_labels,
			'tag_labels'      => $tag_labels,
			'difficulty'      => $difficulty_label,
		];
	}
	
	/**
	 * Récupère les circuits pour la liste des circuits vedettes
	 * 
	 * @param int $limit Nombre de circuits à récupérer
	 * @return array
	 */
	private function get_featured_circuits( int $limit = self::MAX_CIRCUITS ): array {
		$circuits = $this->repository->get_by_args( Constants::POST_TYPE_CIRCUIT, [
			'posts_per_page' => $limit,
			'orderby'        => 'date',
			'order'          => 'DESC',
		] );
		
		if ( empty( $circuits ) ) {
			return [];
		}
		
		$featured_circuits = [];
		
		foreach ( $circuits as $circuit ) {
			if ( ! $circuit instanceof \WP_Post ) {
				continue;
			}
			
			$circuit_data = $this->format_circuit_data( $circuit );
			if ( $circuit_data ) {
				$featured_circuits[] = $circuit_data;
			}
		}
		
		return $featured_circuits;
	}
	
	/**
	 * Récupère tous les circuits pour l'archive
	 * 
	 * @param array $args Arguments de requête WordPress supplémentaires
	 * @return array
	 */
	public function get_all_circuits( array $args = [] ): array {
		$default_args = [
			'posts_per_page' => -1, // Tous les circuits
			'orderby'        => 'date',
			'order'          => 'DESC',
		];
		
		$query_args = array_merge( $default_args, $args );
		
		$circuits = $this->repository->get_by_args( Constants::POST_TYPE_CIRCUIT, $query_args );
		
		if ( empty( $circuits ) ) {
			return [];
		}
		
		$all_circuits = [];
		
		foreach ( $circuits as $circuit ) {
			if ( ! $circuit instanceof \WP_Post ) {
				continue;
			}
			
			$circuit_data = $this->format_circuit_data( $circuit );
			if ( $circuit_data ) {
				$all_circuits[] = $circuit_data;
			}
		}
		
		return $all_circuits;
	}
	
	/**
	 * Rend la liste des circuits vedettes
	 * 
	 * @return void
	 */
	public function render(): void {
		$circuits = $this->get_featured_circuits( self::MAX_CIRCUITS );
		
		if ( empty( $circuits ) ) {
			return;
		}
		
		$this->renderer->render( 'circuits-list', [
			'circuits' => $circuits,
		] );
	}
	
	/**
	 * Vérifie si des circuits doivent être affichés
	 * 
	 * @return bool
	 */
	public function has_circuits(): bool {
		$circuits = $this->get_featured_circuits( 1 );
		return ! empty( $circuits );
	}
}

