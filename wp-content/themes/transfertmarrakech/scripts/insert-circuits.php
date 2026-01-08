<?php
/**
 * Script to insert multiple circuits at once
 * 
 * Usage: 
 * 1. Edit the $circuits_data array below with your circuit information
 * 2. Run this script from WordPress root: php wp-content/themes/transfertmarrakech/scripts/insert-circuits.php
 *    Or access via browser: http://yoursite.com/wp-content/themes/transfertmarrakech/scripts/insert-circuits.php
 * 
 * @package TransfertMarrakech
 */

// Load WordPress
require_once( dirname( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) ) . '/wp-load.php' );

use TM\Core\Constants;

if ( ! current_user_can( 'edit_posts' ) ) {
	die( 'You do not have permission to run this script.' );
}

/**
 * Insert a single circuit
 * 
 * @param array $circuit_data Circuit data array
 * @return int|WP_Error Post ID on success, WP_Error on failure
 */
function insert_circuit( array $circuit_data ) {
	// Required fields
	if ( empty( $circuit_data['title'] ) ) {
		return new \WP_Error( 'missing_title', 'Circuit title is required' );
	}
	
	// Prepare post data
	$post_data = array(
		'post_title'    => sanitize_text_field( $circuit_data['title'] ),
		'post_content'  => isset( $circuit_data['content'] ) ? $circuit_data['content'] : '',
		'post_excerpt'  => isset( $circuit_data['excerpt'] ) ? $circuit_data['excerpt'] : '',
		'post_status'   => isset( $circuit_data['status'] ) ? $circuit_data['status'] : 'publish',
		'post_type'     => Constants::POST_TYPE_CIRCUIT,
		'post_author'   => isset( $circuit_data['author_id'] ) ? (int) $circuit_data['author_id'] : get_current_user_id(),
	);
	
	// Insert the post
	$post_id = wp_insert_post( $post_data, true );
	
	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}
	
	// Set featured image if provided
	if ( ! empty( $circuit_data['thumbnail_url'] ) || ! empty( $circuit_data['thumbnail_path'] ) ) {
		$thumbnail_url = $circuit_data['thumbnail_url'] ?? '';
		$thumbnail_path = $circuit_data['thumbnail_path'] ?? '';
		
		if ( ! empty( $thumbnail_url ) ) {
			// Download image from URL
			$attachment_id = insert_image_from_url( $thumbnail_url, $post_id );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		} elseif ( ! empty( $thumbnail_path ) ) {
			// Upload from local path
			$attachment_id = insert_image_from_path( $thumbnail_path, $post_id );
			if ( $attachment_id ) {
				set_post_thumbnail( $post_id, $attachment_id );
			}
		}
	}
	
	// Set meta fields
	$meta_fields = array(
		Constants::META_CIRCUIT_LOCATION          => 'location',
		Constants::META_CIRCUIT_DURATION_DAYS     => 'duration_days',
		Constants::META_CIRCUIT_HIGHLIGHTS        => 'highlights',
		Constants::META_CIRCUIT_PICKUP_INFO       => 'pickup_info',
		Constants::META_CIRCUIT_MEETING_POINT     => 'meeting_point',
		Constants::META_CIRCUIT_DIFFICULTY        => 'difficulty',
		Constants::META_CIRCUIT_LANGUAGES         => 'languages',
		Constants::META_CIRCUIT_TAGS              => 'tags',
		Constants::META_CIRCUIT_INCLUDED          => 'included',
		Constants::META_CIRCUIT_EXCLUDED          => 'excluded',
		Constants::META_CIRCUIT_NOT_SUITABLE      => 'not_suitable',
		Constants::META_CIRCUIT_IMPORTANT_INFO    => 'important_info',
		Constants::META_CIRCUIT_WHAT_TO_BRING     => 'what_to_bring',
		Constants::META_CIRCUIT_NOT_ALLOWED       => 'not_allowed',
		Constants::META_CIRCUIT_KNOW_BEFORE_GO    => 'know_before_go',
		Constants::META_CIRCUIT_CANCELLATION      => 'cancellation',
		Constants::META_CIRCUIT_PRICE_TIERS       => 'price_tiers',
		Constants::META_CIRCUIT_VEHICLES          => 'vehicles',
		Constants::META_CIRCUIT_ITINERARY_DAYS    => 'itinerary_days',
	);
	
	foreach ( $meta_fields as $meta_key => $data_key ) {
		if ( isset( $circuit_data[ $data_key ] ) ) {
			$value = $circuit_data[ $data_key ];
			
			// Handle array fields
			if ( in_array( $meta_key, array(
				Constants::META_CIRCUIT_LANGUAGES,
				Constants::META_CIRCUIT_TAGS,
				Constants::META_CIRCUIT_PRICE_TIERS,
				Constants::META_CIRCUIT_VEHICLES,
				Constants::META_CIRCUIT_ITINERARY_DAYS,
			), true ) ) {
				update_post_meta( $post_id, $meta_key, is_array( $value ) ? $value : array() );
			} else {
				update_post_meta( $post_id, $meta_key, $value );
			}
		}
	}
	
	return $post_id;
}

