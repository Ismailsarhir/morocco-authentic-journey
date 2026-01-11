<?php
/**
 * Template for displaying search results
 * Optimized for performance
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

get_header();

// Récupère le terme de recherche (une seule fois)
$search_query = get_search_query();
$search_query_escaped = esc_html( $search_query );

// Variables initialisées
$tours = [];
$circuits = [];
$transfers = [];
$total_results = 0;
$max_num_pages = 1;

// Nombre d'éléments par type par page
$items_per_type = 6;
$current_page = max( 1, get_query_var( 'paged' ) ?: 1 );

// Instances et constantes (chargées une seule fois)
$renderer = new \TM\Template\Renderer();

// Vérifie qu'on est bien sur une page de recherche avec un terme de recherche
if ( is_search() && ! empty( $search_query ) ) {
	// Récupère les instances (singleton - optimisé)
	$tours_list = \TM\Core\ToursList::get_instance();
	$circuits_list = \TM\Core\CircuitsList::get_instance();
	$transfers_list = \TM\Core\TransfersList::get_instance();
	
	// Cache les constantes pour éviter les appels répétés
	$post_type_tour = \TM\Core\Constants::POST_TYPE_TOUR;
	$post_type_circuit = \TM\Core\Constants::POST_TYPE_CIRCUIT;
	$post_type_transfer = \TM\Core\Constants::POST_TYPE_TRANSFER;
	
	// Arguments de base pour les requêtes (partagés entre toutes les requêtes)
	$args_base = [
		's'                      => $search_query,
		'post_status'            => 'publish',
		'posts_per_page'         => $items_per_type,
		'paged'                  => $current_page,
		'update_post_meta_cache' => true,
		'update_post_term_cache' => true,
		'no_found_rows'          => false, // Nécessaire pour found_posts
	];
	
	// Query pour Tours
	$tours_query = new \WP_Query( array_merge( $args_base, [ 'post_type' => $post_type_tour ] ) );
	if ( $tours_query->have_posts() ) {
		foreach ( $tours_query->posts as $post ) {
			$tour_data = $tours_list->format_tour_data( $post );
			if ( $tour_data ) {
				$tours[] = $tour_data;
			}
		}
	}
	$tours_count = $tours_query->found_posts;
	$tours_max_pages = $tours_query->max_num_pages;
	wp_reset_postdata();
	
	// Query pour Circuits
	$circuits_query = new \WP_Query( array_merge( $args_base, [ 'post_type' => $post_type_circuit ] ) );
	if ( $circuits_query->have_posts() ) {
		foreach ( $circuits_query->posts as $post ) {
			$circuit_data = $circuits_list->format_circuit_data( $post );
			if ( $circuit_data ) {
				$circuits[] = $circuit_data;
			}
		}
	}
	$circuits_count = $circuits_query->found_posts;
	$circuits_max_pages = $circuits_query->max_num_pages;
	wp_reset_postdata();
	
	// Query pour Transfers
	$transfers_query = new \WP_Query( array_merge( $args_base, [ 'post_type' => $post_type_transfer ] ) );
	if ( $transfers_query->have_posts() ) {
		foreach ( $transfers_query->posts as $post ) {
			$transfer_data = $transfers_list->format_transfer_data( $post );
			if ( $transfer_data ) {
				$transfers[] = $transfer_data;
			}
		}
	}
	$transfers_count = $transfers_query->found_posts;
	$transfers_max_pages = $transfers_query->max_num_pages;
	wp_reset_postdata();
	
	// Calcule les totaux (une seule fois)
	$total_results = $tours_count + $circuits_count + $transfers_count;
	$max_num_pages = max( $tours_max_pages, $circuits_max_pages, $transfers_max_pages );
	
	// Cache les counts pour éviter les appels répétés dans le template
	$tours_display_count = count( $tours );
	$circuits_display_count = count( $circuits );
	$transfers_display_count = count( $transfers );
} else {
	$tours_display_count = 0;
	$circuits_display_count = 0;
	$transfers_display_count = 0;
}

?>
<!-- Hero avec titre de recherche -->
<div class="search-hero">
	<div class="search-hero__inner">
		<h1 class="search-hero__title animated-title">
			<?php 
			if ( ! empty( $search_query ) ) {
				/* translators: %s: Search query */
				printf( esc_html__( 'Search results for: %s', 'transfertmarrakech' ), $search_query_escaped );
			} else {
				esc_html_e( 'Search Results', 'transfertmarrakech' );
			}
			?>
		</h1>
		<?php if ( $total_results > 0 ) : ?>
			<p class="search-hero__subtitle animated-title">
				<?php
				/* translators: %d: Number of results */
				printf( esc_html( _n( '%d result found', '%d results found', $total_results, 'transfertmarrakech' ) ), $total_results );
				?>
			</p>
		<?php endif; ?>
	</div>
</div>

<div class="modules">
	<?php if ( $total_results > 0 ) : ?>
		<!-- Section Tours -->
		<?php if ( $tours_display_count > 0 ) : ?>
			<section class="module toursList">
				<div class="toursList__inner">
					<h2 class="toursList__title animated-title">
						<?php esc_html_e( 'Tours', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $tours_display_count ); ?>)</span>
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
		<?php if ( $circuits_display_count > 0 ) : ?>
			<section class="module circuitsList">
				<div class="circuitsList__inner">
					<h2 class="circuitsList__title animated-title">
						<?php esc_html_e( 'Circuits', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $circuits_display_count ); ?>)</span>
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
		<?php if ( $transfers_display_count > 0 ) : ?>
			<section class="module transfersList">
				<div class="transfersList__inner">
					<h2 class="transfersList__title animated-title">
						<?php esc_html_e( 'Transfers', 'transfertmarrakech' ); ?>
						<span class="module__count">(<?php echo esc_html( $transfers_display_count ); ?>)</span>
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
		if ( $max_num_pages > 1 ) {
			global $wp_query;
			$original_max_pages = $wp_query->max_num_pages;
			$wp_query->max_num_pages = $max_num_pages;
			$renderer->render( 'pagination' );
			$wp_query->max_num_pages = $original_max_pages;
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
							esc_html__( 'Sorry, no results found for "%s". Please try with other keywords.', 'transfertmarrakech' ), 
							$search_query_escaped 
						);
					} else {
						esc_html_e( 'Please enter a search term.', 'transfertmarrakech' );
					}
					?>
				</h2>
				<div class="search-no-results__suggestions">
					<h3><?php esc_html_e( 'Suggestions:', 'transfertmarrakech' ); ?></h3>
					<ul>
						<li><?php esc_html_e( 'Check the spelling of your keywords', 'transfertmarrakech' ); ?></li>
						<li><?php esc_html_e( 'Use more general terms', 'transfertmarrakech' ); ?></li>
						<li><?php esc_html_e( 'Try other keywords', 'transfertmarrakech' ); ?></li>
					</ul>
				</div>
			</div>
		</section>
	<?php endif; ?>
</div>

<?php get_footer(); ?>
