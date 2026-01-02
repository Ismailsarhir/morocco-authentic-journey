<?php
/**
 * Template part pour la section description du produit
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $sections Sections à afficher (format: ['title' => string, 'content' => string, 'type' => 'list'|'text', 'class' => string])
 */

$sections = $sections ?? [];

if ( empty( $sections ) ) {
	return;
}
?>
<div class="description">
	<div class="description__inner">
		<?php foreach ( $sections as $section ) : ?>
			<?php
			$title = $section['title'] ?? '';
			$content = $section['content'] ?? '';
			$type = $section['type'] ?? 'text';
			$class = $section['class'] ?? '';
			
			if ( empty( $title ) || empty( $content ) ) {
				continue;
			}
			
			$wrapper_class = 'description__list-wrapper';
			if ( ! empty( $class ) ) {
				$wrapper_class .= ' ' . esc_attr( $class );
			}
			?>
			<div class="<?php echo esc_attr( $wrapper_class ); ?>">
				<div class="description__title">
					<?php echo esc_html( $title ); ?>
				</div>
				<?php if ( $type === 'list' ) : ?>
					<ul class="description__list is-bold">
						<?php
						$items = is_array( $content ) ? $content : [ $content ];
						foreach ( $items as $index => $item ) :
							if ( empty( $item ) ) {
								continue;
							}
							?>
							<li>
								<?php if ( $index > 0 ) : ?>/ <?php endif; ?>
								<?php echo esc_html( $item ); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				<?php else : ?>
					<div class="description__list">
						<?php
						// Si le contenu est déjà filtré avec apply_filters('the_content'), on l'affiche tel quel
						// Sinon, on applique wpautop pour formater les paragraphes
						if ( strpos( $content, '<p>' ) !== false || strpos( $content, '<div>' ) !== false ) {
							echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						} else {
							echo wpautop( esc_html( $content ) );
						}
						?>
					</div>
				<?php endif; ?>
			</div>
		<?php endforeach; ?>
	</div>
</div>

