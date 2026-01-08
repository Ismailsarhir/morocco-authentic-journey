<?php
/**
 * Helper pour les fonctions liées aux circuits
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Utils;

/**
 * Classe helper pour les fonctions de rendu des circuits
 */
class CircuitHelper {
	
	/**
	 * Render a single circuit day with its steps, accommodations, meals, and transportation
	 *
	 * @param array $day Day data array
	 * @param \TM\Template\Renderer $renderer Renderer instance
	 * @return void
	 */
	public static function render_circuit_day( array $day, \TM\Template\Renderer $renderer ): void {
		$day_title = $day['day_title'] ?? '';
		$steps = $day['steps'] ?? [];
		$accommodations = $day['accommodations'] ?? '';
		$meals = $day['meals'] ?? '';
		$transportation = $day['transportation'] ?? '';

		// Convert steps to places format for places-list template
		$places = [];
		if ( ! empty( $steps ) && is_array( $steps ) ) {
			foreach ( $steps as $step ) {
				if ( ! is_array( $step ) ) {
					continue;
				}
				$step_title = $step['title'] ?? '';
				$step_description = $step['description'] ?? '';

				if ( empty( $step_title ) && empty( $step_description ) ) {
					continue;
				}

				$places[] = [
					'time' => '',
					'title' => $step_title,
					'description' => $step_description,
				];
			}
		}

		// Add details as the last card if accommodations, meals, or transportation exist
		$has_details = ! empty( $accommodations ) || ! empty( $meals ) || ! empty( $transportation );
		if ( $has_details ) {
			$details_map = [
				'accommodations' => [
					'value' => $accommodations,
					'label' => \esc_html__( 'Accommodations:', 'transfertmarrakech' ),
				],
				'meals' => [
					'value' => $meals,
					'label' => \esc_html__( 'Meals:', 'transfertmarrakech' ),
				],
				'transportation' => [
					'value' => $transportation,
					'label' => \esc_html__( 'Transportation:', 'transfertmarrakech' ),
				],
			];
			
			$details_content = '';
			foreach ( $details_map as $detail ) {
				if ( ! empty( $detail['value'] ) ) {
					$details_content .= \sprintf(
						'<li class="circuit-day__detail-item"><strong>%s</strong> <span>%s</span></li>',
						$detail['label'],
						\esc_html( $detail['value'] )
					);
				}
			}

			// Add details as the last place
			$places[] = [
				'time' => '',
				'title' => '',
				'description' => '<ul class="circuit-day__details-list">' . $details_content . '</ul>',
			];
		}

		// Skip if no places to display
		if ( empty( $places ) && empty( $day_title ) ) {
			return;
		}

		// Start places-list module structure
		echo '<div class="modules module-places-list">';
		echo '<div class="module placesList">';
		echo '<div class="placesList__inner">';

		// Display day title with animated-title
		if ( ! empty( $day_title ) ) {
			echo '<h2 class="placesList__title animated-title">';
			echo \esc_html( $day_title );
			echo '</h2>';
		}

		// Render cards section
		if ( ! empty( $places ) ) {
			$total = count( $places );
			$last_index = $total - 1;

			echo '<section class="cards">';
			foreach ( $places as $index => $place ) {
				$time = $place['time'] ?? '';
				$place_title = $place['title'] ?? '';
				$description = $place['description'] ?? '';
				$card_id = 'card-' . ( $index + 1 );
				$is_details_card = ( $index === $last_index ) && $has_details && empty( $place_title );
				?>
				<div class="card<?php echo $is_details_card ? ' circuit-day__details-card' : ''; ?>" id="<?php echo \esc_attr( $card_id ); ?>">
					<div class="card-inner">
						<div class="card-content">
							<?php if ( ! $is_details_card ) : ?>
								<div class="card-content-header">
									<?php if ( ! empty( $time ) ) : ?>
										<div class="card-tag">
											<p><?php echo \esc_html( $time ); ?></p>
										</div>
									<?php endif; ?>
									<?php if ( ! empty( $place_title ) ) : ?>
										<h3 class="card-title"><?php echo \esc_html( $place_title ); ?></h3>
									<?php endif; ?>
								</div>
								<?php if ( ! empty( $description ) ) : ?>
									<p class="card-description"><?php echo \esc_html( $description ); ?></p>
								<?php endif; ?>
							<?php else : ?>
								<div class="circuit-day__details">
									<?php echo $description; // Already escaped in $details_content ?>
								</div>
							<?php endif; ?>
						</div>
					</div>
				</div>
				<?php
			}
			echo '</section>';
		}

		// Close places-list module structure
		echo '</div>'; // .placesList__inner
		echo '</div>'; // .placesList
		echo '</div>'; // .modules.module-places-list
	}
}