/**
 * Insert image from URL
 */
function insert_image_from_url( string $image_url, int $post_id ): ?int {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
	$tmp = download_url( $image_url );
	
	if ( is_wp_error( $tmp ) ) {
		return null;
	}
	
	$file_array = array(
		'name'     => basename( $image_url ),
		'tmp_name' => $tmp,
	);
	
	$id = media_handle_sideload( $file_array, $post_id );
	
	if ( is_wp_error( $id ) ) {
		@unlink( $file_array['tmp_name'] );
		return null;
	}
	
	return $id;
}

/**
 * Insert image from local path
 */
function insert_image_from_path( string $image_path, int $post_id ): ?int {
	require_once( ABSPATH . 'wp-admin/includes/file.php' );
	require_once( ABSPATH . 'wp-admin/includes/media.php' );
	require_once( ABSPATH . 'wp-admin/includes/image.php' );
	
	if ( ! file_exists( $image_path ) ) {
		return null;
	}
	
	$file_array = array(
		'name'     => basename( $image_path ),
		'tmp_name' => $image_path,
	);
	
	$id = media_handle_sideload( $file_array, $post_id );
	
	return is_wp_error( $id ) ? null : $id;
}

// ============================================================================
// CIRCUIT DATA - 3 Circuits
// ============================================================================

