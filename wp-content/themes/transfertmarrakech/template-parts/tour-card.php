<?php
/**
 * Template part pour une carte tour
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $tour_data Données du tour
 */

if ( ! isset( $tour_data ) || empty( $tour_data ) ) {
	return;
}

$tour = $tour_data['tour'] ?? null;
if ( ! $tour || ! $tour instanceof \WP_Post ) {
	return;
}

// Extraction des données (déjà formatées dans ToursList)
$tour_id         = (int) ( $tour_data['tour_id'] ?? ( $tour ? $tour->ID : 0 ) );
$title           = $tour_data['title'] ?? '';
$permalink       = $tour_data['permalink'] ?? '';
$thumbnail       = $tour_data['thumbnail'] ?? '';
$duration        = $tour_data['duration'] ?? '';
$days            = (int) ( $tour_data['days'] ?? 0 );
$price_formatted = $tour_data['price_formatted'] ?? '';
$location        = $tour_data['location'] ?? '';
$vehicle_names   = $tour_data['vehicle_names'] ?? [];

// Validation des données essentielles
if ( empty( $title ) || empty( $permalink ) || empty( $thumbnail ) ) {
	return;
}

// Formatage de la durée avec "heure(s)" en français
$duration_display = '';
if ( ! empty( $duration ) ) {
	$duration_escaped = \esc_html( $duration );
	if ( preg_match( '/(\d+)h/i', $duration, $matches ) ) {
		$hours = (int) $matches[1];
		$heure_text = ( $hours > 1 ) ? \__( 'heures', 'transfertmarrakech' ) : \__( 'heure', 'transfertmarrakech' );
		$duration_display = $duration_escaped . ' ' . \esc_html( $heure_text );
	} else {
		$duration_display = $duration_escaped . ' ' . \esc_html__( 'heures', 'transfertmarrakech' );
	}
}

?>

<a class="tour-card" href="<?php echo \esc_url( $permalink ); ?>">
	<div class="tour-card__img-wrapper">
		<div class="parallax">
			<img 
				class="tour-card__img"
				src="<?php echo \esc_url( $thumbnail ); ?>" 
				alt="<?php echo \esc_attr( $title ); ?>" 
				fetchpriority="low" 
				decoding="async" 
				loading="lazy"
			>
		</div>
		<?php if ( ! empty( $location ) ) : ?>
			<div class="tag"><?php echo \esc_html( $location ); ?></div>
		<?php endif; ?>
	</div>
	<div class="tour-card__infos">
		<h5 class="tour-card__infos-title">
			<?php echo \esc_html( $title ); ?>
		</h5>
		<?php if ( ! empty( $vehicle_names ) && is_array( $vehicle_names ) ) : ?>
			<div class="tour-card__infos-tags">
				<?php foreach ( $vehicle_names as $vehicle_name ) : ?>
					<?php if ( ! empty( $vehicle_name ) ) : ?>
						<div class="tag"><?php echo \esc_html( $vehicle_name ); ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="tour-card__infos-table">
			<?php if ( ! empty( $duration_display ) || ( $days > 0 ) ) : ?>
				<ul>
					<?php if ( ! empty( $duration_display ) ) : ?>
						<li>
							<?php 
							/* translators: %s: Duration (e.g., "9 heures") */
							echo $duration_display . ' ' . \esc_html__( 'de route', 'transfertmarrakech' );
							?>
						</li>
					<?php endif; ?>
					<?php if ( $days > 0 ) : ?>
						<li>
							<?php 
							/* translators: %d: Number of days */
							printf( 
								\esc_html__( '%d jours de séjour', 'transfertmarrakech' ),
								$days
							);
							?>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $price_formatted ) ) : ?>
				<div>
					<?php \esc_html_e( 'À partir de :', 'transfertmarrakech' ); ?> 
					<strong><?php echo \esc_html( $price_formatted ); ?> <?php \esc_html_e( 'MAD*', 'transfertmarrakech' ); ?></strong>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>
