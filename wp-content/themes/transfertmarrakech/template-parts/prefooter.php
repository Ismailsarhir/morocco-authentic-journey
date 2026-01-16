<?php
/**
 * Prefooter template part
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Configuration
// Get contact page URL
$contact_page = \get_page_by_path( 'contact' );
$subscribe_url = $contact_page ? \get_permalink( $contact_page->ID ) : '#';

// Check if we're on the contact page
$is_contact_page = \is_page( 'contact' );

if ( $is_contact_page ) {
	// Different text for contact page
	$title_text = __( 'Reserve Your Tours', 'transfertmarrakech' );
	$description_text = __( 'Ready to explore Marrakech? Contact us to book your perfect tour, circuit or transfer. We\'re here to make your journey unforgettable!', 'transfertmarrakech' );
	$cta_text = __( 'Book Now', 'transfertmarrakech' );
	// On contact page, link to WhatsApp instead
	$whatsapp_message = 'Hello, I would like to make a reservation.';
	$subscribe_url = \TM\Utils\MetaHelper::build_whatsapp_url( $whatsapp_message );
} else {
	// Default newsletter text
	$title_text = __( 'Subscribe to our newsletter', 'transfertmarrakech' );
	$description_text = __( 'Stay informed about our special offers, new products and exclusive promotions. Subscribe now so you don\'t miss anything!', 'transfertmarrakech' );
	$cta_text = __( 'Subscribe', 'transfertmarrakech' );
}
?>

<div class="prefooter__wrapper">
	<div class="prefooter">
		<div class="prefooter__inner">
			<?php if ( ! empty( $title_text ) ) : ?>
				<div class="prefooter__title h2 animated-lines">
					<?php echo esc_html( $title_text ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $description_text ) ) : ?>
				<div class="prefooter__txt">
					<?php echo esc_html( $description_text ); ?>
				</div>
			<?php endif; ?>
			
			<?php if ( ! empty( $subscribe_url ) && ! empty( $cta_text ) ) : ?>
				<a 
					href="<?php echo esc_url( $subscribe_url ); ?>" 
					class="cta secondary light-hover"
				>
					<span class="cta__inner" data-label="<?php echo esc_attr( $cta_text ); ?>">
						<span class="cta__txt">
							<?php echo esc_html( $cta_text ); ?>
						</span>
					</span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</div>

