<?php
/**
 * Template for displaying pages
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// Récupère le post actuel
$page = \TM\Utils\MetaHelper::get_current_post();
if ( ! $page instanceof \WP_Post ) {
	get_footer();
	return;
}

// Extraction des données
$title = \TM\Utils\MetaHelper::get_post_title( $page );
$content = \apply_filters( 'the_content', $page->post_content );
?>

<main>
	<div class="page__header">
		<h1 class="page__title animated-title"><?php echo esc_html( $title ); ?></h1>
	</div>
	
	<?php if ( ! empty( $content ) ) : ?>
		<div class="page__body">
			<div class="page__body__inner">
				<?php echo $content; ?>
			</div>
		</div>
	<?php endif; ?>
</main>

<?php
get_footer();
