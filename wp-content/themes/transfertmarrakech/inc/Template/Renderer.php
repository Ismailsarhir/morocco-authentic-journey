<?php
/**
 * Helper pour rendre les template parts
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Template;

/**
 * Classe pour rendre les templates
 */
class Renderer {
	
	/**
	 * Chemin de base des templates
	 * 
	 * @var string
	 */
	protected string $template_path;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		$this->template_path = \get_template_directory() . '/template-parts/';
	}
	
	/**
	 * Rend un template part
	 * 
	 * @param string $template_name Nom du template (sans extension)
	 * @param array  $data          Données à passer au template
	 * @return void
	 */
	public function render( string $template_name, array $data = [] ): void {
		$template_file = $this->template_path . $template_name . '.php';
		
		if ( ! \file_exists( $template_file ) ) {
			// Fallback vers un template générique
			$template_file = $this->template_path . 'loop-generic.php';
			
			if ( ! \file_exists( $template_file ) ) {
				return;
			}
		}
		
		// Extrait les données pour les rendre accessibles dans le template
		\extract( $data, EXTR_SKIP );
		
		// Inclut le template
		include $template_file;
	}
	
	/**
	 * Rend un template part et retourne le résultat
	 * 
	 * @param string $template_name Nom du template (sans extension)
	 * @param array  $data          Données à passer au template
	 * @return string
	 */
	public function render_string( string $template_name, array $data = [] ): string {
		\ob_start();
		$this->render( $template_name, $data );
		return \ob_get_clean();
	}
}

