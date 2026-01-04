<?php

/**
 * Template part pour afficher la liste des places/étapes (itinéraire)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $title Titre principal du bloc (optionnel)
 * @var array $places Tableau des places/étapes (format: [['time' => string, 'title' => string, 'description' => string], ...])
 */

$title = $title ?? '';
$places = $places ?? [];

if (empty($places) || ! is_array($places)) {
	return;
}

// Filtre les places valides
$valid_places = array_filter($places, function ($place) {
	if (! is_array($place)) {
		return false;
	}
	$place_title = $place['title'] ?? '';
	$description = $place['description'] ?? '';
	return ! empty($place_title) || ! empty($description);
});

if (empty($valid_places)) {
	return;
}
?>
<div class="modules module-places-list">
	<div class="module placesList">
		<div class="placesList__inner">
			<?php if (! empty($title)) : ?>
				<h2 class="placesList__title animated-title">
					<?php echo esc_html($title); ?>
				</h2>
			<?php endif; ?>

			<section class="cards">
				<?php
				$places_array = array_values($valid_places);
				$total = count($places_array);

				if ($total > 0) {
					foreach ($places_array as $index => $place) {
						$time = $place['time'] ?? '';
						$place_title = $place['title'] ?? '';
						$description = $place['description'] ?? '';
						$card_id = 'card-' . ($index + 1);
				?>
						<div class="card" id="<?php echo esc_attr($card_id); ?>">
							<div class="card-inner">
								<div class="card-content">
									<div class="card-content-header">
										<?php if (! empty($time)) : ?>
											<div class="card-tag">
												<p><?php echo esc_html($time); ?></p>
											</div>
										<?php endif; ?>
										<?php if (! empty($place_title)) : ?>
											<h3 class="card-title"><?php echo esc_html($place_title); ?></h3>
										<?php endif; ?>
									</div>
									<?php if (! empty($description)) : ?>
										<p class="card-description"><?php echo esc_html($description); ?></p>
									<?php endif; ?>
								</div>
							</div>
						</div>
				<?php
					}
				}
				?>
			</section>
		</div>
	</div>
</div>