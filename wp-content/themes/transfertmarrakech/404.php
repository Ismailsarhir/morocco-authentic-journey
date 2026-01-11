<?php

/**
 * The template for displaying 404 pages (not found)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if (! defined('ABSPATH')) {
	exit;
}

get_header();
?>

<main id="main-content" class="error-404">
	<div class="error-404__content">
		<h1 class="error-404__title animated-title">
			<?php esc_html_e('Oops! The page you are looking for does not exist.', 'transfertmarrakech'); ?>
		</h1>

		<div class="error-404__actions">
			<a href="<?php echo esc_url(home_url('/')); ?>" class="cta primary">
				<span class="cta__inner" data-label="<?php esc_attr_e('Back to Home', 'transfertmarrakech'); ?>">
					<span class="cta__txt"><?php esc_html_e('Back to Home', 'transfertmarrakech'); ?></span>
				</span>
			</a>
		</div>
	</div>
</main>

<?php get_footer(); ?>
