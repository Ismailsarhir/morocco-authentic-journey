<?php
/**
 * Classe abstraite de base pour les Custom Post Types
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\CPT;

/**
 * Classe abstraite pour tous les Custom Post Types
 * Fournit les méthodes communes à tous les CPT
 */
abstract class PostType {
	
	/**
	 * Slug du post type
	 * 
	 * @var string
	 */
	protected string $post_type;
	
	/**
	 * Labels du post type
	 * 
	 * @var array
	 */
	protected array $labels;
	
	/**
	 * Arguments d'enregistrement du post type
	 * 
	 * @var array
	 */
	protected array $args;
	
	/**
	 * Constructeur
	 * 
	 * @param string $post_type Slug du post type
	 */
	public function __construct( string $post_type ) {
		$this->post_type = $post_type;
		$this->labels    = $this->get_labels();
		$this->args      = $this->get_args();
	}
	
	/**
	 * Enregistre le post type et ses hooks
	 * 
	 * @return void
	 */
	public function register(): void {
		$this->register_post_type();
		$this->register_taxonomies();
		$this->register_meta_fields();
		$this->register_meta_boxes();
		$this->register_save_hooks();
	}
	
	/**
	 * Enregistre le post type WordPress
	 * 
	 * @return void
	 */
	protected function register_post_type(): void {
		\register_post_type(
			$this->post_type,
			array_merge(
				$this->args,
				[ 'labels' => $this->labels ]
			)
		);
	}
	
	/**
	 * Enregistre les taxonomies associées
	 * À surcharger dans les classes enfants si nécessaire
	 * 
	 * @return void
	 */
	protected function register_taxonomies(): void {
		// À implémenter dans les classes enfants
	}
	
	/**
	 * Enregistre les champs meta avec register_post_meta
	 * À surcharger dans les classes enfants
	 * 
	 * @return void
	 */
	protected function register_meta_fields(): void {
		// À implémenter dans les classes enfants
	}
	
	/**
	 * Enregistre les meta boxes
	 * À surcharger dans les classes enfants
	 * 
	 * @return void
	 */
	protected function register_meta_boxes(): void {
		// À implémenter dans les classes enfants
	}
	
	/**
	 * Enregistre les hooks de sauvegarde
	 * 
	 * @return void
	 */
	protected function register_save_hooks(): void {
		\add_action( 'save_post_' . $this->post_type, [ $this, 'save_meta' ], 10, 2 );
	}
	
	/**
	 * Sauvegarde les meta données
	 * À surcharger dans les classes enfants
	 * 
	 * @param int     $post_id ID du post
	 * @param WP_Post $post    Objet post
	 * @return void
	 */
	public function save_meta( int $post_id, $post ): void {
		// Vérifications de sécurité
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Vérifier que $post est valide
		if ( ! $post || ! ( $post instanceof \WP_Post ) ) {
			return;
		}
		
		if ( $post->post_type !== $this->post_type ) {
			return;
		}
		
		// À implémenter dans les classes enfants
	}
	
	/**
	 * Retourne les labels du post type
	 * À surcharger dans les classes enfants
	 * 
	 * @return array
	 */
	abstract protected function get_labels(): array;
	
	/**
	 * Retourne les arguments d'enregistrement du post type
	 * À surcharger dans les classes enfants
	 * 
	 * @return array
	 */
	abstract protected function get_args(): array;
}

