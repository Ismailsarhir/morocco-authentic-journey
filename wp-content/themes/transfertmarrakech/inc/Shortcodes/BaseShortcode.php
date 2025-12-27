<?php
/**
 * Classe de base pour les shortcodes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Shortcodes;

use TM\Repository\PostRepository;
use TM\Template\Renderer;

/**
 * Classe abstraite de base pour tous les shortcodes
 */
abstract class BaseShortcode {
	
	/**
	 * Repository partagé
	 * 
	 * @var PostRepository|null
	 */
	protected static ?PostRepository $repository = null;
	
	/**
	 * Renderer partagé
	 * 
	 * @var Renderer|null
	 */
	protected static ?Renderer $renderer = null;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		// Initialise les instances partagées si nécessaire
		if ( self::$repository === null ) {
			// Utilise l'instance partagée du repository (optimisation)
			self::$repository = PostRepository::get_instance();
		}
		
		if ( self::$renderer === null ) {
			self::$renderer = new Renderer();
		}
	}
	
	/**
	 * Construit une meta_query à partir de critères
	 * 
	 * @param array $criteria Critères de recherche (key => value)
	 * @return array Meta query
	 */
	protected function build_meta_query( array $criteria ): array {
		if ( empty( $criteria ) ) {
			return [];
		}
		
		$meta_query = [];
		
		foreach ( $criteria as $key => $value ) {
			if ( ! empty( $value ) ) {
				$meta_query[] = [
					'key'     => $key,
					'value'   => \sanitize_text_field( $value ),
					'compare' => '=',
				];
			}
		}
		
		if ( count( $meta_query ) > 1 ) {
			$meta_query['relation'] = 'AND';
		}
		
		return $meta_query;
	}
	
	/**
	 * Enregistre le shortcode
	 * 
	 * @return void
	 */
	abstract public function register(): void;
	
	/**
	 * Affiche le shortcode
	 * 
	 * @param array  $atts    Attributs du shortcode
	 * @param string $content Contenu du shortcode
	 * @return string
	 */
	abstract public function render( array $atts = [], string $content = '' ): string;
}

