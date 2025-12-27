<?php
/**
 * Featured Text class for rendering the featured text section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;

/**
 * Classe pour gérer le rendu de la section Featured Text
 */
class FeaturedText {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var FeaturedText|null
	 */
	private static ?FeaturedText $instance = null;
	
	/**
	 * Renderer pour les templates
	 * 
	 * @var Renderer
	 */
	private Renderer $renderer;
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		$this->renderer = new Renderer();
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return FeaturedText
	 */
	public static function get_instance(): FeaturedText {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère le texte principal depuis les options du thème
	 * 
	 * @return string
	 */
	private function get_featured_text(): string {
		$default_text = __( 
			'Transfert Marrakech est bien plus qu\'un simple voyagiste, mais un pionnier du voyage au Maroc fort de ses 10 ans d\'expérience.', 
			'transfertmarrakech' 
		);
		
		return \get_option( 'tm_featured_text', $default_text );
	}
	
	/**
	 * Récupère le surtexte depuis les options du thème
	 * 
	 * @return string
	 */
	private function get_surtext(): string {
		$default_surtext = __( 'Depuis 2015', 'transfertmarrakech' );
		
		return \get_option( 'tm_featured_surtext', $default_surtext );
	}
	
	/**
	 * Rend la section Featured Text
	 * 
	 * @return void
	 */
	public function render(): void {
		$featured_text = $this->get_featured_text();
		$surtext = $this->get_surtext();
		
		if ( empty( $featured_text ) ) {
			return;
		}
		
		$this->renderer->render( 'featured-text', [
			'featured_text' => $featured_text,
			'surtext'       => $surtext,
		] );
	}
	
	/**
	 * Vérifie si la section doit être affichée
	 * 
	 * @return bool
	 */
	public function has_content(): bool {
		$featured_text = $this->get_featured_text();
		return ! empty( $featured_text );
	}
}

