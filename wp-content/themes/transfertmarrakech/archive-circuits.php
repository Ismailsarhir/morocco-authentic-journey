<?php
/**
 * Template for displaying circuit archives
 * Optimized PHP pagination (no AJAX)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

get_header();

// Récupère les options de l'archive
$archive_image_id = \TM\Admin\ArchiveCircuitsSettings::get_archive_image_id();
$archive_title = \TM\Admin\ArchiveCircuitsSettings::get_archive_title();
$archive_subtitle = \TM\Admin\ArchiveCircuitsSettings::get_archive_subtitle();

// Utilise la requête WordPress principale (déjà configurée pour 1 post par page)
global $wp_query;

// Récupère les circuits depuis la requête principale (optimisé)
$circuits_list = \TM\Core\CircuitsList::get_instance();
$circuits = [];

if ($wp_query->have_posts()) {
	// Optimisé : utilise directement $wp_query->posts au lieu de the_post()
	foreach ($wp_query->posts as $circuit) {
		if ($circuit instanceof \WP_Post && $circuit->post_type === 'circuits') {
			$circuit_data = $circuits_list->format_circuit_data($circuit);
			if ($circuit_data) {
				$circuits[] = $circuit_data;
			}
		}
	}
}

?>
<!-- Hero avec image parallax et titre -->
<div class="archive-hero">
	<div class="archive-hero__inner">
		<h1 class="archive-hero__title animated-title">
			<?php echo esc_html($archive_title); ?>
		</h1>
		<?php if (! empty($archive_subtitle)) : ?>
			<p class="archive-hero__subtitle animated-title">
				<?php echo esc_html($archive_subtitle); ?>
			</p>
		<?php endif; ?>
	</div>

	<?php if (! empty($archive_image_id)) : ?>
		<div class="archive-hero__bg">
			<div class="parallax">
				<?php
				$full_image_url = wp_get_attachment_image_url($archive_image_id, 'full');
				$image_alt = get_post_meta($archive_image_id, '_wp_attachment_image_alt', true);
				if (empty($image_alt)) {
					$image_alt = esc_attr($archive_title);
				}
				?>
				<img
					src="<?php echo esc_url($full_image_url); ?>"
					alt="<?php echo esc_attr($image_alt); ?>"
					fetchpriority="high">
			</div>
		</div>
	<?php endif; ?>
</div>
<div class="modules">
	<section class="module circuitsList">
		<div class="circuitsList__inner">
			<?php if (! empty($circuits)) : ?>
				<div class="circuitsList__list">
					<?php
					$renderer = new \TM\Template\Renderer();
					foreach ($circuits as $circuit_data) :
						$renderer->render('circuit-card', ['circuit_data' => $circuit_data]);
					endforeach;
					?>
				</div>
				<?php
				// Affiche la pagination
				$renderer->render('pagination');
				?>
			<?php else : ?>
				<p><?php \esc_html_e('Aucun circuit trouvé.', 'transfertmarrakech'); ?></p>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php get_footer(); ?>

