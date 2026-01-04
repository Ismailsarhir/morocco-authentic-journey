<?php
/**
 * Template part pour afficher les prix des tours
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 * 
 * @var array $price_tiers Tableau des prix par nombre de personnes
 */

$price_tiers = $price_tiers ?? [];
$cancellation = $cancellation ?? '';

if ( empty( $price_tiers ) || ! is_array( $price_tiers ) ) {
	return;
}

// Filtre les tiers valides
$valid_tiers = array_filter( $price_tiers, function( $tier ) {
	return ! empty( $tier['min_persons'] ) && ! empty( $tier['max_persons'] ) && ! empty( $tier['price'] );
});

if ( empty( $valid_tiers ) ) {
	return;
}

$tour_type_labels = \TM\Core\Constants::get_tour_type_labels();
?>
<div class="prices" id="departs">
	<h2 class="prices__title animated-title">
		<?php esc_html_e( 'Départs & Prix', 'transfertmarrakech' ); ?>
	</h2>
	
	<div class="prices__subtitle">
		<?php esc_html_e( 'Prix par personne (USD)', 'transfertmarrakech' ); ?>
	</div>
	
	<div class="table-wrapper">
		<table class="prices__table">
			<thead>
				<tr>
					<th rowspan="2" class="rounded-top-left rounded-bottom-left">
						<?php esc_html_e( 'Nombre de personnes', 'transfertmarrakech' ); ?>
					</th>
					<th rowspan="2">
						<?php esc_html_e( 'Type', 'transfertmarrakech' ); ?>
					</th>
					<th rowspan="2" class="rounded-top-right rounded-bottom-right">
						<?php esc_html_e( 'Prix régulier', 'transfertmarrakech' ); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $valid_tiers as $tier ) : 
					$min = absint( $tier['min_persons'] ?? 0 );
					$max = absint( $tier['max_persons'] ?? 0 );
					$price = esc_html( $tier['price'] ?? '' );
					$type = $tier['type'] ?? '';
					
					if ( $min === 0 || $max === 0 || empty( $price ) ) {
						continue;
					}
					
					// Format du nombre de personnes
					if ( $min === $max ) {
						$persons_label = sprintf(
							/* translators: %d: Number of persons */
							esc_html__( '%d personne', 'transfertmarrakech' ),
							$min
						);
						if ( $min > 1 ) {
							$persons_label = sprintf(
								/* translators: %d: Number of persons */
								esc_html__( '%d personnes', 'transfertmarrakech' ),
								$min
							);
						}
					} else {
						$persons_label = sprintf(
							/* translators: %1$d: Min persons, %2$d: Max persons */
							esc_html__( '%1$d à %2$d personnes', 'transfertmarrakech' ),
							$min,
							$max
						);
					}
					
					// Type de tour
					$type_label = '';
					if ( ! empty( $type ) && isset( $tour_type_labels[ $type ] ) ) {
						$type_label = $tour_type_labels[ $type ];
					} else {
						$type_label = esc_html__( 'Tous types', 'transfertmarrakech' );
					}
					
					// Format du prix - support pour valeurs numériques et chaînes de caractères
					if ( is_numeric( $price ) ) {
						$price_formatted = number_format( floatval( $price ), 2, '.', ' ' ) . ' USD';
					} else {
						// Si c'est une chaîne (ex: "Ask walid"), on l'affiche telle quelle
						$price_formatted = esc_html( $price );
					}
					?>
					<tr>
						<td class="bold nowrap">
							<?php echo esc_html( $persons_label ); ?>
						</td>
						<td class="nowrap">
							<?php echo esc_html( $type_label ); ?>
						</td>
						<td class="bold center nowrap">
							<?php echo $price_formatted; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php if ( ! empty( $cancellation ) ) : ?>
		<div class="table-caption">
			<?php echo esc_html( $cancellation ); ?>
		</div>
	<?php endif; ?>
</div>

