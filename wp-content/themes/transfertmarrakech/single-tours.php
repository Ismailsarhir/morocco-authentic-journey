<?php

/**
 * Template for displaying single tour posts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if (! defined('ABSPATH')) {
	exit;
}

get_header();

// Récupère le post actuel
$tour = \get_queried_object();
if (! $tour instanceof \WP_Post) {
	// Si pas de post, on utilise le post global
	global $post;
	$tour = $post;
}

if (! $tour instanceof \WP_Post) {
	\get_footer();
	return;
}

$tour_id = $tour->ID;

// Récupère les meta du tour
$tour_meta = \TM\Utils\MetaHelper::get_tour_meta($tour_id);

// Extraction des données (optimisé : utilise les propriétés de l'objet quand possible)
$title = $tour->post_title ?: \get_the_title($tour_id);
$content = \apply_filters('the_content', $tour->post_content);
$thumbnail_id = \get_post_thumbnail_id($tour_id);
$location = $tour_meta['tm_location'] ?? '';
$duration = $tour_meta['tm_duration'] ?? '';
$duration_minutes = $tour_meta['tm_duration_minutes'] ?? 0;
$price = $tour_meta['tm_price'] ?? '';
$price_formatted = ! empty($price) ? \number_format((float) $price, 0, ',', ' ') : '';
$highlights = $tour_meta['tm_highlights'] ?? '';
$meeting_point = $tour_meta['tm_meeting_point'] ?? '';

// Villes visitées : utilise la localisation
$country_visited = $location;

// Formate les points forts en liste (séparés par virgules uniquement)
$highlights_list = [];
if (! empty($highlights)) {
	$highlights_list = \array_filter(\array_map('trim', \explode(',', $highlights)));
}

// Récupère les véhicules associés
$repository = \TM\Repository\PostRepository::get_instance();
$vehicle_posts = $repository->get_related_vehicles_for_tour($tour_id);
$tour_vehicles_data = [];

if (! empty($vehicle_posts)) {
	foreach ($vehicle_posts as $vehicle) {
		if (! $vehicle instanceof \WP_Post) {
			continue;
		}
		
		$vehicle_id = $vehicle->ID;
		
		// Récupère l'image avec fallback sur différentes tailles
		$thumbnail_url = \get_the_post_thumbnail_url( $vehicle_id, 'large' )
			?: \get_the_post_thumbnail_url( $vehicle_id, 'medium' )
			?: \get_the_post_thumbnail_url( $vehicle_id, 'thumbnail' );
		
		// Skip vehicles without featured image
		if ( ! $thumbnail_url ) {
			continue;
		}

		$tour_vehicles_data[] = [
			'vehicle_id' => $vehicle_id,
			'title'      => $vehicle->post_title,
			'thumbnail'  => $thumbnail_url,
		];
	}
}

// Formatage de la durée
$duration_display = '';
if (! empty($duration)) {
	$duration_escaped = esc_html($duration);
	if (preg_match('/(\d+)h/i', $duration, $matches)) {
		$hours = (int) $matches[1];
		$duration_display = sprintf(
			'%s %s',
			$duration_escaped,
			esc_html(_n('heure', 'heures', $hours, 'transfertmarrakech'))
		);
	} else {
		$duration_display = $duration_escaped . ' ' . esc_html__('heures', 'transfertmarrakech');
	}
}

// Calcul des jours et récupération des nuits et repas
$days = max(0, (int) $duration_minutes);
$nights = max(0, (int) ($tour_meta['tm_nights'] ?? max(0, $days - 1)));
$meals = max(0, (int) ($tour_meta['tm_meals'] ?? 0));

// Récupère les termes de destination pour le backlink
$destinations = \get_the_terms($tour_id, 'destination');
$destination_link = '#';
$destination_name = '';
if (! empty($destinations) && ! \is_wp_error($destinations)) {
	$destination = \reset($destinations);
	if ($destination instanceof \WP_Term) {
		$destination_link = \get_term_link($destination);
		$destination_name = $destination->name;
	}
}
?>


<main class="product">
	<div class="product__inner">
		<h1 class="product__title animated-title">
			<?php echo esc_html($title); ?>
		</h1>

		<?php if (! empty($thumbnail_id)) : ?>

			<div class="product__card">
				<div class="product__card-img-wrapper">
					<div class="parallax">
						<?php
						echo wp_get_attachment_image(
							$thumbnail_id,
							'large',
							false,
							[
								'class' => 'product__card-img',
								'alt'   => esc_attr($title),
							]
						);
						?>
					</div>
				</div>

				<div class="product__card-infos">
					<ul>
						<?php if ($days > 0) : ?>
							<li>
								<?php
								/* translators: %d: Number of days */
								printf(
									esc_html__('%d jours', 'transfertmarrakech'),
									$days
								);
								?>
							</li>
						<?php endif; ?>

						<?php if ($nights > 0) : ?>
							<li>
								<?php
								/* translators: %d: Number of nights */
								printf(
									esc_html__('%d nuits', 'transfertmarrakech'),
									$nights
								);
								?>
							</li>
						<?php endif; ?>

						<?php if ($meals > 0) : ?>
							<li>
								<?php
								/* translators: %d: Number of meals */
								printf(
									esc_html__('%d repas', 'transfertmarrakech'),
									$meals
								);
								?>
							</li>
						<?php endif; ?>
					</ul>

					<?php if (! empty($price_formatted)) : ?>
						<div>
							<?php esc_html_e('À partir de :', 'transfertmarrakech'); ?>
							<strong><?php echo esc_html($price_formatted); ?> <?php esc_html_e('MAD*', 'transfertmarrakech'); ?></strong>
						</div>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

	<?php if (! empty($thumbnail_id)) : ?>
		<div class="product__bg">
			<div class="parallax">
				<?php echo wp_get_attachment_image(
					$thumbnail_id,
					'large',
					false,
					[
						'class' => 'product__bg-img',
						'alt'   => esc_attr($title),
					]
				); ?>
			</div>
		</div>
	<?php endif; ?>

	<?php if (! empty($destination_name)) : ?>
		<a href="<?php echo esc_url($destination_link); ?>" class="backlink">
			<button class="cta primary left is-arrow is-white">
				<span class="cta__inner"></span>
			</button>
			<span class="backlink__txt">
				<?php echo esc_html($destination_name); ?>
			</span>
		</a>
	<?php endif; ?>
