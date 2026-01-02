<?php
/**
 * Shortcode pour afficher les transferts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Shortcodes;

use TM\Core\Constants;

/**
 * Classe pour le shortcode [tm_transferts]
 */
class TransferShortcode extends BaseShortcode {
	
	/**
	 * Enregistre le shortcode
	 * 
	 * @return void
	 */
	public function register(): void {
		\add_shortcode( 'tm_transferts', [ $this, 'render' ] );
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
			'limit' => -1,
			'type'  => '',
		], $atts, 'tm_transferts' );
		
		$args = [
			'posts_per_page' => (int) $atts['limit'],
		];
		
		// Filtre par type
		if ( ! empty( $atts['type'] ) ) {
			$args['meta_query'] = [
				[
					'key'     => Constants::META_TRANSFER_TYPE,
					'value'   => \sanitize_text_field( $atts['type'] ),
					'compare' => '=',
				],
			];
		}
		
		$transfers = self::$repository->get_by_args( Constants::POST_TYPE_TRANSFER, $args );
		
		if ( empty( $transfers ) ) {
			return '<p>' . \esc_html__( 'Aucun transfert trouvé', 'transfertmarrakech' ) . '</p>';
		}
		
		\ob_start();
		?>
		<div class="tm-transfers-list">
			<?php foreach ( $transfers as $transfer ) : ?>
				<?php self::$renderer->render( 'loop-transfer', [ 'transfer' => $transfer ] ); ?>
			<?php endforeach; ?>
		</div>
		<?php
		return \ob_get_clean();
	}
}

