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

<footer class="footer__wrapper">
	<div class="footer">
		<div class="footer__top">
			<div>
				<?php
				// Logo du footer - utilise render_logo du Header
				\TM\Core\Header::get_instance()->render_logo( 'footer__logo-link', 'footer__logo' );
				?>
				
				<?php
				// CTA Réservez Maintenant
				$reservez_url = '#';
				?>
				<a href="<?php echo esc_url( $reservez_url ); ?>" class="cta primary">
					<span class="cta__inner" data-label="<?php echo esc_attr__( 'Réservez Maintenant', 'transfertmarrakech' ); ?>">
						<span class="cta__txt"><?php echo esc_html__( 'Réservez Maintenant', 'transfertmarrakech' ); ?></span>
					</span>
				</a>
				
				<?php
				// CTA EMAIL
				$email_url = 'mailto:contact@example.com';
				$email_label = __( 'Contactez-nous', 'transfertmarrakech' );
				?>
				<a href="<?php echo esc_url( $email_url ); ?>" class="cta secondary">
					<span class="cta__inner" data-label="<?php echo esc_attr( $email_label ); ?>">
						<span class="cta__txt"><?php echo esc_html( $email_label ); ?></span>
					</span>
				</a>
			</div>
			
			<?php if ( has_nav_menu( 'footer-quick-links' ) ) : ?>
				<div>
					<div>
						<div class="footer__links-title">
							<?php echo esc_html__( 'Liens rapides', 'transfertmarrakech' ); ?>
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
							<?php echo esc_html__( 'Suivez-nous', 'transfertmarrakech' ); ?>
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
					<?php
					// Politique de confidentialité
					$privacy_url = '#';
					?>
					<li>
						<a href="<?php echo esc_url( $privacy_url ); ?>" target="_blank" rel="noopener noreferrer">
						Boulevard el Mansour Eddahbi - Marrakech
						</a>
					</li>
				</ul>
			</div>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>

</body>
</html>
