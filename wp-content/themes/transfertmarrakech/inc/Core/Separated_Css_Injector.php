<?php
/**
 * Classe pour injecter conditionnellement les fichiers CSS séparés
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

/**
 * Classe pour gérer l'injection conditionnelle des fichiers CSS
 */
class Separated_Css_Injector {

	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var Separated_Css_Injector|null
	 */
	private static ?Separated_Css_Injector $instance = null;

	/**
	 * Tableau des fichiers CSS avec leurs conditions
	 * 
	 * @var array
	 */
	private array $css_files = [];

	/**
	 * Chemin de base vers les fichiers CSS
	 * 
	 * @var string
	 */
	private string $css_path;

	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return Separated_Css_Injector
	 */
	public static function instance(): Separated_Css_Injector {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}

	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		$this->css_path = TM_THEME_URI . '/assets/css/';

		$this->css_files = [
			'home' => [
				'condition' => function() {
					return \is_home() || \is_front_page();
				},
				'file_name' => 'home',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'single-tours' => [
				'condition' => function() {
					return \is_singular( 'tours' );
				},
				'file_name' => 'single-tours',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'single-transferts' => [
				'condition' => function() {
					return \is_singular( 'transferts' );
				},
				'file_name' => 'single-transfers',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'archive-tours' => [
				'condition' => function() {
					return \is_post_type_archive( 'tours' ) || ( \is_archive() && \get_post_type() === 'tours' );
				},
				'file_name' => 'archive-tours',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'archive-transferts' => [
				'condition' => function() {
					return \is_post_type_archive( 'transferts' ) || ( \is_archive() && \get_post_type() === 'transferts' );
				},
				'file_name' => 'archive-transferts',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'page-404' => [
				'condition' => function() {
					return \is_404();
				},
				'file_name' => 'global',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
			'page' => [
				'condition' => function() {
					return \is_page();
				},
				'file_name' => 'global',
				'dependencies' => [ 'global_css' ],
				'dequeues' => [],
			],
		];

		\add_action( 'wp_enqueue_scripts', [ $this, 'inject_css_files' ], 999 );
	}

	/**
	 * Injecte le fichier CSS global
	 * 
	 * @return void
	 */
	private function inject_global_file(): void {
		\wp_enqueue_style( 
			'global_css', 
			$this->get_file_path( 'global' ),
			[],
			TM_VERSION
		);
	}

	/**
	 * Construit le chemin vers un fichier CSS
	 * 
	 * @param string $file_name Nom du fichier (sans extension)
	 * @return string Chemin complet vers le fichier CSS
	 */
	private function get_file_path( string $file_name ): string {
		// Si vous avez besoin de versions mobiles, décommentez et adaptez :
		// if ( wp_is_mobile() ) {
		//     $file_name .= '_mobile';
		// }

		return $this->css_path . $file_name . '.css';
	}

	/**
	 * Injecte les fichiers CSS conditionnellement
	 * 
	 * @return void
	 */
	public function inject_css_files(): void {
		$this->inject_global_file();

		foreach ( $this->css_files as $handle => $css_file_info ) {
			if ( ! isset( $css_file_info['condition'] ) ) {
				continue;
			}

			if ( ! isset( $css_file_info['file_name'] ) ) {
				continue;
			}

			if ( ! \call_user_func( $css_file_info['condition'] ) ) {
				continue;
			}

			// Décharge les styles spécifiés
			$dequeues = $css_file_info['dequeues'] ?? [];
			foreach ( $dequeues as $dequeue ) {
				\wp_dequeue_style( $dequeue );
			}

			// Récupère les dépendances
			$dependencies = $css_file_info['dependencies'] ?? [];

			// Enqueue le fichier CSS
			\wp_enqueue_style(
				$handle,
				$this->get_file_path( $css_file_info['file_name'] ),
				$dependencies,
				TM_VERSION
			);

			// Arrête après avoir trouvé la première condition vraie
			break;
		}
	}
}