<?php
/**
 * Template part pour le Hero
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var WP_Post $hero_post Post à afficher dans le Hero
 */

use TM\Utils\HeroHelper;
use TM\Core\Header;

// Récupère le post Hero si non fourni
if ( ! isset( $hero_post ) ) {
	$hero_post = HeroHelper::get_hero_post();
}

// Si aucun post Hero, on ne rend rien
if ( ! $hero_post ) {
	return;
}

// Récupère les données du Hero
$hero_title = HeroHelper::get_hero_title( $hero_post );
$hero_video_url = HeroHelper::get_hero_video_url( $hero_post );
$destinations_url = \home_url( '/destinations' );
$header_instance = Header::get_instance();
?>

<div class="hero">
	<div class="hero__inner">
		<h1 class="hero__title">
			<?php echo \esc_html( $hero_title ); ?>
		</h1>
	</div>
	
	<div class="search-bar is-clicky">
		<?php $header_instance->render_search_form( 'hero-search-input' ); ?>
	</div>
	
	<?php if ( ! empty( $hero_video_url ) ) : ?>
		<parallax-el translate-y="-100" class="hero__bg">
			<div class="iframe">
				<iframe 
					src="<?php echo \esc_url( $hero_video_url ); ?>" 
					frameborder="0" 
					allow="autoplay; encrypted-media; fullscreen; picture-in-picture" 
					allowfullscreen 
					data-ot-ignore 
					loading="lazy"
					playsinline="1"
				></iframe>
			</div>
		</parallax-el>
	<?php endif; ?>
</div>

