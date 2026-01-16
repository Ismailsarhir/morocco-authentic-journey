<?php
/**
 * Contact Form Handler
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Core;

/**
 * Classe pour gérer les soumissions du formulaire de contact
 */
class ContactFormHandler {
	
	/**
	 * Instance unique de la classe (Singleton)
	 * 
	 * @var ContactFormHandler|null
	 */
	private static ?ContactFormHandler $instance = null;
	
	/**
	 * Table name (without prefix)
	 * 
	 * @var string
	 */
	private const TABLE_NAME = 'tm_contact_submissions';
	
	/**
	 * Flag to prevent duplicate processing in the same request
	 * 
	 * @var bool
	 */
	private static bool $is_processing = false;
	
	/**
	 * Get table name with prefix
	 * 
	 * @return string
	 */
	private function get_table_name(): string {
		global $wpdb;
		return $wpdb->prefix . self::TABLE_NAME;
	}
	
	/**
	 * Constructeur privé (Singleton)
	 */
	private function __construct() {
		// Empêche l'instanciation directe
	}
	
	/**
	 * Récupère l'instance unique de la classe
	 * 
	 * @return ContactFormHandler
	 */
	public static function get_instance(): ContactFormHandler {
		if ( is_null( static::$instance ) ) {
			static::$instance = new self();
		}
		return static::$instance;
	}
	
	/**
	 * Enregistre les hooks
	 * 
	 * @return void
	 */
	public function register(): void {
		\add_action( 'admin_post_tm_contact_form_submit', [ $this, 'handle_submission' ] );
		\add_action( 'admin_post_nopriv_tm_contact_form_submit', [ $this, 'handle_submission' ] );
		\add_action( 'wp_ajax_tm_contact_form_submit', [ $this, 'handle_ajax_submission' ] );
		\add_action( 'wp_ajax_nopriv_tm_contact_form_submit', [ $this, 'handle_ajax_submission' ] );
	}
	
	/**
	 * Store submission in database
	 * 
	 * @param string $name    Name
	 * @param string $email   Email
	 * @param string $phone   Phone
	 * @param string $message Message
	 * @return int|false Submission ID or false on failure
	 */
	private function store_submission( string $name, string $email, string $phone, string $message ) {
		// Create table if it doesn't exist
		$this->create_submissions_table();
		
		global $wpdb;
		$table_name = $this->get_table_name();
		
		// Check for duplicate submission within last 30 seconds (same email + message)
		$recent_duplicate = $wpdb->get_var( $wpdb->prepare(
			"SELECT id FROM $table_name 
			WHERE email = %s 
			AND message = %s 
			AND created_at > DATE_SUB(NOW(), INTERVAL 30 SECOND)
			LIMIT 1",
			$email,
			$message
		) );
		
		// If duplicate found, return false to prevent double entry
		if ( $recent_duplicate ) {
			return false;
		}
		
		// Insert with backticks around reserved keyword 'read'
		$result = $wpdb->query( $wpdb->prepare(
			"INSERT INTO $table_name (name, email, phone, message, created_at, `read`) VALUES (%s, %s, %s, %s, %s, %d)",
			$name,
			$email,
			$phone,
			$message,
			\current_time( 'mysql' ),
			0
		) );
		
		return $result !== false ? $wpdb->insert_id : false;
	}
	
