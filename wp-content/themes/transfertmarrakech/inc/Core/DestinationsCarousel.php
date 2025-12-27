<?php
/**
 * Destinations Carousel class for rendering the destinations section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;

/**
 * Classe pour gérer le rendu du carrousel des destinations
 */
class DestinationsCarousel {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var DestinationsCarousel|null
	 */
	private static ?DestinationsCarousel $instance = null;
	
	/**
	 * Renderer pour les templates
	 * 
	 * @var Renderer
	 */
	private Renderer $renderer;
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		$this->renderer = new Renderer();
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return DestinationsCarousel
	 */
	public static function get_instance(): DestinationsCarousel {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère les termes de la taxonomy tour_location avec leurs images
	 * Seulement les termes qui ont une image définie
	 * 
	 * @return array
	 */
	private function get_destinations(): array {
		$terms = \get_terms( [
			'taxonomy'               => 'tour_location',
			'hide_empty'             => false, // Récupère tous les termes, même sans posts associés
			'orderby'                 => 'name',
			'order'                   => 'ASC',
			'update_term_meta_cache' => true, // Active le cache des meta pour améliorer les performances
		] );
		
		if ( \is_wp_error( $terms ) ) {
			// Log l'erreur en mode debug
			if ( \defined( 'WP_DEBUG' ) && \WP_DEBUG ) {
				\error_log( 'DestinationsCarousel Error: ' . $terms->get_error_message() );
			}
			return [];
		}
		
		if ( empty( $terms ) ) {
			return [];
		}
		
		// Note: update_term_meta_cache => true dans get_terms() charge déjà les meta en cache
		// WordPress n'a pas de fonction update_term_meta_cache() séparée comme pour les posts
		
		$destinations = [];
		
		foreach ( $terms as $term ) {
			// Vérifie que c'est bien un objet WP_Term
			if ( ! $term instanceof \WP_Term ) {
				continue;
			}
			
			$image = $this->get_term_image( $term );
			
			// Ne garde que les destinations qui ont une image
			if ( ! $image ) {
				continue;
			}
			
			$term_link = \get_term_link( $term );
			
			// Vérifie que le lien est valide
			if ( \is_wp_error( $term_link ) ) {
				continue;
			}
			
			$destinations[] = [
				'term'       => $term,
				'name'       => $term->name,
				'slug'       => $term->slug,
				'url'        => $term_link,
				'image'      => $image,
				'image_alt'  => $this->get_term_image_alt( $term, $image ),
			];
		}
		
		return $destinations;
	}
	
	/**
	 * Récupère l'image d'un terme depuis term meta uniquement
	 * 
	 * @param \WP_Term $term Terme
	 * @return array|null Tableau avec 'url', 'srcset' ou null
	 */
	private function get_term_image( \WP_Term $term ): ?array {
		// Récupère l'image depuis term meta uniquement
		$term_image_id = \get_term_meta( $term->term_id, 'tm_term_image', true );
		
		if ( ! $term_image_id || ! \is_numeric( $term_image_id ) ) {
			return null;
		}
		
		// Vérifie que l'attachment existe
		$attachment = \get_post( $term_image_id );
		if ( ! $attachment || $attachment->post_type !== 'attachment' ) {
			return null;
		}
		
		$image_url = \wp_get_attachment_image_url( $term_image_id, 'large' );
		
		if ( ! $image_url ) {
			return null;
		}
		
		$image_srcset = \wp_get_attachment_image_srcset( $term_image_id, 'large' );
		
		return [
			'url'    => $image_url,
			'srcset' => $image_srcset ?: '',
			'id'     => $term_image_id,
		];
	}
	
	/**
	 * Récupère le texte alternatif de l'image
	 * 
	 * @param \WP_Term $term Terme
	 * @param array|null $image Données de l'image
	 * @return string
	 */
	private function get_term_image_alt( \WP_Term $term, ?array $image ): string {
		if ( $image && isset( $image['id'] ) ) {
			$alt = \get_post_meta( $image['id'], '_wp_attachment_image_alt', true );
			if ( ! empty( $alt ) ) {
				return $alt;
			}
		}
		
		// Fallback vers le nom du terme
		return \sprintf( 
			/* translators: %s: Destination name */
			\__( 'Image de %s', 'transfertmarrakech' ),
			$term->name
		);
	}
	
	/**
	 * Rend le carrousel des destinations
	 * 
	 * @return void
	 */
	public function render(): void {
		$destinations = $this->get_destinations();
		
		// Debug mode - affiche des infos si WP_DEBUG est activé
		if ( \defined( 'WP_DEBUG' ) && \WP_DEBUG && empty( $destinations ) ) {
			$terms = \get_terms( [
				'taxonomy'               => 'tour_location',
				'hide_empty'             => false,
				'update_term_meta_cache' => true,
			] );
			
			if ( ! \is_wp_error( $terms ) && ! empty( $terms ) ) {
				\error_log( 'DestinationsCarousel Debug: Found ' . count( $terms ) . ' terms' );
				foreach ( $terms as $term ) {
					$image_id = \get_term_meta( $term->term_id, 'tm_term_image', true );
					\error_log( sprintf( 
						'Term "%s" (ID: %d) - Image ID: %s', 
						$term->name, 
						$term->term_id, 
						$image_id ?: 'none' 
					) );
				}
			}
		}
		
		if ( empty( $destinations ) ) {
			return;
		}
		
		$this->renderer->render( 'destinations-carousel', [
			'destinations' => $destinations,
		] );
	}
	
	/**
	 * Vérifie si des destinations doivent être affichées
	 * 
	 * @return bool
	 */
	public function has_destinations(): bool {
		$destinations = $this->get_destinations();
		return ! empty( $destinations );
	}
}

