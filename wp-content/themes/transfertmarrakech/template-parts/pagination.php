<?php
/**
 * Template part pour la pagination
 * Optimisé pour les performances
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

global $wp_query;

if ( ! isset( $wp_query ) || $wp_query->max_num_pages <= 1 ) {
	return;
}

$current_page = max( 1, get_query_var( 'paged' ) ?: 1 );
$total_pages = $wp_query->max_num_pages;

// Cache la base URL (optimisé)
static $base_url = null;
if ( $base_url === null ) {
	$base_url = str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) );
}

// Configuration de la pagination
$pagination_links = paginate_links( [
	'base'      => $base_url,
	'format'    => '',
	'current'   => $current_page,
	'total'     => $total_pages,
	'prev_next' => false,
	'end_size'  => 1,
	'mid_size'  => 2,
	'type'      => 'array',
	'show_all'  => false,
] );

if ( empty( $pagination_links ) ) {
	return;
}

// Pré-compile les regex (optimisé)
static $page_regex = '/>(\d+)</';
static $url_regex = '/href=["\']([^"\']+)["\']/';

?>
<div class="pager">
	<ul>
		<?php foreach ( $pagination_links as $link ) : ?>
			<?php
			// Optimisé : vérifications combinées
			$is_current = strpos( $link, 'current' ) !== false;
			$is_dots = strpos( $link, '&hellip;' ) !== false || strpos( $link, '…' ) !== false || strpos( $link, 'dots' ) !== false;
			
			// Extraction optimisée avec regex pré-compilée
			$page_number = preg_match( $page_regex, $link, $matches ) ? (int) $matches[1] : null;
			$url = preg_match( $url_regex, $link, $url_matches ) ? esc_url( $url_matches[1] ) : ( $page_number ? get_pagenum_link( $page_number ) : '' );
			
			if ( $is_dots ) :
				?>
				<li><span>...</span></li>
			<?php elseif ( $page_number ) : ?>
				<li>
					<a href="<?php echo esc_url( $url ); ?>" 
					   data-value="<?php echo esc_attr( (string) ( $page_number - 1 ) ); ?>" 
					   data-page="<?php echo esc_attr( $page_number ); ?>" 
					   class="<?php echo $is_current ? 'is-selected' : 'pager-link'; ?>">
						<?php echo esc_html( $page_number ); ?>
					</a>
				</li>
			<?php endif; ?>
		<?php endforeach; ?>
	</ul>
</div>
