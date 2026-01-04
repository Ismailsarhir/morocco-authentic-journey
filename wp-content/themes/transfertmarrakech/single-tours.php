<?php

/**
 * Template for displaying single tour posts
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
$tour = \TM\Utils\MetaHelper::get_current_post();
if (! $tour instanceof \WP_Post) {
	\get_footer();
	return;
}

$tour_id = $tour->ID;

// Récupère les meta du tour
$tour_meta = \TM\Utils\MetaHelper::get_tour_meta($tour_id);

// Extraction des données
$title = \TM\Utils\MetaHelper::get_post_title($tour);
$content = \apply_filters('the_content', $tour->post_content);
$thumbnail_id = \get_post_thumbnail_id($tour_id);
$location = $tour_meta[\TM\Core\Constants::META_TOUR_LOCATION] ?? '';
$duration_raw = $tour_meta[\TM\Core\Constants::META_TOUR_DURATION] ?? '';
$duration_unit = $tour_meta[\TM\Core\Constants::META_TOUR_DURATION_UNIT] ?? 'hours';
$duration = ! empty( $duration_raw ) ? \TM\Utils\MetaHelper::format_duration( $duration_raw, $duration_unit ) : '';
$highlights = $tour_meta[\TM\Core\Constants::META_TOUR_HIGHLIGHTS] ?? '';
$meeting_point = $tour_meta[\TM\Core\Constants::META_TOUR_MEETING_POINT] ?? '';
$tour_type = $tour_meta[\TM\Core\Constants::META_TOUR_TYPE] ?? '';
$difficulty = $tour_meta[\TM\Core\Constants::META_TOUR_DIFFICULTY] ?? '';
$languages = $tour_meta[\TM\Core\Constants::META_TOUR_LANGUAGES] ?? [];
$tags = $tour_meta[\TM\Core\Constants::META_TOUR_TAGS] ?? [];
$itinerary_title = $tour_meta[\TM\Core\Constants::META_TOUR_ITINERARY_TITLE] ?? '';
$itinerary_places = $tour_meta[\TM\Core\Constants::META_TOUR_ITINERARY] ?? [];
$included = $tour_meta[\TM\Core\Constants::META_TOUR_INCLUDED] ?? '';
$excluded = $tour_meta[\TM\Core\Constants::META_TOUR_EXCLUDED] ?? '';
$cancellation = $tour_meta[\TM\Core\Constants::META_TOUR_CANCELLATION] ?? '';
$price_tiers = $tour_meta[\TM\Core\Constants::META_TOUR_PRICE_TIERS] ?? [];

// Calcule le prix minimum depuis les tiers pour l'affichage
$min_price = '';
if (! empty($price_tiers) && is_array($price_tiers)) {
	$prices = array_filter(array_column($price_tiers, 'price'));
	if (! empty($prices)) {
		$min_price = min(array_map('floatval', $prices));
	}
}
$price_formatted = ! empty($min_price) ? \TM\Utils\MetaHelper::format_price_usd($min_price) : '';

// Villes visitées : utilise la localisation
$country_visited = $location;

// Points forts en texte
$highlights_text = ! empty($highlights) ? $highlights : '';

// Récupère les véhicules associés
$repository = \TM\Repository\PostRepository::get_instance();
$vehicle_posts = $repository->get_related_vehicles_for_tour($tour_id);
$tour_vehicles_data = [];

if (! empty($vehicle_posts)) {
	foreach ($vehicle_posts as $vehicle) {
		if (! $vehicle instanceof \WP_Post) {
			continue;
		}

		$vehicle_id = $vehicle->ID;

		// Récupère l'image avec fallback sur différentes tailles
		$thumbnail_url = \TM\Utils\MetaHelper::get_post_thumbnail_url_with_fallback($vehicle_id);

		// Skip vehicles without featured image
		if (! $thumbnail_url) {
			continue;
		}

		$tour_vehicles_data[] = [
			'vehicle_id' => $vehicle_id,
			'title'      => $vehicle->post_title,
			'thumbnail'  => $thumbnail_url,
		];
	}
}

// Récupère les données de destination pour le backlink
$destination_data = \TM\Utils\MetaHelper::get_destination_backlink($tour_id);
$destination_link = $destination_data['link'];
$destination_name = $destination_data['name'];

// URL de partage
$share_url = \get_permalink($tour_id);
$share_title = \esc_attr($title);

// Préparation des données pour les templates
$renderer = new \TM\Template\Renderer();

// Card info items pour le header
$card_info_items = [];

// Tags/Catégories - Affiche tous les tags sélectionnés
if (! empty($tags) && is_array($tags)) {
	$tags_text = implode(', ', array_map('esc_html', $tags));
	if (! empty($tags_text)) {
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
	'post_id'         => $tour_id,
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
	// Affiche les keywords/tags
	$renderer->render('product-keywords', [
		'primary_tags' => array_filter([$meeting_point]),
		'secondary_tags' => array_merge(
			$tags ?? [],
			[
				$tour_type ? esc_html__('Group Tour', 'transfertmarrakech') : '',
				$difficulty ? ucfirst($difficulty) : '',
			]
		),
	]);

	// Préparation des sections de description
	$description_sections = [];
	
	// Highlights
	if (! empty($highlights_text)) {
		$description_sections[] = [
			'title'   => esc_html__('Highlights', 'transfertmarrakech'),
			'content' => $highlights_text,
			'type'    => 'text',
			'class'   => 'highlights',
		];
	}
	
	
	// Affiche la description
	if (! empty($description_sections)) {
		$renderer->render('product-description', [
			'sections' => $description_sections,
		]);
	}

	// Affiche What's Included et What's Excluded
	$links_list_sections = [];
	if (! empty($included)) {
		$links_list_sections[] = [
			'title'   => esc_html__('Inclus', 'transfertmarrakech'),
			'content' => $included,
		];
	}
	if (! empty($excluded)) {
		$links_list_sections[] = [
			'title'   => esc_html__('Exclus', 'transfertmarrakech'),
			'content' => $excluded,
		];
	}
	if (! empty($links_list_sections)) {
		$renderer->render('links-list', [
			'block_title' => esc_html__('Ce qui est inclus et exclus', 'transfertmarrakech'),
			'sections' => $links_list_sections,
		]);
	}

	// Affiche Places / Étapes
	if (! empty($itinerary_places) && is_array($itinerary_places)) {
		$renderer->render('places-list', [
			'title' => $itinerary_title ?? esc_html__('Places / Étapes', 'transfertmarrakech'),
			'places' => $itinerary_places,
		]);
	}

	// Affiche les véhicules assignés au tour
	if (! empty($tour_vehicles_data)) {
		$renderer->render('vehicles-grid', [
			'vehicles' => $tour_vehicles_data,
			'title'    => esc_html__('Véhicules disponibles pour ce tour', 'transfertmarrakech'),
		]);
	}

	// Construit le message WhatsApp
	$whatsapp_message = sprintf(
		'Bonjour, %sje suis intéressé(e) par : %s%s%s',
		"\n",
		esc_html($title) . ' ' . esc_html__('à partir de', 'transfertmarrakech') . ' ' . esc_html($price_formatted) . ' ' . esc_html__('pour un tour de', 'transfertmarrakech') . ' ' . esc_html($meeting_point) . ' ' . esc_html__('à', 'transfertmarrakech') . ' ' . esc_html($location) . ' ',
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
