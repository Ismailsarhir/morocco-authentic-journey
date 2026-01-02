<?php
/**
 * Base abstract class for List classes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

use TM\Template\Renderer;
use TM\Repository\PostRepository;

/**
 * Classe abstraite de base pour les listes
 */
abstract class BaseList {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var static|null
	 */
	protected static $instance = null;
	
	/**
	 * Renderer pour les templates
	 * 
	 * @var Renderer
	 */
	protected Renderer $renderer;
	
	/**
	 * Repository pour récupérer les posts
	 * 
	 * @var PostRepository
	 */
	protected PostRepository $repository;
	
	/**
	 * Nombre maximum d'éléments à afficher
	 * Doit être défini dans les classes enfants
	 * 
	 * @var int
	 */
	protected const MAX_ITEMS = 6;
	
	/**
	 * Nom du template part à utiliser
	 * Doit être défini dans les classes enfants
	 * 
	 * @var string
	 */
	protected const TEMPLATE_NAME = '';
	
	/**
	 * Multiplicateur pour récupérer plus d'éléments (pour filtrer ceux sans images)
	 * 
	 * @var int
	 */
	protected const REQUEST_MULTIPLIER = 1;
	
	/**
	 * Constructeur protégé (Singleton)
	 */
	protected function __construct() {
		$this->renderer = new Renderer();
		$this->repository = PostRepository::get_instance();
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return static
	 */
	public static function get_instance() {
		if ( is_null( static::$instance ) ) {
			static::$instance = new static();
		}
		return static::$instance;
	}
	
	/**
	 * Récupère les éléments pour la liste
	 * 
	 * @param int $limit Nombre d'éléments à récupérer
	 * @return array
	 */
	abstract protected function get_featured_items( int $limit ): array;
	
	/**
	 * Rend la liste des éléments
	 * 
	 * @return void
	 */
	public function render(): void {
		$items = $this->get_featured_items( static::MAX_ITEMS );
		
		if ( empty( $items ) ) {
			return;
		}
		
		$this->renderer->render( static::TEMPLATE_NAME, [
			$this->get_items_key() => $items,
		] );
	}
	
	/**
	 * Vérifie si des éléments doivent être affichés
	 * 
	 * @return bool
	 */
	public function has_items(): bool {
		$items = $this->get_featured_items( 1 );
		return ! empty( $items );
	}
	
	/**
	 * Retourne la clé pour les items dans le template
	 * 
	 * @return string
	 */
	abstract protected function get_items_key(): string;
}

