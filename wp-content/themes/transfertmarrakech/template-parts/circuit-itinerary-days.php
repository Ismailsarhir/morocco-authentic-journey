<?php

/**
 * Template part pour afficher l'itinéraire par jours du circuit
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $days Tableau des jours (format: [['day_title' => string, 'steps' => array, 'accommodations' => string, 'meals' => string, 'transportation' => string], ...])
 */

$days = $days ?? [];

if (empty($days) || ! is_array($days)) {
	return;
}

// Filtre les jours valides
$valid_days = array_filter($days, function ($day) {
	if (! is_array($day)) {
		return false;
	}
	$day_title = $day['day_title'] ?? '';
	$steps = $day['steps'] ?? [];
	return ! empty($day_title) || (is_array($steps) && ! empty($steps));
});

if (empty($valid_days)) {
	return;
}
?>
<div class="modules module-circuit-itinerary-days">
	<div class="module circuitItineraryDays">
		<div class="circuitItineraryDays__inner">
			<h2 class="circuitItineraryDays__title animated-title">
				<?php esc_html_e('Itinerary', 'transfertmarrakech'); ?>
			</h2>

			<section class="circuit-days">
				<?php
				$days_array = array_values($valid_days);
				$total = count($days_array);

				if ($total > 0) {
					foreach ($days_array as $day_index => $day) {
						$day_title = $day['day_title'] ?? '';
						$steps = $day['steps'] ?? [];
						$accommodations = $day['accommodations'] ?? '';
						$meals = $day['meals'] ?? '';
						$transportation = $day['transportation'] ?? '';
						$day_number = $day_index + 1;
				?>
						<div class="circuit-day" id="day-<?php echo esc_attr($day_number); ?>">
							<?php if (! empty($day_title)) : ?>
								<h3 class="circuit-day__title animated-title">
									<?php echo esc_html($day_title); ?>
								</h3>
							<?php endif; ?>
							
							<?php if (! empty($steps) && is_array($steps)) : ?>
								<?php
								// Filter valid steps
								$valid_steps = array_filter($steps, function ($step) {
									if (! is_array($step)) {
										return false;
									}
									$step_title = $step['title'] ?? '';
									$step_description = $step['description'] ?? '';
									return ! empty($step_title) || ! empty($step_description);
								});
								
								if (! empty($valid_steps)) :
									$steps_array = array_values($valid_steps);
								?>
									<div class="cards">
										<?php foreach ($steps_array as $step_index => $step) : ?>
											<?php
											$step_title = $step['title'] ?? '';
											$step_description = $step['description'] ?? '';
											$card_id = 'day-' . $day_number . '-card-' . ($step_index + 1);
											?>
											<div class="card" id="<?php echo esc_attr($card_id); ?>">
												<div class="card-inner">
													<div class="card-content">
														<div class="card-content-header">
															<?php if (! empty($step_title)) : ?>
																<h4 class="card-title"><?php echo esc_html($step_title); ?></h4>
															<?php endif; ?>
														</div>
														<?php if (! empty($step_description)) : ?>
															<p class="card-description"><?php echo esc_html($step_description); ?></p>
														<?php endif; ?>
													</div>
												</div>
											</div>
										<?php endforeach; ?>
									</div>
								<?php endif; ?>
							<?php endif; ?>
							
							<?php if (! empty($accommodations) || ! empty($meals) || ! empty($transportation)) : ?>
								<div class="circuit-day__details">
									<?php if (! empty($accommodations)) : ?>
										<div class="circuit-day__detail-item">
											<strong><?php esc_html_e('Accommodations:', 'transfertmarrakech'); ?></strong>
											<span><?php echo esc_html($accommodations); ?></span>
										</div>
									<?php endif; ?>
									<?php if (! empty($meals)) : ?>
										<div class="circuit-day__detail-item">
											<strong><?php esc_html_e('Meals:', 'transfertmarrakech'); ?></strong>
											<span><?php echo esc_html($meals); ?></span>
										</div>
									<?php endif; ?>
									<?php if (! empty($transportation)) : ?>
										<div class="circuit-day__detail-item">
											<strong><?php esc_html_e('Transportation:', 'transfertmarrakech'); ?></strong>
											<span><?php echo esc_html($transportation); ?></span>
										</div>
									<?php endif; ?>
								</div>
							<?php endif; ?>
						</div>
				<?php
					}
				}
				?>
			</section>
		</div>
	</div>
</div>

