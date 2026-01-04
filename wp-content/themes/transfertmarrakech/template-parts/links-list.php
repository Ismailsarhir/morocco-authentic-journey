<?php
/**
 * Template part pour afficher des listes avec structure linksList
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $block_title Titre principal du bloc (optionnel)
 * @var array $sections Tableau de sections (format: [['title' => string, 'content' => string], ...])
 *                       Le contenu peut être une chaîne (une ligne par item) ou un tableau d'items
 */

$block_title = $block_title ?? '';
$sections = $sections ?? [];

if ( empty( $sections ) || ! is_array( $sections ) ) {
	return;
}

// Filtre les sections valides
$valid_sections = array_filter( $sections, function( $section ) {
	return ! empty( $section['title'] ) && ! empty( $section['content'] );
});

if ( empty( $valid_sections ) ) {
	return;
}
?>
<div class="linksList__inner">
	<?php if ( ! empty( $block_title ) ) : ?>
		<h2 class="linksList__title animated-title">
			<?php echo esc_html( $block_title ); ?>
		</h2>
	<?php endif; ?>
	<div class="linksList__list">
		<?php foreach ( $valid_sections as $index => $section ) : ?>
			<?php
			$title = $section['title'] ?? '';
			$content = $section['content'] ?? '';
			
			// Convertit le contenu en tableau d'items
			$items = is_string( $content ) 
				? array_filter( array_map( 'trim', explode( "\n", $content ) ) )
				: ( is_array( $content ) ? array_filter( array_map( 'trim', $content ) ) : [] );
			
			if ( empty( $items ) ) {
				continue;
			}
			?>
			<div class="linksList__link-wrapper" data-catindex="<?php echo esc_attr( $index ); ?>">
				<h5 class="linksList__link" tabindex="0">
					<?php echo esc_html( $title ); ?>
					<button class="cta primary down is-arrow">
						<span class="cta__inner"></span>
					</button>
				</h5>
				<div class="linksList__link-txtwrapper">
					<div class="linksList__link-txt">
						<ul>
							<?php foreach ( $items as $item ) : ?>
								<li><?php echo esc_html( $item ); ?></li>
							<?php endforeach; ?>
						</ul>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>

