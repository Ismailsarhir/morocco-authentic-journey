<?php
/**
 * Template part pour une carte circuit
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $circuit_data Données du circuit
 */

if ( ! isset( $circuit_data ) || empty( $circuit_data ) ) {
	return;
}

$circuit = $circuit_data['circuit'] ?? null;
if ( ! $circuit || ! $circuit instanceof \WP_Post ) {
	return;
}

// Extraction des données (déjà formatées dans CircuitsList)
$circuit_id      = (int) ( $circuit_data['circuit_id'] ?? ( $circuit ? $circuit->ID : 0 ) );
$title           = $circuit_data['title'] ?? '';
$permalink       = $circuit_data['permalink'] ?? '';
$thumbnail       = $circuit_data['thumbnail'] ?? '';
$duration        = $circuit_data['duration'] ?? '';
$price_formatted = $circuit_data['price_formatted'] ?? '';
$location        = $circuit_data['location'] ?? '';
$language_labels = $circuit_data['language_labels'] ?? [];
$tag_labels      = $circuit_data['tag_labels'] ?? [];
$difficulty      = $circuit_data['difficulty'] ?? '';

// Validation des données essentielles
if ( empty( $title ) || empty( $permalink ) || empty( $thumbnail ) ) {
	return;
}

?>

<a class="circuit-card" href="<?php echo \esc_url( $permalink ); ?>">
	<div class="circuit-card__img-wrapper">
		<div class="parallax">
			<img 
				class="circuit-card__img"
				src="<?php echo \esc_url( $thumbnail ); ?>" 
				alt="<?php echo \esc_attr( $title ); ?>" 
				fetchpriority="low" 
				decoding="async" 
				loading="lazy"
			>
		</div>
		<?php if ( ! empty( $location ) ) : ?>
			<div class="tag"><?php echo \esc_html( $location ); ?></div>
		<?php endif; ?>
	</div>
	<div class="circuit-card__infos">
		<h5 class="circuit-card__infos-title">
			<?php echo \esc_html( $title ); ?>
		</h5>
		<?php if ( ! empty( $tag_labels ) && is_array( $tag_labels ) ) : ?>
			<div class="circuit-card__infos-tags">
				<?php foreach ( $tag_labels as $tag_label ) : ?>
					<?php if ( ! empty( $tag_label ) ) : ?>
						<div class="tag"><?php echo \esc_html( $tag_label ); ?></div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
		<div class="circuit-card__infos-table">
			<?php if ( ! empty( $duration ) || ! empty( $language_labels ) || ! empty( $difficulty ) ) : ?>
				<ul>
					<?php if ( ! empty( $duration ) ) : ?>
						<li>
							<?php \esc_html_e( 'Durée :', 'transfertmarrakech' ); ?> <?php echo \esc_html( $duration ); ?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $difficulty ) ) : ?>
						<li>
							<?php \esc_html_e( 'Difficulté :', 'transfertmarrakech' ); ?> <?php echo \esc_html( $difficulty ); ?>
						</li>
					<?php endif; ?>
					<?php if ( ! empty( $language_labels ) && is_array( $language_labels ) ) : ?>
						<li>
							<?php \esc_html_e( 'Langues :', 'transfertmarrakech' ); ?> <?php echo \esc_html( implode( ', ', $language_labels ) ); ?>
						</li>
					<?php endif; ?>
				</ul>
			<?php endif; ?>
			<?php if ( ! empty( $price_formatted ) ) : ?>
				<div>
					<?php \esc_html_e( 'À partir de :', 'transfertmarrakech' ); ?>
					<strong><?php echo \esc_html( $price_formatted ); ?></strong>
				</div>
			<?php endif; ?>
		</div>
	</div>
</a>

