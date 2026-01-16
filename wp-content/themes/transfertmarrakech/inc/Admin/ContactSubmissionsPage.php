<?php
/**
 * Admin Page for Contact Form Submissions
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Admin;

/**
 * Classe pour gérer la page d'administration des soumissions de contact
 */
class ContactSubmissionsPage {
	
	/**
	 * Page slug
	 * 
	 * @var string
	 */
	private const PAGE_SLUG = 'tm-contact-submissions';
	
	/**
	 * Table name (without prefix)
	 * 
	 * @var string
	 */
	private const TABLE_NAME = 'tm_contact_submissions';
	
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
	 * Verify nonce for admin actions
	 * 
	 * @param string $action Action name
	 * @param int    $id     Submission ID
	 * @return bool True if nonce is valid
	 */
	private function verify_nonce( string $action, int $id ): bool {
		return isset( $_GET['_wpnonce'] ) && \wp_verify_nonce( $_GET['_wpnonce'], $action . '_' . $id );
	}
	
	/**
	 * Get submission ID from request
	 * 
	 * @return int Submission ID or 0 if invalid
	 */
	private function get_submission_id(): int {
		$id = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		return $id > 0 ? $id : 0;
	}
	
	/**
	 * Enregistre la page d'administration
	 * 
	 * @return void
	 */
	public function register(): void {
		\add_action( 'admin_menu', [ $this, 'add_admin_page' ] );
		\add_action( 'admin_post_tm_mark_submission_read', [ $this, 'mark_as_read' ] );
		\add_action( 'admin_post_tm_delete_submission', [ $this, 'delete_submission' ] );
	}
	
	/**
	 * Ajoute la page d'administration
	 * 
	 * @return void
	 */
	public function add_admin_page(): void {
		\add_submenu_page(
			'tools.php',
			\__( 'Contact Submissions', 'transfertmarrakech' ),
			\__( 'Contact Submissions', 'transfertmarrakech' ),
			'manage_options',
			self::PAGE_SLUG,
			[ $this, 'render_page' ]
		);
	}
	
