<?php
/**
 * Admin Settings Page for Archive Transfers
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Admin;

/**
 * Classe pour gérer la page d'administration de l'archive des transferts
 */
class ArchiveTransfersSettings {
	
	/**
	 * Option group name
	 * 
	 * @var string
	 */
	private const OPTION_GROUP = 'tm_archive_transfers_settings';
	
	/**
	 * Page slug
	 * 
	 * @var string
	 */
	private const PAGE_SLUG = 'tm-archive-transfers';
	
	/**
	 * Option name for archive image
	 * 
	 * @var string
	 */
	private const OPTION_ARCHIVE_IMAGE = 'tm_archive_transfers_image';
	
	/**
	 * Option name for archive title
	 * 
	 * @var string
	 */
	private const OPTION_ARCHIVE_TITLE = 'tm_archive_transfers_title';
	
	/**
	 * Option name for archive subtitle
	 * 
	 * @var string
	 */
	private const OPTION_ARCHIVE_SUBTITLE = 'tm_archive_transfers_subtitle';
	
	/**
	 * Enregistre la page d'administration et les settings
	 * 
	 * @return void
	 */
	public function register(): void {
		// Enregistre la page d'administration
		\add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		
		// Enregistre les settings
		\add_action( 'admin_init', [ $this, 'register_settings' ] );
		
		// Enqueue scripts pour le media uploader
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
	
	/**
	 * Enqueue les scripts nécessaires pour le media uploader
	 * 
	 * @param string $hook_suffix
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		// Charge les scripts uniquement sur notre page
		$expected_hook = 'transferts_page_' . self::PAGE_SLUG;
		if ( $expected_hook !== $hook_suffix ) {
			return;
		}
		
		// Enqueue WordPress media uploader
		\wp_enqueue_media();
		
		// Enqueue notre script personnalisé (réutilise le même script)
		\wp_enqueue_script(
			'tm-archive-transfers-admin',
			TM_THEME_URI . '/assets/js/admin-archive-tours.js',
			[ 'jquery' ],
			TM_VERSION,
			true
		);
	}
	
	/**
	 * Ajoute la page d'administration comme sous-menu de Transfers
	 * 
	 * @return void
	 */
	public function add_admin_page(): void {
		// Ajoute comme sous-menu du post type "transferts"
		\add_submenu_page(
			'edit.php?post_type=transferts', // Parent: menu Transfers
			\__( 'Archive Transfers', 'transfertmarrakech' ), // Page title
			\__( 'Archive Transfers', 'transfertmarrakech' ), // Menu title
			'manage_options', // Capability
			self::PAGE_SLUG, // Menu slug
			[ $this, 'render_page' ], // Callback
			20 // Position
		);
	}
	
	/**
	 * Enregistre les settings
	 * 
	 * @return void
	 */
	public function register_settings(): void {
		// Enregistre le setting pour l'image
		\register_setting(
			self::OPTION_GROUP,
			self::OPTION_ARCHIVE_IMAGE,
			[
				'type'              => 'integer',
				'sanitize_callback' => 'absint',
				'default'           => 0,
			]
		);
		
		// Enregistre le setting pour le titre
		\register_setting(
			self::OPTION_GROUP,
			self::OPTION_ARCHIVE_TITLE,
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => \__( 'Tous nos transferts', 'transfertmarrakech' ),
			]
		);
		
		// Enregistre le setting pour le sous-titre
		\register_setting(
			self::OPTION_GROUP,
			self::OPTION_ARCHIVE_SUBTITLE,
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => \__( 'Découvrez nos services de transfert', 'transfertmarrakech' ),
			]
		);
		
		// Enregistre la section de settings
		\add_settings_section(
			'tm_archive_transfers_section',
			\__( 'Paramètres de l\'archive des transferts', 'transfertmarrakech' ),
			[ $this, 'render_section_description' ],
			self::PAGE_SLUG
		);
		
		// Ajoute le champ image
		\add_settings_field(
			self::OPTION_ARCHIVE_IMAGE,
			\__( 'Image de fond', 'transfertmarrakech' ),
			[ $this, 'render_image_field' ],
			self::PAGE_SLUG,
			'tm_archive_transfers_section'
		);
		
		// Ajoute le champ titre
		\add_settings_field(
			self::OPTION_ARCHIVE_TITLE,
			\__( 'Titre', 'transfertmarrakech' ),
			[ $this, 'render_title_field' ],
			self::PAGE_SLUG,
			'tm_archive_transfers_section'
		);
		