$circuits_data = array(
	// Circuit 1: Marrakech 3-Day Merzouga Desert Experience
	array(
		'title'            => 'Marrakech: 3-Day Merzouga Desert Experience Camel & Meals Shared Group',
		'content'          => 'Travel from Marrakech to Merzouga on a breathtaking desert adventure through the High Atlas Mountains and Tizi n\'Tichka Pass. Discover the UNESCO-listed Aït Ben Haddou, Tinghir, and the impressive Todgha Gorges, visit traditional Berber villages, ride camels across the golden dunes of Erg Chebbi at sunset, and enjoy an authentic night in a Berber desert camp beneath the Sahara stars.',
		'excerpt'          => '3-day desert adventure from Marrakech to Merzouga. Visit Aït Ben Haddou, explore Todgha Gorges, ride camels at sunset, and spend a night in a Berber desert camp.',
		'status'           => 'publish',
		// 'thumbnail_url'    => 'https://example.com/merzouga-3day.jpg', // Add image URL here
		
		// Basic info
		'location'         => 'Marrakech',
		'duration_days'    => 3,
		'meeting_point'    => 'Marrakech hotels pickup',
		'difficulty'       => 'medium',
		
		// Languages
		'languages'        => array( 'english', 'french', 'spanish', 'portuguese' ),
		
		// Tags
		'tags'             => array( 'adventure', 'cultural', 'historical', 'nature', 'Desert' ),
		
		// Highlights
		'highlights'       => "See the impressive Atlas Mountains and take in panoramic views\nVisit the UNESCO Ait Ben Haddou and see the earthen clay architecture\nTake a guided tour of the Tinghir oasis and Todra gorges\nExperience a camel ride at sunset and witness a desert sunrise",
		
		// Pickup info
		'pickup_info'      => "Pickup is available from all Marrakech hotels. For guest houses and riads, the pickup will be from the closest point accessible by van. This location will be provided the day before the activity.",
		
		// Included/Excluded
		'included'         => "Hotel pickup and drop-off\nTransportation by air-conditioned minibus with luggage storage\nLocal guide in Tinghir and Merzouga\nPrivate room at Hotel Bougafer, or a similar 4-star hotel\nCamel trek in Erg Chebbi\nOvernight in a desert camp in Erg Chebbi (toilets outside and shower in Auberge)\nDinner and breakfast (vegetarian option available)\nSandboarding",
		'excluded'         => "Lunch and drinks\nGratuities for driver\nLocal guide at Ait Ben Haddou (€3)\nQuad bike (optional)",
		
		// Additional info
		'not_suitable'     => "People with back problems\nWheelchair users",
		'important_info'   => "Pick up start at 7:20\nAccess to swimming pool\nLast minutes booking contact us via WhatsApp\nLocal guide speaks English / Spanish / French\nThe sand boarding is available for customers\nPossibility to store luggage during the trip\nPrivate room / camp available duo the volume of the group",
		'what_to_bring'    => "Passport or ID card\nComfortable shoes\nSunglasses\nSun hat\nLong pants",
		'not_allowed'      => "Pets\nSmoking in the vehicle",
		'know_before_go'   => "Please note that lunch is not included on this tour & drinks 10-12€ cash\nPick up start at 7:20\nAccess to swimming pool\nLast minutes booking contact us via WhatsApp\nLocal guide speaks English / Spanish / French\nThe sand boarding is available for customers\nPossibility to store luggage during the trip\nPrivate room / camp available duo the volume of the group",
		'cancellation'     => "Free cancellation\nCancel up to 48 hours in advance for 80% refund",
		
		// Price tiers (adjust prices as needed)
		'price_tiers'      => array(
			array( 'min_persons' => 1, 'price' => 350 ),
			array( 'min_persons' => 2, 'price' => 300 ),
			array( 'min_persons' => 4, 'price' => 280 ),
			array( 'min_persons' => 6, 'price' => 250 ),
		),
		
		// Vehicles
		'vehicles'         => array(),
		
		// Itinerary days
		'itinerary_days'   => array(
			array(
				'day_title'     => 'Day 1: Marrakech - Kasbah Ait Benhaddou - Ouarzazate - Tinghir',
				'steps'         => array(
					array(
						'title'       => 'Depart from Marrakech',
						'description' => 'Begin with an early pickup from Marrakech. Travel through the Tizi Ntichka pass and the High Atlas Mountains, with some photo/rest stops along the way.',
					),
					array(
						'title'       => 'Explore Kasbah Ait Benhaddou',
						'description' => 'Make your first stop and explore the UNESCO World Heritage Site of Kasbah Ait Benhaddou, which served as a set for various Hollywood blockbusters.',
					),
					array(
						'title'       => 'Visit Ouarzazate and Kasbah Taourirte',
						'description' => 'Continue your journey to Ouarzazate, visit Kasbah Taourirte, and purchase lunch at a local café.',
					),
					array(
						'title'       => 'Stay Overnight in Tinghir',
						'description' => 'In the afternoon, drive to Tinghir via the Valley of the Roses before you reach your hotel located in Tinghir. Enjoy dinner and relax after a long day of sightseeing and travelling.',
					),
				),
				'accommodations' => 'Hotel, Tinghir',
				'meals'          => 'Dinner',
				'transportation' => 'Minibus',
			),
			array(
				'day_title'     => 'Day 2: Tinghir - Tinghir Oasis - Erfoud – Merzouga',
				'steps'         => array(
					array(
						'title'       => 'Travel to the Oasis of Tinghir',
						'description' => 'After breakfast at your hotel in Tinghir, drive along the "road of 1001 Kasbahs" to the Oasis of Tinghir. Discover the huge cliffs of the Todra River canyon, located nearby.',
					),
					array(
						'title'       => 'Explore the Date Markets in Erfoud',
						'description' => 'After a stop to purchase some lunch, continue the adventure with a visit to Erfoud, famous for its date market and as a historic starting point of ancient trading caravans crossing the Sahara desert to Tombouctou.',
					),
					array(
						'title'       => 'Embark on a Sunset Camel Ride at the Erg Chebbi Dunes',
						'description' => 'Continue to Merzouga and arrive at the spectacular Erg Chebbi dunes. Enjoy a serene camel ride towards the desert camp through the breathtaking dunes with the sunset on the horizon as a backdrop.',
					),
					array(
						'title'       => 'Enjoy Dinner and Music at your Desert Camp',
						'description' => 'As the sun sets, reach the desert camp where you will spend the evening. Savor a traditional Moroccan dinner served under the stars next to a campfire. Enjoy Berber music with the local hosts playing drums and relax for the evening.',
					),
				),
				'accommodations' => 'Tent, Erg Chebbi',
				'meals'          => 'Breakfast and Dinner',
				'transportation' => 'Minibus and Camel',
			),
			array(
				'day_title'     => 'Day 3: Merzouga - Atlas Mountains - Marrakech',
				'steps'         => array(
					array(
						'title'       => 'Breakfast and Return to Marrakech',
						'description' => 'Rise early and enjoy a camel ride back to the main road to begin your journey back to Marrakech, with a stop to purchase lunch along the way.',
					),
				),
				'accommodations' => '',
				'meals'          => 'Breakfast',
				'transportation' => 'Minibus',
			),
		),
	),
	
	// Circuit 2: Merzouga Overnight Camel Trekking Luxury Desert Camp
	array(
		'title'            => 'Merzouga Overnight Camel Trekking Luxury Desert Camp',
		'content'          => 'Come for this overnight tour of camel trekking and bivouac tent stay from Merzouga, a small Moroccan town in the Sahara Desert near the Algerian border. Enjoy a camel ride for one and a half hours in the desert, witnessing the beautiful sunset, and spend the night with traditional Berber drums, music and a campfire under starry skies. Merzouga luxury desert camps also offers a spectacular nomadic experience. It is especially unique because of the \'KHaimas\', which are traditional tents made of camel skin typically used by nomadic people.',
		'excerpt'          => 'Overnight camel trekking tour in Merzouga. Enjoy a 1.5-hour camel ride at sunset, spend the night in a luxury desert camp with Berber music under the stars.',
		'status'           => 'publish',
		// 'thumbnail_url'    => 'https://example.com/merzouga-overnight.jpg', // Add image URL here
		
		// Basic info
		'location'         => 'Merzouga',
		'duration_days'    => 1,
		'meeting_point'    => 'Les Pyramides Merzouga Campground, Ksar merzouga, Merzouga, Morocco',
		'difficulty'       => 'easy',
		
		// Languages
		'languages'        => array( 'arabic', 'english', 'french', 'german', 'italian', 'spanish' ),
		
		// Tags
		'tags'             => array( 'adventure', 'nature', 'Desert', 'cultural' ),
		
		// Highlights
		'highlights'       => "Explore the desert of Merzouga on a camel\nWitness the beautiful sun setting over the dunes\nSpend a night like the desert people, under a sky filled with stars",
		
		// Pickup info
		'pickup_info'      => "Meeting point at Les Pyramides hotel Merzouga to parking your car, have mint tea before starting camel trekking to the camp",
		
		// Included/Excluded
		'included'         => "Camel ride\nTent\nDinner\nBreakfast\nMoroccan guide",
		'excluded'         => "Lunch\nDrinks",
		
		// Additional info
		'not_suitable'     => "",
		'important_info'   => "",
		'what_to_bring'    => "No needs",
		'not_allowed'      => "",
		'know_before_go'   => "Meeting point at Les Pyramides hotel Merzouga to parking your car, have mint tea before starting camel trekking to the camp",
		'cancellation'     => "For cancellations up to 2 days before the tour - Refund of 80% of the tour price.",
		
		// Price tiers (contact for pricing)
		'price_tiers'      => array(
			array( 'min_persons' => 1, 'price' => 'Ask walid/person' ),
		),
		
		// Vehicles
		'vehicles'         => array(),
		
		// Itinerary days
		'itinerary_days'   => array(
			array(
				'day_title'     => 'Desert Day Tour in Merzouga',
				'steps'         => array(
					array(
						'title'       => 'Meet the tour guide',
						'description' => 'Meet the tour guide, who will pick you up from the Merzouga guesthouse around 5:30 pm. Have mint tea before starting.',
					),
					array(
						'title'       => 'Camel ride in the desert',
						'description' => 'Enjoy the 1 hour and 30-minutes camel ride in the desert. The camels will be packed with food, drinking water and blankets.',
					),
					array(
						'title'       => 'Sunset at the dunes',
						'description' => 'Arrive at the camp and walk to the high dunes to see the sunset.',
					),
					array(
						'title'       => 'Evening at the camp',
						'description' => 'Spend the night in an equipped camp with music played on traditional Berber drums in the middle of the Sahara. See the night sky filled with stars that sparkle and shine with intensity. Have a wonderful dinner around a campfire.',
					),
					array(
						'title'       => 'Next day: Return to Merzouga',
						'description' => 'Ride the camels back to the Merzouga guesthouse in the morning.',
					),
				),
				'accommodations' => 'Luxury Desert Camp',
				'meals'          => 'Dinner and Breakfast',
				'transportation' => 'Camel',
			),
		),
	),
	
	// Circuit 3: 2-Day Private Desert Tour Marrakech to Zagora
	array(
		'title'            => '2-Day Private Desert Tour Marrakech to Zagora, Camel Trek & Lux Camp',
		'content'          => 'This 2-day private tour from Marrakech to the Zagora Sahara Desert offers cultural immersion and stunning landscapes. Journey through the High Atlas Mountains and Tizi-n-Tichka pass, visit the UNESCO-listed Ait Benhaddou, enjoy a camel trek in the Zagora Desert and a night under the stars. Highlights include scenic Atlas Mountains drives, exploring ancient kasbahs, and a night in a luxury desert camp under a canopy of stars. Experience a sunset camel-trek, interact with local nomads and marvel at desert expanses. Ideal for couples, families, and friends, the tour includes private transport with a professional multilingual driver, dinner and breakfast in a 5-star luxury camp and a camel trek, ensuring a hassle-free experience.',
		'excerpt'          => '2-day private desert tour from Marrakech to Zagora. Visit Ait Benhaddou, enjoy a sunset camel trek, and spend a night in a 5-star luxury desert camp.',
		'status'           => 'publish',
		// 'thumbnail_url'    => 'https://example.com/zagora-2day.jpg', // Add image URL here
		
		// Basic info
		'location'         => 'Marrakech',
		'duration_days'    => 2,
		'meeting_point'    => 'Hotel pickup in Marrakech',
		'difficulty'       => 'easy',
		
		// Languages
		'languages'        => array( 'english', 'spanish', 'arabic', 'french' ),
		
		// Tags
		'tags'             => array( 'adventure', 'cultural', 'historical', 'Desert', 'photography' ),
		
		// Highlights
		'highlights'       => "Thrilling drive through the High Atlas Mountains via Tizi-n-Tichka pass with panoramic views\nVisit UNESCO-listed Ait Benhaddou, an ancient fortress known for its exotic allure and cinematic significance\nSpend a night in a 5-star luxury desert camp in Zagora, with comfort and starlit tranquility\nSunset camel trek through the Zagora Desert dunes, experiencing traditional nomadic life\nInteract with local nomads, gaining insights into their timeless lifestyle\nPrivate, air-conditioned transportation with a multilingual professional driver\nPerfect for couples, families, and friends seeking a memorable experience",
		
		// Pickup info
		'pickup_info'      => "Round-trip transfers to and from your hotel in Marrakech",
		
		// Included/Excluded
		'included'         => "English / Spanish / Arabic / French-speaking guide\nEnglish-speaking professional driver/guide\nGuided tour inside the attraction\nProfessional guide\nBreakfast\nDinner\nRound-trip transfers to and from your hotel\nTransport\n1-night accommodation at a 5-star luxury desert camp\nInsurance provided by the operator",
		'excluded'         => "Meals and beverages\nTips and gratuities",
		
		// Additional info
		'not_suitable'     => "",
		'important_info'   => "",
		'what_to_bring'    => "",
		'not_allowed'      => "",
		'know_before_go'   => "Package includes: Book now for tomorrow, Free cancellation (24 hours notice), 24-hour confirmation, Group size: 2 to 100",
		'cancellation'     => "Free cancellation (24 hours notice)",
		
		// Price tiers (adjust prices as needed)
		'price_tiers'      => array(
			array( 'min_persons' => 1, 'price' => 600 ),
			array( 'min_persons' => 2, 'price' => 500 ),
			array( 'min_persons' => 4, 'price' => 450 ),
			array( 'min_persons' => 6, 'price' => 400 ),
		),
		
		// Vehicles
		'vehicles'         => array(),
		
		// Itinerary days
		'itinerary_days'   => array(
			array(
				'day_title'     => 'Day 1: Marrakech to Zagora',
				'steps'         => array(
					array(
						'title'       => '07:30 Departure',
						'description' => 'Pickup from your hotel in Marrakech and begin the journey',
					),
					array(
						'title'       => 'Ait Ben Haddou - Guided tour',
						'description' => 'Pause to discover the UNESCO-listed Ait Benhaddou, a living testament to Morocco\'s rich history. Explore the labyrinthine pathways of this ancient fortress and enjoy lunch in this historic setting.',
					),
					array(
						'title'       => '17:30 Accommodation',
						'description' => 'Arrive in the tranquil Zagora desert, enjoy a breathtaking sunset camel trek, and settle into your 5-star luxury desert camp for a magical night under the starlit sky.',
					),
				),
				'accommodations' => 'Zagora Luxury Camp or similar',
				'meals'          => 'Dinner',
				'transportation' => 'Private air-conditioned vehicle',
			),
			array(
				'day_title'     => 'Day 2: Zagora to Marrakech',
				'steps'         => array(
					array(
						'title'       => 'Sunrise over the dunes',
						'description' => 'Rise with the Sahara sun for a stunning sunrise over the dunes.',
					),
					array(
						'title'       => 'Return journey by car (6 hours 30 minutes)',
						'description' => 'Enjoy a scenic drive back to Marrakech, passing remote villages, the Draa Valley, and Ouarzazate\'s famous Atlas Studios.',
					),
					array(
						'title'       => '19:00 Return',
						'description' => 'Return to hotel/personal address in Marrakech',
					),
				),
				'accommodations' => '',
				'meals'          => 'Breakfast',
				'transportation' => 'Private air-conditioned vehicle',
			),
		),
	),
);

