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
		</div>
	</section>
</div>