		// Ajoute le champ sous-titre
		\add_settings_field(
			self::OPTION_ARCHIVE_SUBTITLE,
			\__( 'Sous-titre', 'transfertmarrakech' ),
			[ $this, 'render_subtitle_field' ],
			self::PAGE_SLUG,
			'tm_archive_transfers_section'
		);
	}
	
	/**
	 * Affiche la description de la section
	 * 
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . \esc_html__( 'Configurez l\'image, le titre et le sous-titre affichés sur la page d\'archive des transferts.', 'transfertmarrakech' ) . '</p>';
	}
	
	/**
	 * Affiche le champ image
	 * 
	 * @return void
	 */
	public function render_image_field(): void {
		$image_id = \get_option( self::OPTION_ARCHIVE_IMAGE, 0 );
		$image_url = $image_id ? \wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<div class="tm-archive-image-upload">
			<input 
				type="hidden" 
				id="<?php echo \esc_attr( self::OPTION_ARCHIVE_IMAGE ); ?>" 
				name="<?php echo \esc_attr( self::OPTION_ARCHIVE_IMAGE ); ?>" 
				value="<?php echo \esc_attr( $image_id ); ?>"
			>
			<div class="tm-archive-image-preview" style="margin: 10px 0;">
				<?php if ( $image_url ) : ?>
					<img src="<?php echo \esc_url( $image_url ); ?>" style="max-width: 300px; height: auto; display: block; margin-bottom: 10px;">
				<?php endif; ?>
			</div>
			<button 
				type="button" 
				class="button tm-archive-image-button"
				data-target="<?php echo \esc_attr( self::OPTION_ARCHIVE_IMAGE ); ?>"
			>
				<?php echo $image_id ? \esc_html__( 'Changer l\'image', 'transfertmarrakech' ) : \esc_html__( 'Sélectionner une image', 'transfertmarrakech' ); ?>
			</button>
			<?php if ( $image_id ) : ?>
				<button 
					type="button" 
					class="button tm-archive-image-remove"
					data-target="<?php echo \esc_attr( self::OPTION_ARCHIVE_IMAGE ); ?>"
					style="margin-left: 10px;"
				>
					<?php \esc_html_e( 'Supprimer', 'transfertmarrakech' ); ?>
				</button>
			<?php endif; ?>
			<p class="description">
				<?php \esc_html_e( 'Sélectionnez l\'image de fond pour le hero de l\'archive des transferts.', 'transfertmarrakech' ); ?>
			</p>
		</div>
		<?php
	}
	
	/**
	 * Affiche le champ titre
	 * 
	 * @return void
	 */
	public function render_title_field(): void {
		$value = \get_option( self::OPTION_ARCHIVE_TITLE, \__( 'Tous nos transferts', 'transfertmarrakech' ) );
		?>
		<input 
			type="text" 
			name="<?php echo \esc_attr( self::OPTION_ARCHIVE_TITLE ); ?>" 
			value="<?php echo \esc_attr( $value ); ?>" 
			class="regular-text"
			placeholder="<?php echo \esc_attr__( 'Tous nos transferts', 'transfertmarrakech' ); ?>"
		>
		<p class="description">
			<?php \esc_html_e( 'Le titre principal affiché dans le hero de l\'archive.', 'transfertmarrakech' ); ?>
		</p>
		<?php
	}
	
	/**
	 * Affiche le champ sous-titre
	 * 
	 * @return void
	 */
	public function render_subtitle_field(): void {
		$value = \get_option( self::OPTION_ARCHIVE_SUBTITLE, \__( 'Découvrez nos services de transfert', 'transfertmarrakech' ) );
		?>
		<input 
			type="text" 
			name="<?php echo \esc_attr( self::OPTION_ARCHIVE_SUBTITLE ); ?>" 
			value="<?php echo \esc_attr( $value ); ?>" 
			class="regular-text"
			placeholder="<?php echo \esc_attr__( 'Découvrez nos services de transfert', 'transfertmarrakech' ); ?>"
		>
		<p class="description">
			<?php \esc_html_e( 'Le sous-titre affiché sous le titre principal.', 'transfertmarrakech' ); ?>
		</p>
		<?php
	}
	
	/**
	 * Affiche la page d'administration
	 * 
	 * @return void
	 */
	public function render_page(): void {
		// Vérifie les permissions
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \__( 'Vous n\'avez pas les permissions nécessaires pour accéder à cette page.', 'transfertmarrakech' ) );
		}
		
		// Affiche les messages de succès
		if ( isset( $_GET['settings-updated'] ) ) {
			\add_settings_error(
				'tm_archive_transfers_messages',
				'tm_archive_transfers_message',
				\__( 'Paramètres sauvegardés avec succès.', 'transfertmarrakech' ),
				'success'
			);
		}
		
		\settings_errors( 'tm_archive_transfers_messages' );
		?>
		<div class="wrap">
			<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				\settings_fields( self::OPTION_GROUP );
				\do_settings_sections( self::PAGE_SLUG );
				\submit_button( \__( 'Enregistrer les modifications', 'transfertmarrakech' ) );
				?>
			</form>
		</div>
		<?php
	}
	
	/**
	 * Récupère l'ID de l'image de l'archive
	 * 
	 * @return int
	 */
	public static function get_archive_image_id(): int {
		return (int) \get_option( self::OPTION_ARCHIVE_IMAGE, 0 );
	}
	
	/**
	 * Récupère le titre de l'archive
	 * 
	 * @return string
	 */
	public static function get_archive_title(): string {
		return \get_option( self::OPTION_ARCHIVE_TITLE, \__( 'Tous nos transferts', 'transfertmarrakech' ) );
	}
	
	/**
	 * Récupère le sous-titre de l'archive
	 * 
	 * @return string
	 */
	public static function get_archive_subtitle(): string {
		return \get_option( self::OPTION_ARCHIVE_SUBTITLE, \__( 'Découvrez nos services de transfert', 'transfertmarrakech' ) );
	}
}

