<?php
/**
 * Helper class for header functionality
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Utils;

/**
 * Helper class pour la gestion du header
 */
class HeaderHelper {
	
	/**
	 * Récupère les destinations pour le menu
	 * 
	 * @return array
	 */
	public static function get_destinations(): array {
		return [
			[
				'icon' => 'icone-afrique.webp',
				'label' => __( 'Afrique', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-amerique-centrale.webp',
				'label' => __( 'Amérique centrale & Caraïbes', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-amerique-nord.webp',
				'label' => __( 'Amérique du Nord', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-amerique-sud.webp',
				'label' => __( 'Amérique du Sud', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-asie.webp',
				'label' => __( 'Asie', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-europe.webp',
				'label' => __( 'Europe & Méditerranée', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'icon' => 'icone-pacifique-sud.webp',
				'label' => __( 'Pacifique Sud', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Voir toutes nos destinations', 'transfertmarrakech' ),
				'url' => '#',
				'class' => 'cta primary',
			],
		];
	}
	
	/**
	 * Récupère les liens du menu principal
	 * 
	 * @return array
	 */
	public static function get_main_menu_items(): array {
		return [
			[
				'label' => __( 'Tours vedettes', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Espace voyageur', 'transfertmarrakech' ),
				'url' => '#',
				'class' => 'has-border',
			],
		];
	}
	
	/**
	 * Récupère les liens du menu mobile
	 * 
	 * @return array
	 */
	public static function get_mobile_menu_items(): array {
		return [
			[
				'label' => __( 'Tours vedettes', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Espace voyageur', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Brochures', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Actualités & récits', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Agences partenaires «Club Excellence»', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'À propos', 'transfertmarrakech' ),
				'url' => '#',
			],
			[
				'label' => __( 'Nous joindre', 'transfertmarrakech' ),
				'url' => '#',
			],
		];
	}
	
	/**
	 * Récupère l'URL du logo
	 * 
	 * @return string
	 */
	public static function get_logo_url(): string {
		return \get_template_directory_uri() . '/assets/images/logo.png';
	}
	
	/**
	 * Récupère l'URL d'une icône de destination
	 * 
	 * @param string $icon_name Nom du fichier d'icône
	 * @return string
	 */
	public static function get_destination_icon_url( string $icon_name ): string {
		return \get_template_directory_uri() . '/assets/images/' . $icon_name;
	}
	
	/**
	 * Récupère l'URL de l'icône de recherche
	 * 
	 * @return string
	 */
	public static function get_search_icon_url(): string {
		return \get_template_directory_uri() . '/assets/images/icons/search.svg';
	}
	
	/**
	 * Récupère l'URL du portail des agents (WhatsApp)
	 * 
	 * @return string
	 */
	public static function get_agent_portal_url(): string {
		// Récupère le numéro WhatsApp depuis les options
		$phone_number = \TM\Utils\MetaHelper::get_whatsapp_phone();
		
		// Format WhatsApp: https://wa.me/[country_code][phone_number] (sans espaces, sans +, sans tirets)
		return 'https://wa.me/' . $phone_number;
	}
}
