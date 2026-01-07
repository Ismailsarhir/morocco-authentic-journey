<?php
/**
 * Template for displaying transfer archives
 * Optimized PHP pagination (no AJAX)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

get_header();

// Récupère les options de l'archive
$archive_image_id = \TM\Admin\ArchiveTransfersSettings::get_archive_image_id();
$archive_title = \TM\Admin\ArchiveTransfersSettings::get_archive_title();
$archive_subtitle = \TM\Admin\ArchiveTransfersSettings::get_archive_subtitle();

// Utilise la requête WordPress principale (déjà configurée pour 1 post par page)
global $wp_query;

// Récupère les transferts depuis la requête principale (optimisé)
$transfers_list = \TM\Core\TransfersList::get_instance();
$transfers = [];

if ($wp_query->have_posts()) {
	// Optimisé : utilise directement $wp_query->posts au lieu de the_post()
	foreach ($wp_query->posts as $transfer) {
		if ($transfer instanceof \WP_Post && $transfer->post_type === 'transferts') {
			$transfer_data = $transfers_list->format_transfer_data($transfer);
			if ($transfer_data) {
				$transfers[] = $transfer_data;
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
	<section class="module transfersList">
		<div class="transfersList__inner">
			<?php if (! empty($transfers)) : ?>
				<div class="transfersList__list">
					<?php
					$renderer = new \TM\Template\Renderer();
					foreach ($transfers as $transfer_data) :
						$renderer->render('transfer-card', ['transfer_data' => $transfer_data]);
					endforeach;
					?>
				</div>
				<?php
				// Affiche la pagination
				$renderer->render('pagination');
				?>
			<?php else : ?>
				<p><?php \esc_html_e('Aucun transfert trouvé.', 'transfertmarrakech'); ?></p>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php get_footer(); ?>

