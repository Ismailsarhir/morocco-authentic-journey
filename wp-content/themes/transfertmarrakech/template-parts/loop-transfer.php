<?php
/**
 * Template part pour une boucle transfert (utilisé par shortcode)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var WP_Post $transfer Post du transfert
 */

if ( ! isset( $transfer ) || ! $transfer instanceof \WP_Post ) {
	return;
}

// Format les données pour utiliser transfer-card
$transfer_id = $transfer->ID;
	$transfer_meta = \TM\Utils\MetaHelper::get_transfer_meta( $transfer_id );
	$thumbnail_url = \TM\Utils\MetaHelper::get_post_thumbnail_url_with_fallback( $transfer_id );
	
	if ( ! $thumbnail_url ) {
		return;
	}
	
	$vehicle_id = (int) ( $transfer_meta[ \TM\Core\Constants::META_TRANSFER_VEHICLE ] ?? 0 );
	$vehicle_name = '';
	if ( $vehicle_id > 0 ) {
		$vehicle = \TM\Repository\PostRepository::get_instance()->get_by_id( $vehicle_id );
		if ( $vehicle instanceof \WP_Post ) {
			$vehicle_name = \TM\Utils\MetaHelper::get_post_title( $vehicle );
		}
	}
	
	$transfer_data = [
		'transfer'      => $transfer,
		'transfer_id'   => $transfer_id,
		'title'         => \TM\Utils\MetaHelper::get_post_title( $transfer ),
		'permalink'     => \get_permalink( $transfer_id ),
		'thumbnail'     => $thumbnail_url,
		'type'          => $transfer_meta[ \TM\Core\Constants::META_TRANSFER_TYPE ] ?? '',
		'pickup'        => $transfer_meta[ \TM\Core\Constants::META_TRANSFER_PICKUP ] ?? '',
		'dropoff'       => $transfer_meta[ \TM\Core\Constants::META_TRANSFER_DROPOFF ] ?? '',
		'duration'      => $transfer_meta[ \TM\Core\Constants::META_TRANSFER_DURATION_ESTIMATE ] ?? '',
		'price'         => $transfer_meta[ \TM\Core\Constants::META_TRANSFER_PRICE ] ?? '',
		'price_formatted' => \TM\Utils\MetaHelper::format_price( $transfer_meta[ \TM\Core\Constants::META_TRANSFER_PRICE ] ?? '' ),
		'vehicle_id'    => $vehicle_id,
		'vehicle_name'  => $vehicle_name,
	];
}

if ( empty( $transfer_data ) ) {
	return;
}

$renderer = new \TM\Template\Renderer();
$renderer->render( 'transfer-card', [ 'transfer_data' => $transfer_data ] );

