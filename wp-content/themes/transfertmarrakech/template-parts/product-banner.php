<?php
/**
 * Template part pour la bannière du produit (image, titre, partage, WhatsApp)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var string $title Titre du produit
 * @var int    $thumbnail_id ID de l'image mise en avant
 * @var string $share_url URL à partager
 * @var string $share_title Titre pour le partage
 * @var string $whatsapp_url URL WhatsApp pré-remplie
 * @var string $whatsapp_label Label du bouton WhatsApp
 */

if ( ! isset( $title ) || empty( $title ) ) {
	return;
}

$thumbnail_id = $thumbnail_id ?? 0;
$share_url = $share_url ?? '';
$share_title = $share_title ?? esc_attr( $title );
$whatsapp_url = $whatsapp_url ?? '';
$whatsapp_label = $whatsapp_label ?? esc_html__( 'Contacter une agence', 'transfertmarrakech' );
?>
<div class="banner">
	<div class="banner__inner">
		<div class="banner__media">
			<?php if ( ! empty( $thumbnail_id ) ) : ?>
				<div class="banner__card-img-wrapper2">
					<?php
					echo wp_get_attachment_image(
						$thumbnail_id,
						'large',
						false,
						[
							'class' => 'banner__card-img',
							'alt'   => esc_attr( $title ),
						]
					);
					?>
				</div>
			<?php endif; ?>
		</div>
		<div class="banner__title">
			<?php echo esc_html( $title ); ?>
		</div>
	</div>
	<div class="banner__actions">
		<?php if ( ! empty( $share_url ) ) : ?>
			<div class="banner__share-wrapper">
				<a href="#" class="banner__share has-underline" data-share-url="<?php echo esc_url( $share_url ); ?>" data-share-title="<?php echo esc_attr( $share_title ); ?>">
					<?php esc_html_e( 'Partager', 'transfertmarrakech' ); ?>
				</a>
			</div>
		<?php endif; ?>
		<?php if ( ! empty( $whatsapp_url ) ) : ?>
			<div class="banner__btns">
				<a href="<?php echo esc_url( $whatsapp_url ); ?>" target="_blank" rel="noopener noreferrer" class="cta secondary light-hover">
					<span class="cta__inner" data-label="<?php echo esc_attr( $whatsapp_label ); ?>">
						<span class="cta__txt">
							<?php echo esc_html( $whatsapp_label ); ?>
						</span>
					</span>
				</a>
			</div>
		<?php endif; ?>
	</div>
</div>

