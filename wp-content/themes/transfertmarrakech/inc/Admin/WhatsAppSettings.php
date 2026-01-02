<?php
/**
 * Admin Settings Page for WhatsApp Phone Number
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Admin;

use TM\Core\Constants;

/**
 * Classe pour gérer la page d'administration du numéro WhatsApp
 */
class WhatsAppSettings {
	
	/**
	 * Option group name
	 * 
	 * @var string
	 */
	private const OPTION_GROUP = 'tm_whatsapp_settings';
	
	/**
	 * Page slug
	 * 
	 * @var string
	 */
	private const PAGE_SLUG = 'tm-whatsapp';
	
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
			\__( 'Numéro WhatsApp', 'transfertmarrakech' ),
			\__( 'Numéro WhatsApp', 'transfertmarrakech' ),
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
		// Enregistre le setting pour le numéro WhatsApp
		\register_setting(
			self::OPTION_GROUP,
			Constants::OPTION_WHATSAPP_PHONE,
			[
				'type'              => 'string',
				'sanitize_callback' => [ $this, 'sanitize_phone_number' ],
				'default'           => Constants::OPTION_WHATSAPP_PHONE_DEFAULT,
			]
		);
		
		// Enregistre la section de settings
		\add_settings_section(
			'tm_whatsapp_section',
			\__( 'Paramètres WhatsApp', 'transfertmarrakech' ),
			[ $this, 'render_section_description' ],
			self::PAGE_SLUG
		);
		
		// Ajoute le champ numéro de téléphone
		\add_settings_field(
			Constants::OPTION_WHATSAPP_PHONE,
			\__( 'Numéro de téléphone WhatsApp', 'transfertmarrakech' ),
			[ $this, 'render_phone_field' ],
			self::PAGE_SLUG,
			'tm_whatsapp_section'
		);
	}
	
	/**
	 * Sanitize le numéro de téléphone
	 * 
	 * @param string $value Valeur à nettoyer
	 * @return string
	 */
	public function sanitize_phone_number( string $value ): string {
		// Retire tous les caractères non numériques sauf le +
		$cleaned = \preg_replace( '/[^0-9+]/', '', $value );
		
		// Si le numéro commence par +, on le garde, sinon on retire le +
		if ( \strpos( $cleaned, '+' ) === 0 ) {
			return $cleaned;
		}
		
		return \preg_replace( '/[^0-9]/', '', $cleaned );
	}
	
	/**
	 * Affiche la description de la section
	 * 
	 * @return void
	 */
	public function render_section_description(): void {
		echo '<p>' . \esc_html__( 'Configurez le numéro de téléphone WhatsApp utilisé pour les boutons de contact et de réservation.', 'transfertmarrakech' ) . '</p>';
	}
	
	/**
	 * Affiche le champ numéro de téléphone
	 * 
	 * @return void
	 */
	public function render_phone_field(): void {
		$value = \get_option( Constants::OPTION_WHATSAPP_PHONE, Constants::OPTION_WHATSAPP_PHONE_DEFAULT );
		?>
		<input 
			type="text" 
			name="<?php echo \esc_attr( Constants::OPTION_WHATSAPP_PHONE ); ?>" 
			value="<?php echo \esc_attr( $value ); ?>" 
			class="regular-text"
			placeholder="<?php echo \esc_attr( Constants::OPTION_WHATSAPP_PHONE_DEFAULT ); ?>"
			pattern="[0-9+]+"
		>
		<p class="description">
			<?php \esc_html_e( 'Entrez le numéro de téléphone WhatsApp avec l\'indicatif pays (ex: 2126xxxxxxxx pour le Maroc). Ne pas inclure le + au début.', 'transfertmarrakech' ); ?>
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
				'tm_whatsapp_messages',
				'tm_whatsapp_message',
				\__( 'Paramètres sauvegardés avec succès.', 'transfertmarrakech' ),
				'success'
			);
		}
		
		\settings_errors( 'tm_whatsapp_messages' );
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
	 * Récupère le numéro WhatsApp depuis les options
	 * 
	 * @return string
	 */
	public static function get_phone_number(): string {
		return \get_option( Constants::OPTION_WHATSAPP_PHONE, Constants::OPTION_WHATSAPP_PHONE_DEFAULT );
	}
}

