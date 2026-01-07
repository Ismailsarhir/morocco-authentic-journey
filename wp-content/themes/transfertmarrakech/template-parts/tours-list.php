<?php

/**
 * Template part pour la liste des tours vedettes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $tours Tableau des tours avec leurs données
 */

if (! isset($tours) || empty($tours)) {
	return;
}

$renderer = new \TM\Template\Renderer();
$tours_url = home_url('/tours/');
?>

<div class="modules">
	<section class="module toursList">
		<div class="toursList__inner">
			<h2 class="toursList__title animated-title">
				<?php \esc_html_e('Tours vedettes', 'transfertmarrakech'); ?>
			</h2>
			<div class="toursList__list">
				<?php foreach ($tours as $tour_data) :
					$renderer->render('tour-card', ['tour_data' => $tour_data]);
				endforeach; ?>
			</div>
			<div class="toursList__cta">
				<a
					target=""
					href="<?php echo \esc_url($tours_url); ?>"
					class="cta primary">
					<span class="cta__inner" data-label="<?php \esc_attr_e('Voir tout les tours', 'transfertmarrakech'); ?>">
						<span class="cta__txt">
							<?php \esc_html_e('Voir tout les tours', 'transfertmarrakech'); ?>
						</span>
					</span>
				</a>
			</div>
		</div>
	</section>
</div>