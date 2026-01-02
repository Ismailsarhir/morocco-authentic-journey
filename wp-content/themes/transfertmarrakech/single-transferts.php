<?php

/**
 * Template for displaying single transfer posts
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
$transfer = \TM\Utils\MetaHelper::get_current_post();
if (! $transfer instanceof \WP_Post) {
	\get_footer();
	return;
}

$transfer_id = $transfer->ID;

// Récupère les meta du transfert
$transfer_meta = \TM\Utils\MetaHelper::get_transfer_meta($transfer_id);

// Extraction des données
$title = \TM\Utils\MetaHelper::get_post_title($transfer);
$content = \apply_filters('the_content', $transfer->post_content);
$thumbnail_id = \get_post_thumbnail_id($transfer_id);
$price = $transfer_meta[\TM\Core\Constants::META_TRANSFER_PRICE] ?? '';
$price_formatted = \TM\Utils\MetaHelper::format_price($price);
$pickup = $transfer_meta[\TM\Core\Constants::META_TRANSFER_PICKUP] ?? '';
$dropoff = $transfer_meta[\TM\Core\Constants::META_TRANSFER_DROPOFF] ?? '';
$duration_estimate = $transfer_meta[\TM\Core\Constants::META_TRANSFER_DURATION_ESTIMATE] ?? '';
$description = $transfer_meta[\TM\Core\Constants::META_TRANSFER_DESCRIPTION] ?? '';

// Villes visitées : utilise pickup et dropoff
$cities_visited = [];
if (! empty($pickup)) {
	$cities_visited[] = $pickup;
}
if (! empty($dropoff)) {
	$cities_visited[] = $dropoff;
}
$cities_visited = \array_unique($cities_visited);

// Récupère le véhicule associé
$vehicle_id = $transfer_meta[\TM\Core\Constants::META_TRANSFER_VEHICLE] ?? 0;
$transfer_vehicles_data = [];

if ($vehicle_id > 0) {
	$repository = \TM\Repository\PostRepository::get_instance();
	$vehicle = $repository->get_by_id((int) $vehicle_id);

	if ($vehicle instanceof \WP_Post) {
		// Récupère l'image avec fallback sur différentes tailles
		$thumbnail_url = \TM\Utils\MetaHelper::get_post_thumbnail_url_with_fallback($vehicle_id);

		// Skip vehicle without featured image
		if ($thumbnail_url) {
			$transfer_vehicles_data[] = [
				'vehicle_id' => $vehicle_id,
				'title'      => $vehicle->post_title,
				'thumbnail'  => $thumbnail_url,
			];
		}
	}
}

// Formatage de la durée estimée
$duration_display = \TM\Utils\MetaHelper::format_duration($duration_estimate);

// Récupère les données de destination pour le backlink
$destination_data = \TM\Utils\MetaHelper::get_destination_backlink($transfer_id);
$destination_link = $destination_data['link'];
$destination_name = $destination_data['name'];

// URL de partage
$share_url = \get_permalink($transfer_id);
$share_title = \esc_attr($title);

// Préparation des données pour les templates
$renderer = new \TM\Template\Renderer();

// Card info items pour le header
$card_info_items = [];
if (! empty($pickup)) {
	$card_info_items[] = [
		'label' => esc_html__('Départ :', 'transfertmarrakech'),
		'value' => $pickup,
	];
}
if (! empty($dropoff)) {
	$card_info_items[] = [
		'label' => esc_html__('Arrivée :', 'transfertmarrakech'),
		'value' => $dropoff,
	];
}
if (! empty($duration_display)) {
	$card_info_items[] = [
		'label' => esc_html__('Durée estimée :', 'transfertmarrakech'),
		'value' => $duration_display,
	];
}

// Affiche le header du produit
$renderer->render('product-header', [
	'title'           => $title,
	'thumbnail_id'    => $thumbnail_id,
	'card_info_items' => $card_info_items,
	'price_formatted' => $price_formatted,
	'post_id'         => $transfer_id,
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
		'primary_tags' => array_filter([$pickup, $dropoff]),
		'secondary_tags' => [
			esc_html__('Transfert garanti', 'transfertmarrakech'),
			esc_html__('Recommandé', 'transfertmarrakech'),
			esc_html__('Nouvelle route', 'transfertmarrakech'),
		],
	]);

	// Préparation des sections de description
	$description_sections = [];
	
	if (! empty($cities_visited)) {
		$description_sections[] = [
			'title'   => esc_html__('Villes visitées', 'transfertmarrakech'),
			'content' => $cities_visited,
			'type'    => 'list',
			'class'   => '',
		];
	}
	
	if (! empty($description)) {
		$description_sections[] = [
			'title'   => esc_html__('Description', 'transfertmarrakech'),
			'content' => $description,
			'type'    => 'text',
			'class'   => 'summary',
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

	// Affiche le véhicule assigné au transfert
	if (! empty($transfer_vehicles_data)) {
		$renderer->render('vehicles-grid', [
			'vehicles' => $transfer_vehicles_data,
			'title'    => esc_html__('Véhicule disponible pour ce transfert', 'transfertmarrakech'),
		]);
	}

	// Construit le message WhatsApp
	$whatsapp_message = sprintf(
		'Bonjour, %sje suis intéressé(e) par : %s%s%s',
		"\n",
		esc_html($title) . ' ' . esc_html__('à partir de', 'transfertmarrakech') . ' ' . esc_html($price_formatted) . ' ' . esc_html__('MAD*', 'transfertmarrakech') . ' ' . esc_html__('pour un transfert de', 'transfertmarrakech') . ' ' . esc_html($pickup) . ' ' . esc_html__('à', 'transfertmarrakech') . ' ' . esc_html($dropoff) . ' ',
		"\n",
		esc_url($share_url)
	);
	$whatsapp_url = \TM\Utils\MetaHelper::build_whatsapp_url($whatsapp_message);

	// Affiche la bannière
	$renderer->render('product-banner', [
		'title'         => $title,
		'thumbnail_id' => $thumbnail_id,
		'share_url'     => $share_url,
		'share_title'   => $share_title,
		'whatsapp_url'  => $whatsapp_url,
		'whatsapp_label' => esc_html__('Contacter une agence', 'transfertmarrakech'),
	]);
	?>
</main>

<?php
get_footer();
