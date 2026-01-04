<?php
/**
 * Template part pour l'en-tête du produit (titre, carte, image de fond)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $title Titre du produit
 * @var int    $thumbnail_id ID de l'image mise en avant
 * @var array  $card_info_items Items à afficher dans la carte (format: ['label' => string, 'value' => string])
 * @var string $price_formatted Prix formaté (optionnel)
 * @var int    $post_id ID du post (optionnel, pour détecter le type automatiquement)
 */

if ( ! isset( $title ) || empty( $title ) ) {
	return;
}

$thumbnail_id = $thumbnail_id ?? 0;
$card_info_items = $card_info_items ?? [];
$price_formatted = $price_formatted ?? '';
$post_id = $post_id ?? 0;

// Détecte automatiquement le type de produit depuis le post type
$post_type = $post_id > 0 ? \get_post_type( $post_id ) : '';
$is_transfer = ( $post_type === \TM\Core\Constants::POST_TYPE_TRANSFER );
?>
<main class="product">
	<div class="product__inner">
		<h1 class="product__title animated-title">
			<?php echo esc_html( $title ); ?>
		</h1>

		<?php if ( ! empty( $thumbnail_id ) ) : ?>
			<div class="product__card">
				<div class="product__card-img-wrapper">
					<div class="parallax">
						<?php
						echo wp_get_attachment_image(
							$thumbnail_id,
							'large',
							false,
							[
								'class' => 'product__card-img',
								'alt'   => esc_attr( $title ),
							]
						);
						?>
					</div>
				</div>

				<?php if ( ! empty( $card_info_items ) || ! empty( $price_formatted ) ) : ?>
					<div class="product__card-infos">
						<?php if ( ! empty( $card_info_items ) ) : ?>
							<ul>
								<?php foreach ( $card_info_items as $item ) : ?>
									<?php if ( ! empty( $item['value'] ) ) : ?>
										<li>
											<?php if ( ! empty( $item['label'] ) ) : ?>
												<?php echo esc_html( $item['label'] ); ?>
											<?php endif; ?>
											<?php if ( $is_transfer ) : ?>
												<strong><?php echo esc_html( $item['value'] ); ?></strong>
											<?php else : ?>
												<?php echo esc_html( $item['value'] ); ?>
											<?php endif; ?>
										</li>
									<?php endif; ?>
								<?php endforeach; ?>
							</ul>
						<?php endif; ?>

						<?php if ( ! empty( $price_formatted ) ) : ?>
							<div>
								<?php esc_html_e( 'À partir de :', 'transfertmarrakech' ); ?>
								<strong><?php echo esc_html( $price_formatted ); ?></strong>
							</div>
						<?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $thumbnail_id ) ) : ?>
		<div class="product__bg">
			<div class="parallax">
				<?php
				echo wp_get_attachment_image(
					$thumbnail_id,
					'large',
					false,
					[
						'class' => 'product__bg-img',
						'alt'   => esc_attr( $title ),
					]
				);
				?>
			</div>
		</div>
	<?php endif; ?>
</main>

