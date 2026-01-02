<?php

/**
 * Template for displaying single transfer posts
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
$transfer = \get_queried_object();
if (! $transfer instanceof \WP_Post) {
	// Si pas de post, on utilise le post global
	global $post;
	$transfer = $post;
}

if (! $transfer instanceof \WP_Post) {
	\get_footer();
	return;
}

$transfer_id = $transfer->ID;

// Récupère les meta du transfert
$transfer_meta = \TM\Utils\MetaHelper::get_transfer_meta($transfer_id);

// Extraction des données (optimisé : utilise les propriétés de l'objet quand possible)
$title = $transfer->post_title ?: \get_the_title($transfer_id);
$content = \apply_filters('the_content', $transfer->post_content);
$thumbnail_id = \get_post_thumbnail_id($transfer_id);
$transfer_type = $transfer_meta['tm_transfer_type'] ?? '';
$price = $transfer_meta['tm_price'] ?? '';
$price_formatted = ! empty($price) ? \number_format((float) $price, 0, ',', ' ') : '';
$pickup = $transfer_meta['tm_pickup'] ?? '';
$dropoff = $transfer_meta['tm_dropoff'] ?? '';
$duration_estimate = $transfer_meta['tm_duration_estimate'] ?? '';
$description = $transfer_meta['tm_description'] ?? '';

// Villes visitées : utilise pickup et dropoff
$cities_visited = [];
if (! empty($pickup)) {
	$cities_visited[] = $pickup;
}
if (! empty($dropoff)) {
	$cities_visited[] = $dropoff;
}
$cities_visited = \array_unique($cities_visited);

// Récupère le véhicule associé
$vehicle_id = $transfer_meta['tm_vehicle'] ?? 0;
$transfer_vehicles_data = [];

if ($vehicle_id > 0) {
	$repository = \TM\Repository\PostRepository::get_instance();
	$vehicle = $repository->get_by_id((int) $vehicle_id);
	
	if ($vehicle instanceof \WP_Post) {
		// Récupère l'image avec fallback sur différentes tailles
		$thumbnail_url = \get_the_post_thumbnail_url( $vehicle_id, 'large' )
			?: \get_the_post_thumbnail_url( $vehicle_id, 'medium' )
			?: \get_the_post_thumbnail_url( $vehicle_id, 'thumbnail' );
		
		// Skip vehicle without featured image
		if ( $thumbnail_url ) {
			$transfer_vehicles_data[] = [
				'vehicle_id' => $vehicle_id,
				'title'      => $vehicle->post_title,
				'thumbnail'  => $thumbnail_url,
			];
		}
	}
}

// Formatage de la durée estimée
$duration_display = '';
if (! empty($duration_estimate)) {
	$duration_escaped = esc_html($duration_estimate);
	if (preg_match('/(\d+)h/i', $duration_estimate, $matches)) {
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

// Récupère les termes de destination pour le backlink (si disponible)
$destinations = \get_the_terms($transfer_id, 'destination');
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
						<?php if (! empty($pickup)) : ?>
							<li>
								<?php esc_html_e('Départ :', 'transfertmarrakech'); ?>
								<strong><?php echo esc_html($pickup); ?></strong>
							</li>
						<?php endif; ?>

						<?php if (! empty($dropoff)) : ?>
							<li>
								<?php esc_html_e('Arrivée :', 'transfertmarrakech'); ?>
								<strong><?php echo esc_html($dropoff); ?></strong>
							</li>
						<?php endif; ?>

						<?php if (! empty($duration_display)) : ?>
							<li>
								<?php esc_html_e('Durée estimée :', 'transfertmarrakech'); ?>
								<strong><?php echo esc_html($duration_display); ?></strong>
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
			<?php if (! empty($pickup)) : ?>
				<div class="tag">
					<?php echo esc_html($pickup); ?>
				</div>
			<?php endif; ?>
			
			<?php if (! empty($dropoff)) : ?>
				<div class="tag">
					<?php echo esc_html($dropoff); ?>
				</div>
			<?php endif; ?>
			
			<div class="tag secondary">
				<?php esc_html_e('Transfert garanti', 'transfertmarrakech'); ?>
			</div>
		
			<div class="tag secondary">
				<?php esc_html_e('Recommandé', 'transfertmarrakech'); ?>
			</div>
		
			<div class="tag secondary">
				<?php esc_html_e('Nouvelle route', 'transfertmarrakech'); ?>
			</div>
		</div>
	</div>
	
	<div class="description">
		<div class="description__inner">
			<?php if (! empty($cities_visited)) : ?>
				<div class="description__list-wrapper">
					<div class="description__title">
						<?php esc_html_e('Villes visitées', 'transfertmarrakech'); ?>
					</div>
					<ul class="description__list is-bold">
						<?php foreach ($cities_visited as $index => $city) : ?>
							<li>
								<?php if ($index > 0) : ?>/ <?php endif; ?><?php echo esc_html($city); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
			
			<?php if (! empty($description)) : ?>
				<div class="description__list-wrapper summary">
					<div class="description__title">
						<?php esc_html_e('Description', 'transfertmarrakech'); ?>
					</div>
					<div class="description__list">
						<?php echo \wpautop(\esc_html($description)); ?>
					</div>
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
	// Affiche le véhicule assigné au transfert
	if (! empty($transfer_vehicles_data)) {
		$renderer = new \TM\Template\Renderer();
		$renderer->render('vehicles-grid', [
			'vehicles' => $transfer_vehicles_data,
			'title'    => esc_html__('Véhicule disponible pour ce transfert', 'transfertmarrakech'),
		]);
	}
	?>
</main>


<?php
get_footer();

