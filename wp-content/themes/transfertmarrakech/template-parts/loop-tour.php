<?php
/**
 * Template part pour une boucle tour (utilisé par shortcode)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var WP_Post $tour Post du tour
 */

if ( ! isset( $tour ) || ! $tour instanceof \WP_Post ) {
	return;
}

// Format les données pour utiliser tour-card
$tour_list = \TM\Core\ToursList::get_instance();
$tour_data = $tour_list->format_tour_data( $tour );

if ( empty( $tour_data ) ) {
	return;
}

$renderer = new \TM\Template\Renderer();
$renderer->render( 'tour-card', [ 'tour_data' => $tour_data ] );

