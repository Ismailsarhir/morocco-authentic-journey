<?php
/**
 * Template part pour une carte transfert
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $transfer_data Données du transfert
 */

if ( ! isset( $transfer_data ) || empty( $transfer_data ) ) {
	return;
}

$transfer = $transfer_data['transfer'] ?? null;
if ( ! $transfer || ! $transfer instanceof \WP_Post ) {
	return;
}

// Extraction des données
$transfer_id   = (int) ( $transfer_data['transfer_id'] ?? ( $transfer ? $transfer->ID : 0 ) );
$title         = $transfer_data['title'] ?? '';
$permalink     = $transfer_data['permalink'] ?? '';
$thumbnail     = $transfer_data['thumbnail'] ?? '';
$type          = $transfer_data['type'] ?? '';
$pickup        = $transfer_data['pickup'] ?? '';
$dropoff       = $transfer_data['dropoff'] ?? '';
$duration      = $transfer_data['duration'] ?? '';
$price_formatted = $transfer_data['price_formatted'] ?? '';
$vehicle_name  = $transfer_data['vehicle_name'] ?? '';

// Validation des données essentielles
if ( empty( $title ) || empty( $permalink ) || empty( $thumbnail ) ) {
	return;
}

?>

<a class="transfert-card varianteVedette" href="<?php echo \esc_url( $permalink ); ?>">
	<div class="transfert-card__img">
		<div class="parallax">
			<img 
					src="<?php echo \esc_url( $thumbnail ); ?>" 
					alt="<?php echo \esc_attr( $title ); ?>"
					fetchpriority="low" 
					decoding="async" 
					loading="lazy"
				>
		</div>
	</div>
	<div class="transfert-card__infos">
		<h5 class="transfert-card__infos-title">
			<?php echo \esc_html( $title ); ?>
		</h5>
		<?php if ( ! empty( $vehicle_name ) ) : ?>
			<div class="transfert-card__infos-tags">
				<div class="tag"><?php echo \esc_html( $vehicle_name ); ?></div>
			</div>
		<?php endif; ?>
		<div class="transfert-card__infos-table">
			<?php if ( ! empty( $pickup ) || ! empty( $dropoff ) || ! empty( $duration ) ) : ?>
				<ul>
					<?php if ( ! empty( $pickup ) ) : ?>
						<li><?php echo \esc_html( $pickup ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $dropoff ) ) : ?>
						<li><?php echo \esc_html( $dropoff ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $duration ) ) : ?>
						<li><?php echo \esc_html( $duration ); ?></li>
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