	/**
	 * Mark submission as read
	 * 
	 * @return void
	 */
	public function mark_as_read(): void {
		// Check permissions
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'You do not have permission to perform this action.', 'transfertmarrakech' ) );
		}
		
		$id = $this->get_submission_id();
		if ( ! $id ) {
			\wp_die( \esc_html__( 'Invalid submission ID.', 'transfertmarrakech' ) );
		}
		
		// Verify nonce
		if ( ! $this->verify_nonce( 'mark_read', $id ) ) {
			\wp_die( \esc_html__( 'Security check failed.', 'transfertmarrakech' ) );
		}
		
		// Update submission
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$updated = $wpdb->query( $wpdb->prepare(
			"UPDATE $table_name SET `read` = %d WHERE id = %d",
			1,
			$id
		) );
		
		// Redirect with success message
		$redirect_url = \add_query_arg( 'marked', '1', \admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		\wp_safe_redirect( $redirect_url );
		exit;
	}
	
	/**
	 * Delete submission
	 * 
	 * @return void
	 */
	public function delete_submission(): void {
		// Check permissions
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'You do not have permission to perform this action.', 'transfertmarrakech' ) );
		}
		
		$id = $this->get_submission_id();
		if ( ! $id ) {
			\wp_die( \esc_html__( 'Invalid submission ID.', 'transfertmarrakech' ) );
		}
		
		// Verify nonce
		if ( ! $this->verify_nonce( 'delete', $id ) ) {
			\wp_die( \esc_html__( 'Security check failed.', 'transfertmarrakech' ) );
		}
		
		// Delete submission
		global $wpdb;
		$table_name = $this->get_table_name();
		
		$deleted = $wpdb->delete( $table_name, [ 'id' => $id ], [ '%d' ] );
		
		// Redirect with success message
		$redirect_url = \add_query_arg( 'deleted', $deleted ? '1' : '0', \admin_url( 'admin.php?page=' . self::PAGE_SLUG ) );
		\wp_safe_redirect( $redirect_url );
		exit;
	}
	
	/**
	 * Get all submissions from database
	 * 
	 * @return array Array of submissions
	 */
	private function get_submissions(): array {
		global $wpdb;
		$table_name = $this->get_table_name();
		
		return $wpdb->get_results(
			"SELECT * FROM $table_name ORDER BY created_at DESC",
			ARRAY_A
		) ?: [];
	}
	
	/**
	 * Get count of unread submissions
	 * 
	 * @return int Number of unread submissions
	 */
	private function get_unread_count(): int {
		global $wpdb;
		$table_name = $this->get_table_name();
		
		return (int) $wpdb->get_var(
			"SELECT COUNT(*) FROM $table_name WHERE `read` = 0"
		);
	}
	
	/**
	 * Render admin notices
	 * 
	 * @return void
	 */
	private function render_notices(): void {
		$unread_count = $this->get_unread_count();
		
		if ( $unread_count > 0 ) {
			?>
			<div class="notice notice-info">
				<p>
					<strong><?php 
						echo \esc_html( \sprintf( 
							\_n( '%d unread submission', '%d unread submissions', $unread_count, 'transfertmarrakech' ), 
							$unread_count 
						) ); 
					?></strong>
				</p>
			</div>
			<?php
		}
		
		// Show action feedback
		if ( isset( $_GET['marked'] ) && $_GET['marked'] === '1' ) {
			?>
			<div class="notice notice-success is-dismissible">
				<p><?php \esc_html_e( 'Submission marked as read.', 'transfertmarrakech' ); ?></p>
			</div>
			<?php
		}
		
		if ( isset( $_GET['deleted'] ) ) {
			if ( $_GET['deleted'] === '1' ) {
				?>
				<div class="notice notice-success is-dismissible">
					<p><?php \esc_html_e( 'Submission deleted successfully.', 'transfertmarrakech' ); ?></p>
				</div>
				<?php
			} else {
				?>
				<div class="notice notice-error is-dismissible">
					<p><?php \esc_html_e( 'Failed to delete submission.', 'transfertmarrakech' ); ?></p>
				</div>
				<?php
			}
		}
	}
	
	/**
	 * Render submission row
	 * 
	 * @param array $submission Submission data
	 * @return void
	 */
	private function render_submission_row( array $submission ): void {
		$is_unread = empty( $submission['read'] );
		$row_class = $is_unread ? 'unread' : '';
		$submission_id = (int) $submission['id'];
		$has_long_message = \strlen( $submission['message'] ) > 100;
		$formatted_date = \date_i18n( 
			\get_option( 'date_format' ) . ' ' . \get_option( 'time_format' ), 
			\strtotime( $submission['created_at'] ) 
		);
		?>
		<tr class="<?php echo \esc_attr( $row_class ); ?>">
			<td><?php echo \esc_html( $submission_id ); ?></td>
			<td><strong><?php echo \esc_html( $submission['name'] ); ?></strong></td>
			<td>
				<a href="mailto:<?php echo \esc_attr( $submission['email'] ); ?>">
					<?php echo \esc_html( $submission['email'] ); ?>
				</a>
			</td>
			<td><?php echo \esc_html( $submission['phone'] ?: '-' ); ?></td>
			<td>
				<?php if ( $has_long_message ) : ?>
					<details>
						<summary><?php \esc_html_e( 'Read more', 'transfertmarrakech' ); ?></summary>
						<p><?php echo \nl2br( \esc_html( $submission['message'] ) ); ?></p>
					</details>
				<?php else : ?>
					<p><?php echo \nl2br( \esc_html( $submission['message'] ) ); ?></p>
				<?php endif; ?>
			</td>
			<td><?php echo \esc_html( $formatted_date ); ?></td>
			<td>
				<?php if ( $is_unread ) : ?>
					<a href="<?php echo \esc_url( \wp_nonce_url( 
						\admin_url( 'admin-post.php?action=tm_mark_submission_read&id=' . $submission_id ), 
						'mark_read_' . $submission_id 
					) ); ?>" class="button button-small">
						<?php \esc_html_e( 'Mark Read', 'transfertmarrakech' ); ?>
					</a>
				<?php endif; ?>
				<a href="<?php echo \esc_url( \wp_nonce_url( 
					\admin_url( 'admin-post.php?action=tm_delete_submission&id=' . $submission_id ), 
					'delete_' . $submission_id 
				) ); ?>" 
					class="button button-small button-link-delete" 
					onclick="return confirm('<?php \esc_attr_e( 'Are you sure you want to delete this submission?', 'transfertmarrakech' ); ?>');">
					<?php \esc_html_e( 'Delete', 'transfertmarrakech' ); ?>
				</a>
			</td>
		</tr>
		<?php
	}
	
	/**
	 * Affiche la page d'administration
	 * 
	 * @return void
	 */
	public function render_page(): void {
		if ( ! \current_user_can( 'manage_options' ) ) {
			\wp_die( \esc_html__( 'You do not have the necessary permissions to access this page.', 'transfertmarrakech' ) );
		}
		
		// Ensure table exists
		$this->create_submissions_table();
		
		// Get submissions
		$submissions = $this->get_submissions();
		?>
		<div class="wrap">
			<h1><?php echo \esc_html( \get_admin_page_title() ); ?></h1>
			
			<?php $this->render_notices(); ?>
			
			<?php if ( empty( $submissions ) ) : ?>
				<p><?php \esc_html_e( 'No submissions yet.', 'transfertmarrakech' ); ?></p>
			<?php else : ?>
				<table class="wp-list-table widefat fixed striped">
					<thead>
						<tr>
							<th style="width: 5%;"><?php \esc_html_e( 'ID', 'transfertmarrakech' ); ?></th>
							<th style="width: 15%;"><?php \esc_html_e( 'Name', 'transfertmarrakech' ); ?></th>
							<th style="width: 20%;"><?php \esc_html_e( 'Email', 'transfertmarrakech' ); ?></th>
							<th style="width: 15%;"><?php \esc_html_e( 'Phone', 'transfertmarrakech' ); ?></th>
							<th style="width: 30%;"><?php \esc_html_e( 'Message', 'transfertmarrakech' ); ?></th>
							<th style="width: 10%;"><?php \esc_html_e( 'Date', 'transfertmarrakech' ); ?></th>
							<th style="width: 5%;"><?php \esc_html_e( 'Actions', 'transfertmarrakech' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $submissions as $submission ) : ?>
							<?php $this->render_submission_row( $submission ); ?>
						<?php endforeach; ?>
					</tbody>
				</table>
				
				<style>
					.wp-list-table tr.unread {
						background-color: #fff3cd;
					}
					.wp-list-table tr.unread td {
						font-weight: 600;
					}
				</style>
			<?php endif; ?>
		</div>
		<?php
	}
}
