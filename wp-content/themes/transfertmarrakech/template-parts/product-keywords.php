<?php
/**
 * Template part pour les mots-clés/tags du produit
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array  $primary_tags Tags principaux (affichés sans classe secondary)
 * @var array  $secondary_tags Tags secondaires (affichés avec classe secondary)
 */

$primary_tags = $primary_tags ?? [];
$secondary_tags = $secondary_tags ?? [];

if ( empty( $primary_tags ) && empty( $secondary_tags ) ) {
	return;
}
?>
<div class="keywords">
	<div class="keywords__tags">
		<?php foreach ( $primary_tags as $tag ) : ?>
			<?php if ( ! empty( $tag ) ) : ?>
				<div class="tag">
					<?php echo esc_html( $tag ); ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>

		<?php foreach ( $secondary_tags as $tag ) : ?>
			<?php if ( ! empty( $tag ) ) : ?>
				<div class="tag secondary">
					<?php echo esc_html( $tag ); ?>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
</div>

