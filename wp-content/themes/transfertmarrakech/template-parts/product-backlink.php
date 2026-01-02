<?php
/**
 * Template part pour le lien de retour vers la destination
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $destination_link URL de la destination
 * @var string $destination_name Nom de la destination
 */

if ( empty( $destination_name ) || empty( $destination_link ) || $destination_link === '#' ) {
	return;
}
?>
<a href="<?php echo esc_url( $destination_link ); ?>" class="backlink">
	<button class="cta primary left is-arrow is-white">
		<span class="cta__inner"></span>
	</button>
	<span class="backlink__txt">
		<?php echo esc_html( $destination_name ); ?>
	</span>
</a>

