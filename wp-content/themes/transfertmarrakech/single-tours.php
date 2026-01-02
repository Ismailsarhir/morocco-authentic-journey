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
$duration = $tour_meta[\TM\Core\Constants::META_TOUR_DURATION] ?? '';
$duration_minutes = $tour_meta[\TM\Core\Constants::META_TOUR_DURATION_MINUTES] ?? 0;
$price = $tour_meta[\TM\Core\Constants::META_TOUR_PRICE] ?? '';
$price_formatted = \TM\Utils\MetaHelper::format_price($price);
$highlights = $tour_meta[\TM\Core\Constants::META_TOUR_HIGHLIGHTS] ?? '';
$meeting_point = $tour_meta[\TM\Core\Constants::META_TOUR_MEETING_POINT] ?? '';

// Villes visitées : utilise la localisation
$country_visited = $location;

// Formate les points forts en liste (séparés par virgules uniquement)
$highlights_list = [];
if (! empty($highlights)) {
	$highlights_list = \array_filter(\array_map('trim', \explode(',', $highlights)));
}

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

// Formatage de la durée
$duration_display = \TM\Utils\MetaHelper::format_duration($duration);

// Calcul des jours et récupération des nuits et repas
$days = max(0, (int) $duration_minutes);
$nights = max(0, (int) ($tour_meta[\TM\Core\Constants::META_TOUR_NIGHTS] ?? max(0, $days - 1)));
$meals = max(0, (int) ($tour_meta[\TM\Core\Constants::META_TOUR_MEALS] ?? 0));

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
if ($days > 0) {
	$card_info_items[] = [
		'label' => '',
		'value' => sprintf(
			/* translators: %d: Number of days */
			esc_html__('%d jours', 'transfertmarrakech'),
			$days
		),
	];
}
if ($nights > 0) {
	$card_info_items[] = [
		'label' => '',
		'value' => sprintf(
			/* translators: %d: Number of nights */
			esc_html__('%d nuits', 'transfertmarrakech'),
			$nights
		),
	];
}
if ($meals > 0) {
	$card_info_items[] = [
		'label' => '',
		'value' => sprintf(
			/* translators: %d: Number of meals */
			esc_html__('%d repas', 'transfertmarrakech'),
			$meals
		),
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
		'primary_tags' => array_filter([$location, $meeting_point]),
		'secondary_tags' => [
			esc_html__('Départ garanti', 'transfertmarrakech'),
			esc_html__('Coup de coeur', 'transfertmarrakech'),
			esc_html__('Nouvel itinéraire', 'transfertmarrakech'),
		],
	]);

	// Préparation des sections de description
	$description_sections = [];
	
	if (! empty($country_visited)) {
		$description_sections[] = [
			'title'   => esc_html__('Villes visitées', 'transfertmarrakech'),
			'content' => [$country_visited],
			'type'    => 'list',
			'class'   => '',
		];
	}
	
	if (! empty($highlights_list)) {
		$description_sections[] = [
			'title'   => esc_html__('Places visitées', 'transfertmarrakech'),
			'content' => $highlights_list,
			'type'    => 'list',
			'class'   => 'cities',
		];
	}
	
	if (! empty($content)) {
		$description_sections[] = [
			'title'   => esc_html__('Sommaire', 'transfertmarrakech'),
			'content' => $content,
			'type'    => 'text',
			'class'   => 'summary',
		];
	}

	// Affiche la description
	if (! empty($description_sections)) {
		$renderer->render('product-description', [
			'sections' => $description_sections,
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
		esc_html($title) . ' ' . esc_html__('à partir de', 'transfertmarrakech') . ' ' . esc_html($price_formatted) . ' ' . esc_html__('MAD*', 'transfertmarrakech') . ' ' . esc_html__('pour un tour de', 'transfertmarrakech') . ' ' . esc_html($meeting_point) . ' ' . esc_html__('à', 'transfertmarrakech') . ' ' . esc_html($location) . ' ',
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
	?>
</main>

<?php
get_footer();