</main>

<main class="product-body">
	<div class="keywords">
		<div class="keywords__tags">
			<?php if (! empty($location)) : ?>
				<div class="tag">
					<?php echo esc_html($location); ?>
				</div>
			<?php endif; ?>
			
			<?php if (! empty($meeting_point)) : ?>
				<div class="tag">
					<?php echo esc_html($meeting_point); ?>
				</div>
			<?php endif; ?>
			
			<div class="tag secondary">
				<?php esc_html_e('Départ garanti', 'transfertmarrakech'); ?>
			</div>
		
			<div class="tag secondary">
				<?php esc_html_e('Coup de coeur', 'transfertmarrakech'); ?>
			</div>
		
			<div class="tag secondary">
				<?php esc_html_e('Nouvel itinéraire', 'transfertmarrakech'); ?>
			</div>
		</div>
	</div>
	
	<div class="description">
		<div class="description__inner">
			<?php if (! empty($country_visited)) : ?>
				<div class="description__list-wrapper">
					<div class="description__title">
						<?php esc_html_e('Villes visitées', 'transfertmarrakech'); ?>
					</div>
					<ul class="description__list is-bold">
						<li>
							<?php echo esc_html($country_visited); ?>
						</li>
					</ul>
				</div>
			<?php endif; ?>
			
			<?php if (! empty($highlights_list)) : ?>
				<div class="description__list-wrapper cities">
					<div class="description__title">
						<?php esc_html_e('Places visitées', 'transfertmarrakech'); ?>
					</div>
					<ul class="description__list is-bold">
						<?php foreach ($highlights_list as $index => $highlight) : ?>
							<li>
								<?php if ($index > 0) : ?>/ <?php endif; ?><?php echo esc_html($highlight); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<?php if (! empty($content)) : ?>
				<div class="description__list-wrapper summary">
					<div class="description__title">
						<?php esc_html_e('Sommaire', 'transfertmarrakech'); ?>
					</div>
					<div class="description__list">
						<?php echo $content; // Déjà filtré avec apply_filters('the_content') ?>
					</div>
				</div>
			<?php endif; ?>
		</div>
	</div>
	
	<?php
	// Affiche les véhicules assignés au tour
	if (! empty($tour_vehicles_data)) {
		$renderer = new \TM\Template\Renderer();
		$renderer->render('vehicles-grid', [
			'vehicles' => $tour_vehicles_data,
			'title'    => esc_html__('Véhicules disponibles pour ce tour', 'transfertmarrakech'),
		]);
	}
	?>
</main>


<?php
get_footer();
