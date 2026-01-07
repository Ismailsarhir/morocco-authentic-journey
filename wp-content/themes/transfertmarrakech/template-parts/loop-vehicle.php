<?php
/**
 * Template part pour une boucle véhicule (utilisé par shortcode)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var WP_Post $vehicle Post du véhicule
 */

if ( ! isset( $vehicle ) || ! $vehicle instanceof \WP_Post ) {
	return;
}

$vehicle_id = $vehicle->ID;
$vehicle_meta = \TM\Utils\MetaHelper::get_vehicle_meta( $vehicle_id );
$thumbnail_url = \TM\Utils\MetaHelper::get_post_thumbnail_url_with_fallback( $vehicle_id );

if ( ! $thumbnail_url ) {
	return;
}

$title = \TM\Utils\MetaHelper::get_post_title( $vehicle );
$permalink = \get_permalink( $vehicle_id );

if ( empty( $title ) || empty( $permalink ) ) {
	return;
}

?>
<a class="vehicle-card" href="<?php echo \esc_url( $permalink ); ?>">
	<div class="vehicle-card__img-wrapper">
		<img 
			class="vehicle-card__img"
			src="<?php echo \esc_url( $thumbnail_url ); ?>" 
			alt="<?php echo \esc_attr( $title ); ?>" 
			fetchpriority="low" 
			decoding="async" 
			loading="lazy"
		>
	</div>
	<div class="vehicle-card__infos">
		<h5 class="vehicle-card__infos-title">
			<?php echo \esc_html( $title ); ?>
		</h5>
		<?php if ( ! empty( $vehicle_meta[ \TM\Core\Constants::META_VEHICLE_TYPE ] ) ) : ?>
			<div class="vehicle-card__infos-tags">
				<div class="tag"><?php echo \esc_html( $vehicle_meta[ \TM\Core\Constants::META_VEHICLE_TYPE ] ); ?></div>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $vehicle_meta[ \TM\Core\Constants::META_VEHICLE_SEATS ] ) ) : ?>
			<div class="vehicle-card__infos-table">
				<div>
					<?php \esc_html_e( 'Places :', 'transfertmarrakech' ); ?>
					<strong><?php echo \esc_html( $vehicle_meta[ \TM\Core\Constants::META_VEHICLE_SEATS ] ); ?></strong>
				</div>
			</div>
		<?php endif; ?>
	</div>
</a>