	/**
	 * Create submissions table if it doesn't exist
	 * 
	 * @return void
	 */
	private function create_submissions_table(): void {
		global $wpdb;
		$table_name = $this->get_table_name();
		
		// Check if table exists
		$table_exists = $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE %s", $table_name ) ) === $table_name;
		
		if ( $table_exists ) {
			return;
		}
		
		$charset_collate = $wpdb->get_charset_collate();
		
		$sql = "CREATE TABLE $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			name varchar(255) NOT NULL,
			email varchar(255) NOT NULL,
			phone varchar(100) DEFAULT NULL,
			message text NOT NULL,
			created_at datetime NOT NULL,
			`read` tinyint(1) DEFAULT 0,
			PRIMARY KEY (id),
			KEY created_at (created_at),
			KEY read_status (`read`)
		) $charset_collate;";
		
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		\dbDelta( $sql );
	}
	
	/**
	 * Validate and sanitize form data
	 * 
	 * @return array Array with 'data' and 'errors' keys
	 */
	private function validate_form_data(): array {
		$data = [
			'name'    => isset( $_POST['contact_name'] ) ? \sanitize_text_field( $_POST['contact_name'] ) : '',
			'email'   => isset( $_POST['contact_email'] ) ? \sanitize_email( $_POST['contact_email'] ) : '',
			'phone'   => isset( $_POST['contact_phone'] ) ? \sanitize_text_field( $_POST['contact_phone'] ) : '',
			'message' => isset( $_POST['contact_message'] ) ? \sanitize_textarea_field( $_POST['contact_message'] ) : '',
		];
		
		$errors = [];
		
		if ( empty( $data['name'] ) ) {
			$errors['contact_name'] = __( 'Name is required.', 'transfertmarrakech' );
		}
		
		if ( empty( $data['email'] ) || ! \is_email( $data['email'] ) ) {
			$errors['contact_email'] = __( 'Valid email is required.', 'transfertmarrakech' );
		}
		
		if ( empty( $data['message'] ) ) {
			$errors['contact_message'] = __( 'Message is required.', 'transfertmarrakech' );
		}
		
		return [
			'data'   => $data,
			'errors' => $errors,
		];
	}
	
	/**
	 * Send notification email
	 * 
	 * @param string $name    Name
	 * @param string $email   Email
	 * @param string $phone   Phone
	 * @param string $message Message
	 * @return bool True if email sent successfully
	 */
	private function send_notification_email( string $name, string $email, string $phone, string $message ): bool {
		$to = \get_option( 'admin_email' );
		$subject = \sprintf(
			\__( 'New Contact Form Submission from %s', 'transfertmarrakech' ),
			\get_bloginfo( 'name' )
		);
		
		$email_body = \sprintf(
			"%s: %s\n\n%s: %s\n\n%s: %s\n\n%s:\n%s",
			\__( 'Name', 'transfertmarrakech' ),
			$name,
			\__( 'Email', 'transfertmarrakech' ),
			$email,
			\__( 'Phone', 'transfertmarrakech' ),
			$phone ?: \__( 'Not provided', 'transfertmarrakech' ),
			\__( 'Message', 'transfertmarrakech' ),
			$message
		);
		
		$headers = [
			'Content-Type: text/plain; charset=UTF-8',
			'From: ' . \get_bloginfo( 'name' ) . ' <' . \get_option( 'admin_email' ) . '>',
			'Reply-To: ' . $name . ' <' . $email . '>',
		];
		
		return \wp_mail( $to, $subject, $email_body, $headers );
	}
	
	/**
	 * Traite la soumission AJAX du formulaire
	 * 
	 * @return void
	 */
	public function handle_ajax_submission(): void {
		// Prevent duplicate processing
		if ( self::$is_processing ) {
			\wp_send_json_error( [
				'message' => __( 'Your request is already being processed. Please wait.', 'transfertmarrakech' ),
			] );
			return;
		}
		
		// Vérifie le nonce
		if ( ! isset( $_POST['contact_form_nonce'] ) || ! \wp_verify_nonce( $_POST['contact_form_nonce'], 'contact_form_submit' ) ) {
			\wp_send_json_error( [
				'message' => __( 'Security check failed. Please refresh the page and try again.', 'transfertmarrakech' ),
			] );
			return;
		}
		
		// Set processing flag
		self::$is_processing = true;
		
		$result = $this->process_submission();
		
		// Reset processing flag
		self::$is_processing = false;
		
		if ( $result['success'] ) {
			\wp_send_json_success( [
				'message' => __( 'Your message has been sent successfully. We will contact you as soon as possible.', 'transfertmarrakech' ),
			] );
		} else {
			\wp_send_json_error( [
				'message' => $result['message'],
				'errors'  => $result['errors'] ?? [],
			] );
		}
	}
	
	/**
	 * Traite la soumission du formulaire (fallback pour non-AJAX)
	 * 
	 * @return void
	 */
	public function handle_submission(): void {
		// Prevent duplicate processing
		if ( self::$is_processing ) {
			\wp_safe_redirect( \add_query_arg( 'contact_error', '1', \wp_get_referer() ) );
			exit;
		}
		
		// Vérifie le nonce
		if ( ! isset( $_POST['contact_form_nonce'] ) || ! \wp_verify_nonce( $_POST['contact_form_nonce'], 'contact_form_submit' ) ) {
			\wp_safe_redirect( \add_query_arg( 'contact_error', '1', \wp_get_referer() ) );
			exit;
		}
		
		// Set processing flag
		self::$is_processing = true;
		
		$result = $this->process_submission();
		
		// Reset processing flag
		self::$is_processing = false;
		
		// Redirige avec succès ou erreur
		if ( $result['success'] ) {
			\wp_safe_redirect( \add_query_arg( 'contact_sent', '1', \wp_get_referer() ) );
		} else {
			\wp_safe_redirect( \add_query_arg( 'contact_error', '1', \wp_get_referer() ) );
		}
		
		exit;
	}
	
	/**
	 * Process form submission (shared logic)
	 * 
	 * @return array Result array with success status and message/errors
	 */
	private function process_submission(): array {
		// Validate and sanitize form data
		$validation = $this->validate_form_data();
		
		// Return errors if validation failed
		if ( ! empty( $validation['errors'] ) ) {
			return [
				'success' => false,
				'message' => __( 'Please correct the errors in the form.', 'transfertmarrakech' ),
				'errors'  => $validation['errors'],
			];
		}
		
		$data = $validation['data'];
		
		// Store submission in database
		$submission_id = $this->store_submission( 
			$data['name'], 
			$data['email'], 
			$data['phone'], 
			$data['message'] 
		);
		
		// Send notification email
		$email_sent = $this->send_notification_email( 
			$data['name'], 
			$data['email'], 
			$data['phone'], 
			$data['message'] 
		);
		
		// Consider success if either database storage or email succeeded
		if ( $submission_id || $email_sent ) {
			return [
				'success' => true,
				'message' => __( 'Your message has been sent successfully.', 'transfertmarrakech' ),
			];
		}
		
		return [
			'success' => false,
			'message' => __( 'There was an error sending your message. Please try again later.', 'transfertmarrakech' ),
		];
	}
}
