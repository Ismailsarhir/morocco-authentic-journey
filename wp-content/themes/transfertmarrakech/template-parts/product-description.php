<?php
/**
 * Template part pour la section description du produit
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $sections Sections à afficher (format: ['title' => string, 'content' => string, 'type' => 'list'|'text'|'price_list'|'itinerary', 'class' => string])
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
			
			if ( empty( $title ) ) {
				continue;
			}
			
			// Pour l'itinéraire, le contenu peut être vide si seulement le titre existe
			if ( $type !== 'itinerary' && empty( $content ) ) {
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
				<?php elseif ( $type === 'price_list' ) : ?>
					<ul class="description__list description__price-list">
						<?php
						$items = is_array( $content ) ? $content : [ $content ];
						foreach ( $items as $item ) :
							if ( empty( $item ) || ! is_array( $item ) ) {
								continue;
							}
							$label = $item['label'] ?? '';
							$value = $item['value'] ?? '';
							if ( empty( $label ) || empty( $value ) ) {
								continue;
							}
							?>
							<li>
								<strong><?php echo esc_html( $label ); ?>:</strong>
								<span><?php echo esc_html( $value ); ?></span>
							</li>
							<?php endforeach; ?>
					</ul>
				<?php elseif ( $type === 'itinerary' ) : ?>
					<?php
					$itinerary_title = is_array( $content ) && isset( $content['title'] ) ? $content['title'] : '';
					$itinerary_places = is_array( $content ) && isset( $content['places'] ) ? $content['places'] : [];
					?>
					<?php if ( ! empty( $itinerary_title ) ) : ?>
						<div class="description__itinerary-title" style="font-weight: bold; margin-bottom: 15px;">
							<?php echo esc_html( $itinerary_title ); ?>
						</div>
					<?php endif; ?>
					<?php if ( ! empty( $itinerary_places ) && is_array( $itinerary_places ) ) : ?>
						<ul class="description__list description__itinerary-list">
							<?php foreach ( $itinerary_places as $place ) : ?>
								<?php
								if ( ! is_array( $place ) ) {
									continue;
								}
								$time = $place['time'] ?? '';
								$place_title = $place['title'] ?? '';
								$description = $place['description'] ?? '';
								
								if ( empty( $place_title ) && empty( $description ) ) {
									continue;
								}
								?>
								<li class="description__itinerary-item" style="margin-bottom: 20px;">
									<?php if ( ! empty( $time ) ) : ?>
										<strong style="display: block; margin-bottom: 5px;"><?php echo esc_html( $time ); ?></strong>
									<?php endif; ?>
									<?php if ( ! empty( $place_title ) ) : ?>
										<strong style="display: block; margin-bottom: 5px;"><?php echo esc_html( $place_title ); ?></strong>
									<?php endif; ?>
									<?php if ( ! empty( $description ) ) : ?>
										<div style="margin-top: 5px;">
											<?php echo wpautop( esc_html( $description ) ); ?>
										</div>
									<?php endif; ?>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
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

