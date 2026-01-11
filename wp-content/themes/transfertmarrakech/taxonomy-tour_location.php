<?php
/**
 * Template for displaying tour_location taxonomy archives
 * Optimized PHP pagination (no AJAX)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

get_header();

// Récupère le terme de taxonomie actuel
$queried_object = get_queried_object();
if ( ! $queried_object || ! isset( $queried_object->term_id ) ) {
	get_footer();
	return;
}

$term = $queried_object;
$term_id = $term->term_id;

// Récupère l'image du terme depuis les meta
$archive_image_id = get_term_meta( $term_id, 'tm_term_image', true );
$archive_image_id = $archive_image_id ? absint( $archive_image_id ) : 0;

// Utilise le nom du terme comme titre
$archive_title = $term->name;

// Utilise la description du terme comme sous-titre (si disponible)
$archive_subtitle = ! empty( $term->description ) ? $term->description : '';

// Utilise la requête WordPress principale (déjà configurée pour 9 posts par page)
global $wp_query;

// Récupère les tours depuis la requête principale (optimisé)
$tours_list = \TM\Core\ToursList::get_instance();
$tours = [];

// Vérifie d'abord si la requête a des posts
if ($wp_query->have_posts()) {
	// Optimisé : utilise directement $wp_query->posts au lieu de the_post()
	foreach ($wp_query->posts as $tour) {
		if ($tour instanceof \WP_Post && $tour->post_type === 'tours') {
			$tour_data = $tours_list->format_tour_data($tour);
			if ($tour_data) {
				$tours[] = $tour_data;
			}
		}
	}
} else {
	// Si la requête principale n'a pas de posts, essayons une requête directe
	$args = [
		'post_type'      => 'tours',
		'posts_per_page' => 9,
		'post_status'    => 'publish',
		'tax_query'      => [
			[
				'taxonomy' => 'tour_location',
				'field'    => 'term_id',
				'terms'    => $term_id,
			],
		],
	];
	
	$custom_query = new \WP_Query($args);
	if ($custom_query->have_posts()) {
		foreach ($custom_query->posts as $tour) {
			if ($tour instanceof \WP_Post && $tour->post_type === 'tours') {
				$tour_data = $tours_list->format_tour_data($tour);
				if ($tour_data) {
					$tours[] = $tour_data;
				}
			}
		}
	}
	wp_reset_postdata();
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
	<section class="module toursList">
		<div class="toursList__inner">
			<?php if (! empty($tours)) : ?>
				<div class="toursList__list">
					<?php
					$renderer = new \TM\Template\Renderer();
					foreach ($tours as $tour_data) :
						$renderer->render('tour-card', ['tour_data' => $tour_data]);
					endforeach;
					?>
				</div>
				<?php
				// Affiche la pagination
				$renderer->render('pagination');
				?>
			<?php else : ?>
				<p><?php \esc_html_e('No tours found.', 'transfertmarrakech'); ?></p>
			<?php endif; ?>
		</div>
	</section>
</div>

<?php get_footer(); ?>
