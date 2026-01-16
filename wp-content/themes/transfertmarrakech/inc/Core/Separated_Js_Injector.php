<?php
/**
 * Classe pour injecter conditionnellement les fichiers JavaScript séparés
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

/**
 * Classe pour gérer l'injection conditionnelle des fichiers JavaScript
 */
class Separated_Js_Injector {

	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var Separated_Js_Injector|null
	 */
	private static ?Separated_Js_Injector $instance = null;

	/**
	 * Tableau des fichiers JS avec leurs conditions
	 * 
	 * @var array
	 */
	private array $js_files = [];

	/**
	 * Chemin de base vers les fichiers JS
	 * 
	 * @var string
	 */
	private string $js_path;

	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return Separated_Js_Injector
	 */
	public static function instance(): Separated_Js_Injector {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}

	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		$this->js_path = TM_THEME_URI . '/assets/js/';

		$this->js_files = [
			'home' => [
				'condition' => function() {
					return \is_home() || \is_front_page();
				},
				'file_name' => 'home',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'single-tours' => [
				'condition' => function() {
					return \is_singular( 'tours' );
				},
				'file_name' => 'singleProduct',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'single-circuits' => [
				'condition' => function() {
					return \is_singular( 'circuits' );
				},
				'file_name' => 'singleProduct',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'single-transferts' => [
				'condition' => function() {
					return \is_singular( 'transferts' );
				},
				'file_name' => 'singleTransfers',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'page-404' => [
				'condition' => function() {
					return \is_404();
				},
				'file_name' => 'default',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'contact' => [
				'condition' => function() {
					return \is_page( 'contact' );
				},
				'file_name' => 'components/contact-form',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
				'localize' => function() {
					return [
						'object_name' => 'tmContactForm',
						'data' => [
							'ajaxUrl' => \admin_url( 'admin-ajax.php' ),
							'submitText' => \__( 'Submit', 'transfertmarrakech' ),
							'sendingText' => \__( 'Sending...', 'transfertmarrakech' ),
							'errorText' => \__( 'An error occurred. Please try again later.', 'transfertmarrakech' ),
						],
					];
				},
			],
			'page' => [
				'condition' => function() {
					return \is_page();
				},
				'file_name' => 'default',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
			'search' => [
				'condition' => function() {
					return \is_search();
				},
				'file_name' => 'search',
				'dependencies' => [ 'default_js' ],
				'dequeues' => [],
			],
		];

		\add_action( 'wp_enqueue_scripts', [ $this, 'inject_js_files' ], 999 );
	}

	/**
	 * Injecte le fichier JS global (default.js)
	 * 
	 * @return void
	 */
	private function inject_global_file(): void {
		\wp_enqueue_script(
			'default_js',
			$this->get_file_path( 'default' ),
			[], // Swiper est maintenant bundlé dans default.js via webpack
			TM_VERSION,
			true
		);
	}

	/**
	 * Construit le chemin vers un fichier JS
	 * 
	 * @param string $file_name Nom du fichier (sans extension)
	 * @return string Chemin complet vers le fichier JS
	 */
	private function get_file_path( string $file_name ): string {
		return $this->js_path . $file_name . '.js';
	}

	/**
	 * Injecte les fichiers JS conditionnellement
	 * 
	 * @return void
	 */
	public function inject_js_files(): void {
		// Charge toujours default.js en premier
		$this->inject_global_file();

		foreach ( $this->js_files as $handle => $js_file_info ) {
			if ( ! isset( $js_file_info['condition'] ) ) {
				continue;
			}

			if ( ! isset( $js_file_info['file_name'] ) ) {
				continue;
			}

			if ( ! \call_user_func( $js_file_info['condition'] ) ) {
				continue;
			}

			// Décharge les scripts spécifiés
			$dequeues = $js_file_info['dequeues'] ?? [];
			foreach ( $dequeues as $dequeue ) {
				\wp_dequeue_script( $dequeue );
			}

			// Récupère les dépendances
			$dependencies = $js_file_info['dependencies'] ?? [];

			// Enqueue le fichier JS
			\wp_enqueue_script(
				$handle,
				$this->get_file_path( $js_file_info['file_name'] ),
				$dependencies,
				TM_VERSION,
				true
			);

			// Localize script if needed
			if ( isset( $js_file_info['localize'] ) && is_callable( $js_file_info['localize'] ) ) {
				$localize_data = \call_user_func( $js_file_info['localize'] );
				if ( ! empty( $localize_data ) && isset( $localize_data['object_name'] ) && isset( $localize_data['data'] ) ) {
					\wp_localize_script( $handle, $localize_data['object_name'], $localize_data['data'] );
				}
			}

			// Arrête après avoir trouvé la première condition vraie
			break;
		}
	}
}
