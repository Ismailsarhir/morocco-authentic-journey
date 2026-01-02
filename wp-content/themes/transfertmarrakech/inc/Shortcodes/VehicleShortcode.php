<?php
/**
 * Shortcode pour afficher les véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Shortcodes;

use TM\Core\Constants;

/**
 * Classe pour le shortcode [tm_vehicules]
 */
class VehicleShortcode extends BaseShortcode {
	
	/**
	 * Enregistre le shortcode
	 * 
	 * @return void
	 */
	public function register(): void {
		\add_shortcode( 'tm_vehicules', [ $this, 'render' ] );
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
			'limit'     => -1,
			'available' => 'false',
			'type'      => '',
		], $atts, 'tm_vehicules' );
		
		$args = [
			'posts_per_page' => (int) $atts['limit'],
		];
		
		// Construit les critères de meta_query
		$meta_criteria = [];
		if ( $atts['available'] === 'true' ) {
			$meta_criteria[ Constants::META_VEHICLE_AVAILABILITY ] = '1';
		}
		if ( ! empty( $atts['type'] ) ) {
			$meta_criteria[ Constants::META_VEHICLE_TYPE ] = $atts['type'];
		}
		
		$meta_query = $this->build_meta_query( $meta_criteria );
		if ( ! empty( $meta_query ) ) {
			$args['meta_query'] = $meta_query;
		}
		
		$vehicles = self::$repository->get_by_args( Constants::POST_TYPE_VEHICLE, $args );
		
		if ( empty( $vehicles ) ) {
			return '<p>' . esc_html__( 'Aucun véhicule trouvé', 'transfertmarrakech' ) . '</p>';
		}
		
		\ob_start();
		?>
		<div class="tm-vehicles-list">
			<?php foreach ( $vehicles as $vehicle ) : ?>
				<?php self::$renderer->render( 'loop-vehicle', [ 'vehicle' => $vehicle ] ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		return \ob_get_clean();
	}
}

