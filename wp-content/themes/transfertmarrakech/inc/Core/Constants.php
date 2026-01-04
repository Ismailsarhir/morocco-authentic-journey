<?php
/**
 * Constants class for theme-wide constants
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

/**
 * Classe contenant les constantes du thème
 */
class Constants {
	
	/**
	 * Post Types
	 */
	public const POST_TYPE_TRANSFER = 'transferts';
	public const POST_TYPE_TOUR = 'tours';
	public const POST_TYPE_VEHICLE = 'vehicules';
	
	/**
	 * Taxonomies
	 */
	public const TAXONOMY_DESTINATION = 'destination';
	
	/**
	 * Meta Keys - Transfers
	 */
	public const META_TRANSFER_TYPE = 'tm_transfer_type';
	public const META_TRANSFER_VEHICLE = 'tm_vehicle';
	public const META_TRANSFER_PRICE = 'tm_price';
	public const META_TRANSFER_PICKUP = 'tm_pickup';
	public const META_TRANSFER_DROPOFF = 'tm_dropoff';
	public const META_TRANSFER_DURATION_ESTIMATE = 'tm_duration_estimate';
	public const META_TRANSFER_DESCRIPTION = 'tm_description';
	
	/**
	 * Meta Keys - Tours
	 */
	public const META_TOUR_LOCATION = 'tm_location';
	public const META_TOUR_DURATION = 'tm_duration';
	public const META_TOUR_DURATION_UNIT = 'tm_duration_unit';
	public const META_TOUR_DURATION_MINUTES = 'tm_duration_minutes';
	public const META_TOUR_NIGHTS = 'tm_nights';
	public const META_TOUR_MEALS = 'tm_meals';
	public const META_TOUR_PRICE = 'tm_price';
	public const META_TOUR_VEHICLES = 'tm_vehicles';
	public const META_TOUR_HIGHLIGHTS = 'tm_highlights';
	public const META_TOUR_MEETING_POINT = 'tm_meeting_point';
	public const META_TOUR_TYPE = 'tm_tour_type';
	public const META_TOUR_DIFFICULTY = 'tm_tour_difficulty';
	public const META_TOUR_LANGUAGES = 'tm_tour_languages';
	public const META_TOUR_TAGS = 'tm_tour_tags';
	public const META_TOUR_ITINERARY_TITLE = 'tm_tour_itinerary_title';
	public const META_TOUR_ITINERARY = 'tm_tour_itinerary';
	public const META_TOUR_INCLUDED = 'tm_tour_included';
	public const META_TOUR_EXCLUDED = 'tm_tour_excluded';
	public const META_TOUR_CANCELLATION = 'tm_tour_cancellation';
	public const META_TOUR_PRICE_TIERS = 'tm_tour_price_tiers';
	
	/**
	 * Meta Keys - Vehicles
	 */
	public const META_VEHICLE_TYPE = 'tm_vehicle_type';
	public const META_VEHICLE_SEATS = 'tm_seats';
	public const META_VEHICLE_BAGGAGE_CAPACITY = 'tm_baggage_capacity';
	public const META_VEHICLE_GALLERY = 'tm_gallery';
	public const META_VEHICLE_AVAILABILITY = 'tm_availability';
	public const META_VEHICLE_DAILY_PRICE = 'tm_daily_price';
	
	/**
	 * Options
	 */
	public const OPTION_WHATSAPP_PHONE = 'tm_whatsapp_phone';
	public const OPTION_WHATSAPP_PHONE_DEFAULT = '2126xxxxxxxx';
	
	/**
	 * Cached tour type labels
	 * 
	 * @var array|null
	 */
	private static $tour_type_labels = null;
	
	/**
	 * Get tour type labels (cached globally)
	 * 
	 * @return array Associative array of tour type labels
	 */
	public static function get_tour_type_labels(): array {
		if ( self::$tour_type_labels === null ) {
			self::$tour_type_labels = [
				'group'   => esc_html__( 'Group Tour', 'transfertmarrakech' ),
				'private' => esc_html__( 'Private Tour', 'transfertmarrakech' ),
				'shared'  => esc_html__( 'Shared Group', 'transfertmarrakech' ),
			];
		}
		return self::$tour_type_labels;
	}
}

