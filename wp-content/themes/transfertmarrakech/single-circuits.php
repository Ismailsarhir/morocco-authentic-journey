<?php

/**
 * Template for displaying single circuit posts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if (! defined('ABSPATH')) {
	exit;
}

get_header();

// Récupère le post actuel
$circuit = \TM\Utils\MetaHelper::get_current_post();
if (! $circuit instanceof \WP_Post) {
	\get_footer();
	return;
}

$circuit_id = $circuit->ID;

// Récupère les meta du circuit
$circuit_meta = \TM\Utils\MetaHelper::get_circuit_meta($circuit_id);

// Extraction des données
$title = \TM\Utils\MetaHelper::get_post_title($circuit);
$content = \apply_filters('the_content', $circuit->post_content);
$thumbnail_id = \get_post_thumbnail_id($circuit_id);
$location = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_LOCATION] ?? '';
$duration_days = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_DURATION_DAYS] ?? '';
$duration = ! empty($duration_days) ? sprintf(esc_html(_n('%d day', '%d days', $duration_days, 'transfertmarrakech')), $duration_days) : '';
$highlights = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_HIGHLIGHTS] ?? '';
$pickup_info = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_PICKUP_INFO] ?? '';
$meeting_point = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_MEETING_POINT] ?? '';
$difficulty = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_DIFFICULTY] ?? '';
$languages = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_LANGUAGES] ?? [];
$tags = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_TAGS] ?? [];
$itinerary_days = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_ITINERARY_DAYS] ?? [];
$included = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_INCLUDED] ?? '';
$excluded = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_EXCLUDED] ?? '';
$not_suitable = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_NOT_SUITABLE] ?? '';
$important_info = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_IMPORTANT_INFO] ?? '';
$what_to_bring = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_WHAT_TO_BRING] ?? '';
$not_allowed = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_NOT_ALLOWED] ?? '';
$know_before_go = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_KNOW_BEFORE_GO] ?? '';
$cancellation = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_CANCELLATION] ?? '';
$price_tiers = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_PRICE_TIERS] ?? [];

// Calcule le prix minimum depuis les tiers pour l'affichage (optimized)
$price_formatted = '';
if (!empty($price_tiers) && is_array($price_tiers)) {
	$prices = [];
	foreach ($price_tiers as $tier) {
		$price = $tier['price'] ?? '';
		if (is_numeric($price)) {
			$prices[] = (float) $price;
		}
	}
	
	if (!empty($prices)) {
		$min_price = min($prices);
		$price_formatted = \TM\Utils\MetaHelper::format_price_usd($min_price);
	}
}

// Récupère les véhicules associés (optimized with batch query)
$vehicle_ids = $circuit_meta[\TM\Core\Constants::META_CIRCUIT_VEHICLES] ?? [];
$circuit_vehicles_data = [];

if (!empty($vehicle_ids) && is_array($vehicle_ids)) {
	$vehicle_ids = array_map('intval', array_filter($vehicle_ids));
	
	if (!empty($vehicle_ids)) {
		$vehicles_cache_key = 'tm_circuit_vehicles_' . $circuit_id;
		$circuit_vehicles_data = \get_transient( $vehicles_cache_key );
		if ( false === $circuit_vehicles_data ) {
			$circuit_vehicles_data = [];
			$vehicles = \get_posts([
				'post_type'      => \TM\Core\Constants::POST_TYPE_VEHICLE,
				'post__in'       => $vehicle_ids,
				'posts_per_page' => -1,
				'orderby'        => 'post__in',
				'post_status'    => 'publish',
				'no_found_rows'  => true,
				'update_post_meta_cache' => true,
				'update_post_term_cache' => false,
			]);

			foreach ($vehicles as $vehicle) {
				$thumbnail_url = \TM\Utils\MetaHelper::get_post_thumbnail_url_with_fallback($vehicle->ID);
				
				// Skip vehicles without featured image
				if (!$thumbnail_url) {
					continue;
				}

				$circuit_vehicles_data[] = [
					'vehicle_id' => $vehicle->ID,
					'title'      => $vehicle->post_title,
					'thumbnail'  => $thumbnail_url,
				];
			}
			\set_transient( $vehicles_cache_key, $circuit_vehicles_data, 12 * HOUR_IN_SECONDS );
		}
	}
}

// Récupère les données de destination pour le backlink
$destination_data = \TM\Utils\MetaHelper::get_destination_backlink($circuit_id);
$destination_link = $destination_data['link'];
$destination_name = $destination_data['name'];

// URL de partage
$share_url = \get_permalink($circuit_id);
$share_title = \esc_attr($title);

// Préparation des données pour les templates
$renderer = new \TM\Template\Renderer();

// Card info items pour le header
$card_info_items = [];

// Tags/Catégories - Affiche tous les tags sélectionnés
if (! empty($tags) && is_array($tags)) {
	$tag_options = [
		'photography'      => __( 'Photography', 'transfertmarrakech' ),
		'historical'       => __( 'Historical', 'transfertmarrakech' ),
		'sightseeing'      => __( 'Sightseeing', 'transfertmarrakech' ),
		'adventure'        => __( 'Adventure', 'transfertmarrakech' ),
		'adventure sports' => __( 'Adventure Sports', 'transfertmarrakech' ),
		'Paragliding'      => __( 'Paragliding', 'transfertmarrakech' ),
		'ballooning'       => __( 'Ballooning', 'transfertmarrakech' ),
		'architectural'    => __( 'Architectural', 'transfertmarrakech' ),
		'cultural'         => __( 'Cultural', 'transfertmarrakech' ),
		'nature'           => __( 'Nature', 'transfertmarrakech' ),
		'gastronomical'    => __( 'Gastronomical', 'transfertmarrakech' ),
		'Desert'           => __( 'Desert', 'transfertmarrakech' ),
		'atv'              => __( 'ATV', 'transfertmarrakech' ),
	];
	
	$tag_labels = [];
	foreach ( $tags as $tag_value ) {
		if ( ! empty( $tag_value ) && isset( $tag_options[ $tag_value ] ) ) {
			$tag_labels[] = $tag_options[ $tag_value ];
		}
	}
	
	if (! empty($tag_labels)) {
		$tags_text = implode(', ', $tag_labels);
		$card_info_items[] = [
			'label' => '',
			'value' => $tags_text,
		];
	}
}

// Duration
if (! empty($duration)) {
	$card_info_items[] = [
		'label' => esc_html__('Durée:', 'transfertmarrakech'),
		'value' => esc_html($duration),
	];
}

// Affiche le header du produit
$renderer->render('product-header', [
	'title'           => $title,
	'thumbnail_id'    => $thumbnail_id,
	'card_info_items' => $card_info_items,
	'price_formatted' => $price_formatted,
	'post_id'         => $circuit_id,
]);

// Affiche le backlink
if (! empty($destination_name)) {
	$renderer->render('product-backlink', [
		'destination_link' => $destination_link,
		'destination_name' => $destination_name,
	]);
}
?>

<main class="product-body">
	<?php
	// Format language labels
	$language_labels = [];
	if (! empty($languages) && is_array($languages)) {
		$language_options = [
			'english'   => __( 'English', 'transfertmarrakech' ),
			'french'    => __( 'French', 'transfertmarrakech' ),
			'spanish'   => __( 'Spanish', 'transfertmarrakech' ),
			'portuguese' => __( 'Portuguese', 'transfertmarrakech' ),
			'arabic'    => __( 'Arabic', 'transfertmarrakech' ),
			'german'    => __( 'German', 'transfertmarrakech' ),
			'italian'   => __( 'Italian', 'transfertmarrakech' ),
			'slovenian' => __( 'Slovenian', 'transfertmarrakech' ),
			'dutch'     => __( 'Dutch', 'transfertmarrakech' ),
		];
		
		foreach ( $languages as $lang_value ) {
			if ( ! empty( $lang_value ) && isset( $language_options[ $lang_value ] ) ) {
				$language_labels[] = $language_options[ $lang_value ];
			}
		}
	}
	
	// Format tags labels
	$tag_labels = [];
	if (! empty($tags) && is_array($tags)) {
		$tag_options = [
			'photography'      => __( 'Photography', 'transfertmarrakech' ),
			'historical'       => __( 'Historical', 'transfertmarrakech' ),
			'sightseeing'      => __( 'Sightseeing', 'transfertmarrakech' ),
			'adventure'        => __( 'Adventure', 'transfertmarrakech' ),
			'adventure sports' => __( 'Adventure Sports', 'transfertmarrakech' ),
			'Paragliding'      => __( 'Paragliding', 'transfertmarrakech' ),
			'ballooning'       => __( 'Ballooning', 'transfertmarrakech' ),
			'architectural'    => __( 'Architectural', 'transfertmarrakech' ),
			'cultural'         => __( 'Cultural', 'transfertmarrakech' ),
			'nature'           => __( 'Nature', 'transfertmarrakech' ),
			'gastronomical'    => __( 'Gastronomical', 'transfertmarrakech' ),
			'Desert'           => __( 'Desert', 'transfertmarrakech' ),
			'atv'              => __( 'ATV', 'transfertmarrakech' ),
		];
		
		foreach ( $tags as $tag_value ) {
			if ( ! empty( $tag_value ) && isset( $tag_options[ $tag_value ] ) ) {
				$tag_labels[] = $tag_options[ $tag_value ];
			}
		}
	}
	
	// Format difficulty label
	$difficulty_label = '';
	if (! empty($difficulty)) {
		$difficulty_options = [
			'easy'   => __( 'Easy', 'transfertmarrakech' ),
			'medium' => __( 'Medium', 'transfertmarrakech' ),
			'hard'   => __( 'Hard', 'transfertmarrakech' ),
		];
		$difficulty_label = $difficulty_options[ $difficulty ] ?? ucfirst($difficulty);
	}
	
	// Affiche les keywords/tags
	$renderer->render('product-keywords', [
		'primary_tags' => array_filter([$meeting_point]),
		'secondary_tags' => array_merge(
			$tag_labels ?? [],
			[
				$difficulty_label,
			],
			$language_labels ?? []
		),
	]);

	// Préparation des sections de description
	$description_sections = [];

	// Highlights
	if (! empty($highlights)) {
		$description_sections[] = [
			'title'   => esc_html__('Highlights', 'transfertmarrakech'),
			'content' => $highlights,
			'type'    => 'text',
			'class'   => 'highlights',
		];
	}

	// Pickup Information
	if (! empty($pickup_info)) {
		$description_sections[] = [
			'title'   => esc_html__('Pickup Information', 'transfertmarrakech'),
			'content' => $pickup_info,
			'type'    => 'text',
			'class'   => 'pickup-info',
		];
	}

	// Affiche la description
	if (! empty($description_sections)) {
		$renderer->render('product-description', [
			'sections' => $description_sections,
		]);
	}

	// Affiche Important Information, What to Bring, Not Allowed, Know Before You Go
	$info_sections = [];
	if (! empty($important_info)) {
		$info_sections[] = [
			'title'   => esc_html__('Important Information', 'transfertmarrakech'),
			'content' => $important_info,
		];
	}
	if (! empty($what_to_bring)) {
		$info_sections[] = [
			'title'   => esc_html__('What to Bring', 'transfertmarrakech'),
			'content' => $what_to_bring,
		];
	}
	if (! empty($not_allowed)) {
		$info_sections[] = [
			'title'   => esc_html__('Not Allowed', 'transfertmarrakech'),
			'content' => $not_allowed,
		];
	}
	if (! empty($know_before_go)) {
		$info_sections[] = [
			'title'   => esc_html__('Know Before You Go', 'transfertmarrakech'),
			'content' => $know_before_go,
		];
	}
	if (! empty($info_sections)) {
		$renderer->render('links-list', [
			'block_title' => esc_html__('Important Information', 'transfertmarrakech'),
			'sections' => $info_sections,
		]);
	}

	// Affiche Itinerary by Days
	if (!empty($itinerary_days) && is_array($itinerary_days)) {
		// Filter valid days in one pass
		foreach ($itinerary_days as $day) {
			if (!is_array($day)) {
				continue;
			}
			
			$day_title = $day['day_title'] ?? '';
			$steps = $day['steps'] ?? [];
			
			if (!empty($day_title) || (!empty($steps) && is_array($steps))) {
				\TM\Utils\CircuitHelper::render_circuit_day($day, $renderer);
			}
		}
	}

	// Affiche What's Included et What's Excluded
	$links_list_sections = [];
	if (! empty($included)) {
		$links_list_sections[] = [
			'title'   => esc_html__('Includes', 'transfertmarrakech'),
			'content' => $included,
		];
	}
	if (! empty($excluded)) {
		$links_list_sections[] = [
			'title'   => esc_html__('Excluded', 'transfertmarrakech'),
			'content' => $excluded,
		];
	}
	if (! empty($not_suitable)) {
		$links_list_sections[] = [
			'title'   => esc_html__('Not Suitable For', 'transfertmarrakech'),
			'content' => $not_suitable,
		];
	}
	if (! empty($links_list_sections)) {
		$renderer->render('links-list', [
			'block_title' => esc_html__('What\'s Included, Excluded, and Restrictions', 'transfertmarrakech'),
			'sections' => $links_list_sections,
		]);
	}

	// Affiche les véhicules assignés au circuit
	if (! empty($circuit_vehicles_data)) {
		$renderer->render('vehicles-grid', [
			'vehicles' => $circuit_vehicles_data,
			'title'    => esc_html__('Véhicules disponibles pour ce circuit', 'transfertmarrakech'),
		]);
	}

	// Construit le message WhatsApp
	$whatsapp_message = sprintf(
		'Bonjour, %sje suis intéressé(e) par : %s%s%s',
		"\n",
		esc_html($title) . ' ' . esc_html__('à partir de', 'transfertmarrakech') . ' ' . esc_html($price_formatted) . ' ' . esc_html__('pour un circuit de', 'transfertmarrakech') . ' ' . esc_html($duration) . ' ' . esc_html__('à', 'transfertmarrakech') . ' ' . esc_html($location) . ' ',
		"\n",
		esc_url($share_url)
	);
	$whatsapp_url = \TM\Utils\MetaHelper::build_whatsapp_url($whatsapp_message);

	// Affiche la bannière
	$renderer->render('product-banner', [
		'title'         => $title,
		'thumbnail_id'  => $thumbnail_id,
		'share_url'     => $share_url,
		'share_title'   => $share_title,
		'whatsapp_url'  => $whatsapp_url,
		'whatsapp_label' => esc_html__('Contacter une agence', 'transfertmarrakech'),
	]);

	// Affiche les prix par nombre de personnes (tableau optimisé)
	if (! empty($price_tiers) && is_array($price_tiers)) {
		$renderer->render('price-table', [
			'price_tiers' => $price_tiers,
			'cancellation' => $cancellation ?? '',
		]);
	}
	?>
</main>

<?php
get_footer();
