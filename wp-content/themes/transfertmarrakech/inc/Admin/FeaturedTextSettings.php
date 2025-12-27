<?php
/**
 * Admin Settings Page for Featured Text
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Admin;

/**
 * Classe pour gérer la page d'administration du Featured Text
 */
class FeaturedTextSettings {
	
	/**
	 * Option group name
	 * 
	 * @var string
	 */
	private const OPTION_GROUP = 'tm_featured_text_settings';
	
	/**
	 * Page slug
	 * 
	 * @var string
	 */
	private const PAGE_SLUG = 'tm-featured-text';
	
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
	}
	
	/**
	 * Ajoute la page d'administration
	 * 
	 * @return void
	 */
	public function add_admin_page(): void {
		\add_theme_page(
			\__( 'Texte vedette', 'transfertmarrakech' ),
			\__( 'Texte vedette', 'transfertmarrakech' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}
	
	/**
	 * Enregistre les settings
	 * 
	 * @return void
	 */
	public function register_settings(): void {
		// Enregistre le setting pour le surtexte
		\register_setting(
			self::OPTION_GROUP,
			'tm_featured_surtext',
			[
				'type'              => 'string',
				'sanitize_callback' => 'sanitize_text_field',
				'default'           => \__( 'Depuis 2015', 'transfertmarrakech' ),
			]
		);
		
		// Enregistre le setting pour le texte principal
		\register_setting(
			self::OPTION_GROUP,
			'tm_featured_text',
			[
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post', // Permet le HTML basique
				'default'           => \__( 
					'Transfert Marrakech est bien plus qu\'un simple voyagiste, mais un pionnier du voyage au Maroc fort de ses 10 ans d\'expérience.', 
					'transfertmarrakech' 
				),
			]
		);
		
		// Enregistre la section de settings
		\add_settings_section(
			'tm_featured_text_section',
			\__( 'Paramètres du texte vedette', 'transfertmarrakech' ),
			[ $this, 'render_section_description' ],
			self::PAGE_SLUG
		);
		
		// Ajoute le champ surtexte
		\add_settings_field(
			'tm_featured_surtext',
			\__( 'Surtexte', 'transfertmarrakech' ),
			[ $this, 'render_surtext_field' ],
			self::PAGE_SLUG,
			'tm_featured_text_section'
		);
		
		// Ajoute le champ texte principal
		\add_settings_field(
			'tm_featured_text',
			\__( 'Texte principal', 'transfertmarrakech' ),
			[ $this, 'render_text_field' ],
			self::PAGE_SLUG,
			'tm_featured_text_section'
		);
	}
	
	/**
	 * Affiche la description de la section
	 * 
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . \esc_html__( 'Configurez le texte vedette affiché sur la page d\'accueil.', 'transfertmarrakech' ) . '</p>';
	}
	
	/**
	 * Affiche le champ surtexte
	 * 
	 * @return void
	 */
	public function render_surtext_field(): void {
		$value = \get_option( 'tm_featured_surtext', \__( 'Depuis 2015', 'transfertmarrakech' ) );
		?>
		<input 
			type="text" 
			name="tm_featured_surtext" 
			value="<?php echo \esc_attr( $value ); ?>" 
			class="regular-text"
			placeholder="<?php echo \esc_attr__( 'Depuis 2015', 'transfertmarrakech' ); ?>"
		>
		<p class="description">
			<?php \esc_html_e( 'Le texte qui apparaît au-dessus du texte principal (ex: "Depuis 2015")', 'transfertmarrakech' ); ?>
		</p>
		<?php
	}
	
	/**
	 * Affiche le champ texte principal
	 * 
	 * @return void
	 */
	public function render_text_field(): void {
		$value = \get_option( 'tm_featured_text', '' );
		?>
		<textarea 
			name="tm_featured_text" 
			rows="5" 
			class="large-text"
			placeholder="<?php echo \esc_attr__( 'Transfert Marrakech est bien plus qu\'un simple voyagiste...', 'transfertmarrakech' ); ?>"
		><?php echo \esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php \esc_html_e( 'Le texte principal affiché dans la section vedette. Vous pouvez utiliser du HTML basique.', 'transfertmarrakech' ); ?>
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
				'tm_featured_text_messages',
				'tm_featured_text_message',
				\__( 'Paramètres sauvegardés avec succès.', 'transfertmarrakech' ),
				'success'
			);
		}
		
		\settings_errors( 'tm_featured_text_messages' );
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
}

