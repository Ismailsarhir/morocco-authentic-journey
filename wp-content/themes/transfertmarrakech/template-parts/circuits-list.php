<?php

/**
 * Template part pour la liste des circuits vedettes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $circuits Tableau des circuits avec leurs données
 */

if (! isset($circuits) || empty($circuits)) {
	return;
}

$renderer = new \TM\Template\Renderer();
$circuits_url = home_url('/circuits/');
?>

<div class="modules">
	<section class="module circuitsList">
		<div class="circuitsList__inner">
			<h2 class="circuitsList__title animated-title">
				<?php \esc_html_e('Circuits vedettes', 'transfertmarrakech'); ?>
			</h2>
			<div class="circuitsList__list">
				<?php foreach ($circuits as $circuit_data) :
					$renderer->render('circuit-card', ['circuit_data' => $circuit_data]);
				endforeach; ?>
			</div>
			<div class="circuitsList__cta">
				<a
					target=""
					href="<?php echo \esc_url($circuits_url); ?>"
					class="cta primary">
					<span class="cta__inner" data-label="<?php \esc_attr_e('Voir tout les circuits', 'transfertmarrakech'); ?>">
						<span class="cta__txt">
							<?php \esc_html_e('Voir tout les circuits', 'transfertmarrakech'); ?>
						</span>
					</span>
				</a>
			</div>
		</div>
	</section>
</div>

