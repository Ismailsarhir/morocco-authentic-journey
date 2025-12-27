<?php
/**
 * Hero class for rendering the hero section
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Utils\HeroHelper;
use TM\Template\Renderer;

/**
 * Classe pour gérer le rendu du Hero
 */
class Hero {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var Hero|null
	 */
	private static ?Hero $instance = null;
	
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
	 * @return Hero
	 */
	public static function get_instance(): Hero {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Rend le Hero complet
	 * 
	 * @return void
	 */
	public function render(): void {
		$hero_post = HeroHelper::get_hero_post();
		
		if ( ! $hero_post ) {
			return;
		}
		
		$this->renderer->render( 'hero', [ 'hero_post' => $hero_post ] );
	}
	
	/**
	 * Vérifie si un Hero doit être affiché
	 * 
	 * @return bool
	 */
	public function has_hero(): bool {
		return HeroHelper::get_hero_post() !== null;
	}
}

