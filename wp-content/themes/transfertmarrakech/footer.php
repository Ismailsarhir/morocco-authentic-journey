<?php
/**
 * Footer template
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

</div><!-- #container -->
</div><!-- #page -->

<?php
// Prefooter section
get_template_part( 'template-parts/prefooter' );
?>

<footer class="footer__wrapper">
	<div class="footer">
		<div class="footer__top">
			<div>
				<?php
				// Logo du footer - utilise render_logo du Header
				\TM\Core\Header::get_instance()->render_logo( 'footer__logo-link', 'footer__logo' );
				?>
				
				<?php
				// CTA Book Now - WhatsApp
				$whatsapp_message = 'Hello, I would like to make a reservation.';
				$reservez_url = \TM\Utils\MetaHelper::build_whatsapp_url( $whatsapp_message );
				?>
				<a href="<?php echo esc_url( $reservez_url ); ?>" class="cta primary">
					<span class="cta__inner" data-label="<?php echo esc_attr__( 'Book Now', 'transfertmarrakech' ); ?>">
						<span class="cta__txt"><?php echo esc_html__( 'Book Now', 'transfertmarrakech' ); ?></span>
					</span>
				</a>
				
				<?php
				// CTA Contact Page
				// Try to find contact page by slug (contact or contactez-nous)
				$contact_page = \get_page_by_path( 'contact' );
				$contact_url = $contact_page ? \get_permalink( $contact_page->ID ) : '#';
				$contact_label = __( 'Contact Us', 'transfertmarrakech' );
				?>
				<a href="<?php echo esc_url( $contact_url ); ?>" class="cta secondary">
					<span class="cta__inner" data-label="<?php echo esc_attr( $contact_label ); ?>">
						<span class="cta__txt"><?php echo esc_html( $contact_label ); ?></span>
					</span>
				</a>
			</div>
			
			<?php if ( has_nav_menu( 'footer-quick-links' ) ) : ?>
				<div>
					<div>
						<div class="footer__links-title">
							<?php echo esc_html__( 'Quick Links', 'transfertmarrakech' ); ?>
						</div>
						<?php
						wp_nav_menu( [
							'theme_location' => 'footer-quick-links',
							'container'      => false,
							'menu_class'     => 'footer__links',
							'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
							'fallback_cb'    => false,
							'depth'          => 1,
						] );
						?>
					</div>
				</div>
			<?php endif; ?>
			
			<?php if ( has_nav_menu( 'footer-social-links' ) ) : ?>
				<div>
					<div>
						<div class="footer__links-title">
							<?php echo esc_html__( 'Follow Us', 'transfertmarrakech' ); ?>
						</div>
						<?php
						wp_nav_menu( [
							'theme_location' => 'footer-social-links',
							'container'      => false,
							'menu_class'     => 'footer__links',
							'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
							'fallback_cb'    => false,
							'depth'          => 1,
						] );
						?>
					</div>
				</div>
			<?php endif; ?>
		</div>
		
		<div class="footer__bottom">
		
			<div>
				<ul>		
					<li>
						Boulevard el Mansour Eddahbi - Marrakech, Morocco
					</li>
				</ul>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
