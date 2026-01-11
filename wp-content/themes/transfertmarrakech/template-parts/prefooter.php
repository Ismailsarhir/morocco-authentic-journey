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
// Replace with your email address
$newsletter_email = 'contact@example.com';
$newsletter_url = 'mailto:' . $newsletter_email . '?subject=' . rawurlencode( __( 'Newsletter Subscription', 'transfertmarrakech' ) );

$title_text = __( 'Subscribe to our newsletter', 'transfertmarrakech' );
$description_text = __( 'Stay informed about our special offers, new products and exclusive promotions. Subscribe now so you don\'t miss anything!', 'transfertmarrakech' );
$cta_text = __( 'Subscribe', 'transfertmarrakech' );
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
			
			<?php if ( ! empty( $newsletter_url ) && ! empty( $cta_text ) ) : ?>
				<a 
					href="<?php echo esc_url( $newsletter_url ); ?>" 
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

