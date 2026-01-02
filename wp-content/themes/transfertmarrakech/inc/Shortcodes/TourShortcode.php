<?php
/**
 * Shortcode pour afficher les tours
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Shortcodes;

use TM\Core\Constants;

/**
 * Classe pour le shortcode [tm_tours]
 */
class TourShortcode extends BaseShortcode {
	
	/**
	 * Enregistre le shortcode
	 * 
	 * @return void
	 */
	public function register(): void {
		\add_shortcode( 'tm_tours', [ $this, 'render' ] );
	}
	
	/**
	 * Affiche le shortcode
	 * 
	 * @param array  $atts    Attributs du shortcode
	 * @param string $content Contenu du shortcode
	 * @return string
	 */
	public function render( array $atts = [], string $content = '' ): string {
		$atts = \shortcode_atts( [
			'limit'    => -1,
			'location' => '',
		], $atts, 'tm_tours' );
		
		$args = [
			'posts_per_page' => (int) $atts['limit'],
		];
		
		// Filtre par localisation
		if ( ! empty( $atts['location'] ) ) {
			$args['meta_query'] = [
				[
					'key'     => Constants::META_TOUR_LOCATION,
					'value'   => \sanitize_text_field( $atts['location'] ),
					'compare' => 'LIKE',
				],
			];
		}
		
		$tours = self::$repository->get_by_args( Constants::POST_TYPE_TOUR, $args );
		
		if ( empty( $tours ) ) {
			return '<p>' . \esc_html__( 'Aucun tour trouvé', 'transfertmarrakech' ) . '</p>';
		}
		
		\ob_start();
		?>
		<div class="tm-tours-list">
			<?php foreach ( $tours as $tour ) : ?>
				<?php self::$renderer->render( 'loop-tour', [ 'tour' => $tour ] ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		return \ob_get_clean();
	}
}

