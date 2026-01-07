<?php
/**
 * Template part pour la liste des transferts vedettes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $transfers Tableau des transferts avec leurs données
 */

if ( ! isset( $transfers ) || empty( $transfers ) ) {
	return;
}

$renderer = new \TM\Template\Renderer();
$transfers_url = home_url('/transferts/');
?>

<div class="modules">
	<section class="module transfersList">
		<div class="transfersList__inner">
			<h2 class="transfersList__title animated-title">
				<?php \esc_html_e( 'Transferts vedettes', 'transfertmarrakech' ); ?>
			</h2>
			<div class="transfersList__list">
				<?php foreach ( $transfers as $transfer_data ) : 
					$renderer->render( 'transfer-card', [ 'transfer_data' => $transfer_data ] );
				endforeach; ?>
			</div>
			<div class="transfersList__cta">
				<a
					target=""
					href="<?php echo \esc_url($transfers_url); ?>"
					class="cta primary">
					<span class="cta__inner" data-label="<?php \esc_attr_e('Voir tout les transferts', 'transfertmarrakech'); ?>">
						<span class="cta__txt">
							<?php \esc_html_e('Voir tout les transferts', 'transfertmarrakech'); ?>
						</span>
					</span>
				</a>
			</div>
		</div>
	</section>
</div>

