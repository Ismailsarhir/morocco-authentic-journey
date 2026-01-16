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
			\__( 'Featured Text', 'transfertmarrakech' ),
			\__( 'Featured Text', 'transfertmarrakech' ),
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
				'default'           => \__( 'Since 2015', 'transfertmarrakech' ),
			]
		);
		
		// Enregistre le setting pour le texte principal
		// Calculate years dynamically (current year - 2015)
		$years_of_experience = (int) \date( 'Y' ) - 2015;
		$default_text = sprintf(
			\__( 
				'Transfert Marrakech is much more than a simple travel agency, but a pioneer of travel in Morocco with %d years of experience.', 
				'transfertmarrakech' 
			),
			$years_of_experience
		);
		
		\register_setting(
			self::OPTION_GROUP,
			'tm_featured_text',
			[
				'type'              => 'string',
				'sanitize_callback' => 'wp_kses_post', // Permet le HTML basique
				'default'           => $default_text,
			]
		);
		
		// Enregistre la section de settings
		\add_settings_section(
			'tm_featured_text_section',
			\__( 'Featured Text Settings', 'transfertmarrakech' ),
			[ $this, 'render_section_description' ],
			self::PAGE_SLUG
		);
		
		// Ajoute le champ surtexte
		\add_settings_field(
			'tm_featured_surtext',
			\__( 'Surtext', 'transfertmarrakech' ),
			[ $this, 'render_surtext_field' ],
			self::PAGE_SLUG,
			'tm_featured_text_section'
		);
		
		// Ajoute le champ texte principal
		\add_settings_field(
			'tm_featured_text',
			\__( 'Main Text', 'transfertmarrakech' ),
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
		echo '<p>' . \esc_html__( 'Configure the featured text displayed on the homepage.', 'transfertmarrakech' ) . '</p>';
	}
	
	/**
	 * Affiche le champ surtexte
	 * 
	 * @return void
	 */
	public function render_surtext_field(): void {
		$value = \get_option( 'tm_featured_surtext', \__( 'Since 2015', 'transfertmarrakech' ) );
		?>
		<input 
			type="text" 
			name="tm_featured_surtext" 
			value="<?php echo \esc_attr( $value ); ?>" 
			class="regular-text"
			placeholder="<?php echo \esc_attr__( 'Since 2015', 'transfertmarrakech' ); ?>"
		>
		<p class="description">
			<?php \esc_html_e( 'The text that appears above the main text (e.g., "Since 2015")', 'transfertmarrakech' ); ?>
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
		// Calculate years dynamically for placeholder
		$years_of_experience = (int) \date( 'Y' ) - 2015;
		$placeholder = sprintf(
			\__( 'Transfert Marrakech is much more than a simple travel agency, but a pioneer of travel in Morocco with %d years of experience.', 'transfertmarrakech' ),
			$years_of_experience
		);
		?>
		<textarea 
			name="tm_featured_text" 
			rows="5" 
			class="large-text"
			placeholder="<?php echo \esc_attr( $placeholder ); ?>"
		><?php echo \esc_textarea( $value ); ?></textarea>
		<p class="description">
			<?php \esc_html_e( 'The main text displayed in the featured section. You can use basic HTML. Leave empty to use the default text with automatic year calculation.', 'transfertmarrakech' ); ?>
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
			\wp_die( \__( 'You do not have the necessary permissions to access this page.', 'transfertmarrakech' ) );
		}
		
		// Affiche les messages de succès
		if ( isset( $_GET['settings-updated'] ) ) {
			\add_settings_error(
				'tm_featured_text_messages',
				'tm_featured_text_message',
				\__( 'Settings saved successfully.', 'transfertmarrakech' ),
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
				\submit_button( \__( 'Save Changes', 'transfertmarrakech' ) );
				?>
			</form>
		</div>
		<?php
	}
}

