<?php
/**
 * Template part pour la section Featured Text
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $featured_text Texte principal
 * @var string $surtext Surtexte (optionnel)
 */

if ( ! isset( $featured_text ) || empty( $featured_text ) ) {
	return;
}

$surtext = $surtext ?? '';
?>

<div class="modules">
	<section class="module featuredText">
		<div class="featuredText__inner">
			<?php if ( ! empty( $surtext ) ) : ?>
				<div class="featuredText__surtext">
					<?php echo \esc_html( $surtext ); ?>
				</div>
			<?php endif; ?>
			<div class="featuredText__text h2 animated-lines">
				<?php echo \wp_kses_post( $featured_text ); ?>
			</div>
		</div>
	</section>
</div>