// ============================================================================
// EXECUTION - Don't modify below unless you know what you're doing
// ============================================================================

echo "Starting circuit insertion...\n\n";

$results = array(
	'success' => array(),
	'errors'  => array(),
);

foreach ( $circuits_data as $index => $circuit_data ) {
	$circuit_number = $index + 1;
	echo "Processing Circuit {$circuit_number}: {$circuit_data['title']}...\n";
	
	$result = insert_circuit( $circuit_data );
	
	if ( is_wp_error( $result ) ) {
		$error_msg = $result->get_error_message();
		echo "  ❌ ERROR: {$error_msg}\n";
		$results['errors'][] = array(
			'circuit' => $circuit_data['title'],
			'error'   => $error_msg,
		);
	} else {
		$edit_link = admin_url( "post.php?post={$result}&action=edit" );
		echo "  ✅ SUCCESS: Circuit created with ID {$result}\n";
		echo "  📝 Edit: {$edit_link}\n";
		$results['success'][] = array(
			'circuit'   => $circuit_data['title'],
			'post_id'   => $result,
			'edit_link' => $edit_link,
		);
	}
	
	echo "\n";
}

// Summary
echo "\n" . str_repeat( '=', 60 ) . "\n";
echo "SUMMARY\n";
echo str_repeat( '=', 60 ) . "\n";
echo "✅ Successfully inserted: " . count( $results['success'] ) . " circuit(s)\n";
echo "❌ Errors: " . count( $results['errors'] ) . " circuit(s)\n\n";

if ( ! empty( $results['success'] ) ) {
	echo "Successfully created circuits:\n";
	foreach ( $results['success'] as $success ) {
		echo "  - {$success['circuit']} (ID: {$success['post_id']})\n";
	}
	echo "\n";
}

if ( ! empty( $results['errors'] ) ) {
	echo "Errors encountered:\n";
	foreach ( $results['errors'] as $error ) {
		echo "  - {$error['circuit']}: {$error['error']}\n";
	}
	echo "\n";
}

echo "Done!\n";
