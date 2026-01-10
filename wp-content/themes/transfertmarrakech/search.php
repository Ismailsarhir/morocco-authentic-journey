<?php
/**
 * Template for displaying search results
 * Optimized for performance
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

get_header();

// Utilise la requête WordPress principale
global $wp_query;

// Récupère le terme de recherche (une seule fois)
$search_query = get_search_query();
$search_query_escaped = esc_html( $search_query );

// Sépare les résultats par type de post
$tours = [];
$circuits = [];
$transfers = [];

// Vérifie qu'on est bien sur une page de recherche
if ( is_search() && $wp_query->have_posts() ) {
	// Récupère les instances (singleton - optimisé)
	$tours_list = \TM\Core\ToursList::get_instance();
	$circuits_list = \TM\Core\CircuitsList::get_instance();
	$transfers_list = \TM\Core\TransfersList::get_instance();
	
	// Cache les constantes pour éviter les appels répétés
	$post_type_tour = \TM\Core\Constants::POST_TYPE_TOUR;
	$post_type_circuit = \TM\Core\Constants::POST_TYPE_CIRCUIT;
	$post_type_transfer = \TM\Core\Constants::POST_TYPE_TRANSFER;
	
	// Optimisation : utilise directement $wp_query->posts au lieu de the_post()
	// Cela évite les appels setup_postdata() répétés
	foreach ( $wp_query->posts as $post ) {
		if ( ! $post instanceof \WP_Post ) {
			continue;
		}
		
		// Formate les données selon le type de post
		switch ( $post->post_type ) {
			case $post_type_tour:
				$tour_data = $tours_list->format_tour_data( $post );
				if ( $tour_data ) {
					$tours[] = $tour_data;
				}
				break;
				
			case $post_type_circuit:
				$circuit_data = $circuits_list->format_circuit_data( $post );
				if ( $circuit_data ) {
					$circuits[] = $circuit_data;
				}
				break;
				
			case $post_type_transfer:
				$transfer_data = $transfers_list->format_transfer_data( $post );
				if ( $transfer_data ) {
					$transfers[] = $transfer_data;
				}
				break;
		}
	}
	
	// Réinitialise les données du post (nécessaire même avec posts)
	wp_reset_postdata();
}

// Calcule le total une seule fois
$total_results = count( $tours ) + count( $circuits ) + count( $transfers );
$renderer = new \TM\Template\Renderer();

?>
<!-- Hero avec titre de recherche -->
<div class="search-hero">
	<div class="search-hero__inner">
		<h1 class="search-hero__title animated-title">
			<?php 
			if ( ! empty( $search_query ) ) {
				/* translators: %s: Search query */
				printf( esc_html__( 'Résultats de recherche pour : %s', 'transfertmarrakech' ), $search_query_escaped );
			} else {
				esc_html_e( 'Résultats de recherche', 'transfertmarrakech' );
			}
			?>
		</h1>
		<?php if ( $total_results > 0 ) : ?>
			<p class="search-hero__subtitle animated-title">
				<?php
				/* translators: %d: Number of results */
				printf( esc_html( _n( '%d résultat trouvé', '%d résultats trouvés', $total_results, 'transfertmarrakech' ) ), $total_results );
				?>
			</p>
		<?php endif; ?>
	</div>
</div>

<div class="modules">
	<?php if ( $total_results > 0 ) : ?>
		<!-- Section Tours -->
		<?php if ( ! empty( $tours ) ) : ?>
			<section class="module toursList">
				<div class="toursList__inner">
					<h2 class="module__title">
						<?php esc_html_e( 'Tours', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $tours_count = count( $tours ) ); ?>)</span>
					</h2>
					<div class="toursList__list">
						<?php
						foreach ( $tours as $tour_data ) {
							$renderer->render( 'tour-card', [ 'tour_data' => $tour_data ] );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		
		<!-- Section Circuits -->
		<?php if ( ! empty( $circuits ) ) : ?>
			<section class="module circuitsList">
				<div class="circuitsList__inner">
					<h2 class="module__title">
						<?php esc_html_e( 'Circuits', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $circuits_count = count( $circuits ) ); ?>)</span>
					</h2>
					<div class="circuitsList__list">
						<?php
						foreach ( $circuits as $circuit_data ) {
							$renderer->render( 'circuit-card', [ 'circuit_data' => $circuit_data ] );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		
		<!-- Section Transferts -->
		<?php if ( ! empty( $transfers ) ) : ?>
			<section class="module transfersList">
				<div class="transfersList__inner">
					<h2 class="module__title">
						<?php esc_html_e( 'Transferts', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $transfers_count = count( $transfers ) ); ?>)</span>
					</h2>
					<div class="transfersList__list">
						<?php
						foreach ( $transfers as $transfer_data ) {
							$renderer->render( 'transfer-card', [ 'transfer_data' => $transfer_data ] );
						}
						?>
					</div>
				</div>
			</section>
		<?php endif; ?>
		
		<!-- Pagination -->
		<?php
		if ( $wp_query->max_num_pages > 1 ) {
			$renderer->render( 'pagination' );
		}
		?>
		
	<?php else : ?>
		<!-- Aucun résultat -->
		<section class="module search-no-results">
			<div class="search-no-results__inner">
				<h2 class="search-no-results__message">
					<?php
					if ( ! empty( $search_query ) ) {
						/* translators: %s: Search query */
						printf( 
							esc_html__( 'Désolé, aucun résultat trouvé pour "%s". Veuillez essayer avec d\'autres mots-clés.', 'transfertmarrakech' ), 
							$search_query_escaped 
						);
					} else {
						esc_html_e( 'Veuillez entrer un terme de recherche.', 'transfertmarrakech' );
					}
					?>
				</h2>
				<div class="search-no-results__suggestions">
					<h3><?php esc_html_e( 'Suggestions :', 'transfertmarrakech' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Vérifiez l\'orthographe de vos mots-clés', 'transfertmarrakech' ); ?></li>
						<li><?php esc_html_e( 'Utilisez des termes plus généraux', 'transfertmarrakech' ); ?></li>
						<li><?php esc_html_e( 'Essayez d\'autres mots-clés', 'transfertmarrakech' ); ?></li>
					</ul>
				</div>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
