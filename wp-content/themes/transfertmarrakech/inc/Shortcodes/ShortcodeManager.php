<?php
/**
 * Gestionnaire de shortcodes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Shortcodes;

use TM\Shortcodes\VehicleShortcode;
use TM\Shortcodes\TourShortcode;
use TM\Shortcodes\TransferShortcode;

/**
 * Classe pour gérer l'enregistrement de tous les shortcodes
 */
class ShortcodeManager {
	
	/**
	 * Enregistre tous les shortcodes
	 * 
	 * @return void
	 */
	public function register_all(): void {
		$vehicle_shortcode = new VehicleShortcode();
		$vehicle_shortcode->register();
		
		$tour_shortcode = new TourShortcode();
		$tour_shortcode->register();
		
		$transfer_shortcode = new TransferShortcode();
		$transfer_shortcode->register();
	}
}

