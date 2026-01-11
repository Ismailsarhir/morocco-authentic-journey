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
$price_formatted = $tour_data['price_formatted'] ?? '';
$location        = $tour_data['location'] ?? '';
$tag_labels      = $tour_data['tag_labels'] ?? [];
$language_labels = $tour_data['language_labels'] ?? [];
$difficulty      = $tour_data['difficulty'] ?? '';

// Validation des données essentielles
if ( empty( $title ) || empty( $permalink ) || empty( $thumbnail ) ) {
	return;
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
		<?php if ( ! empty( $tag_labels ) && is_array( $tag_labels ) ) : ?>
			<div class="tour-card__infos-tags">
				<?php foreach ( $tag_labels as $tag_label ) : ?>
					<?php if ( ! empty( $tag_label ) ) : ?>
						<div class="tag"><?php echo \esc_html( $tag_label ); ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="tour-card__infos-table">
			<?php if ( ! empty( $duration ) || ! empty( $language_labels ) || ! empty( $difficulty ) ) : ?>
				<ul>
					<?php if ( ! empty( $duration ) ) : ?>
						<li>
							<?php \esc_html_e( 'Duration:', 'transfertmarrakech' ); ?> <?php echo \esc_html( $duration ); ?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $difficulty ) ) : ?>
						<li>
							<?php \esc_html_e( 'Difficulty:', 'transfertmarrakech' ); ?> <?php echo \esc_html( $difficulty ); ?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $language_labels ) && is_array( $language_labels ) ) : ?>
						<li>
							<?php \esc_html_e( 'Languages:', 'transfertmarrakech' ); ?> <?php echo \esc_html( implode( ', ', $language_labels ) ); ?>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $price_formatted ) ) : ?>
				<div>
					<?php \esc_html_e( 'From:', 'transfertmarrakech' ); ?>
					<strong><?php echo \esc_html( $price_formatted ); ?></strong>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>
