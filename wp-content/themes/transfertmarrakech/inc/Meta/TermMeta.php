<?php
/**
 * Term Meta fields for tour_location taxonomy
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

/**
 * Classe pour gérer les champs meta des termes tour_location
 */
class TermMeta {
	
	/**
	 * Taxonomy slug
	 * 
	 * @var string
	 */
	private string $taxonomy = 'tour_location';
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		$this->register_hooks();
	}
	
	/**
	 * Enregistre les hooks WordPress
	 * 
	 * @return void
	 */
	private function register_hooks(): void {
		// Ajoute les champs dans le formulaire d'ajout et d'édition
		\add_action( "{$this->taxonomy}_add_form_fields", [ $this, 'add_form_fields' ] );
		\add_action( "{$this->taxonomy}_edit_form_fields", [ $this, 'edit_form_fields' ] );
		
		// Sauvegarde les champs
		\add_action( "created_{$this->taxonomy}", [ $this, 'save_term_meta' ] );
		\add_action( "edited_{$this->taxonomy}", [ $this, 'save_term_meta' ] );
		
		// Enqueue les scripts nécessaires
		\add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
	
	/**
	 * Enqueue les scripts pour le sélecteur de médias
	 * 
	 * @param string $hook_suffix Hook suffix
	 * @return void
	 */
	public function enqueue_scripts( string $hook_suffix ): void {
		// Charge uniquement sur les pages de taxonomie
		if ( $hook_suffix !== 'edit-tags.php' && $hook_suffix !== 'term.php' ) {
			return;
		}
		
		$screen = \get_current_screen();
		if ( ! $screen || $screen->taxonomy !== $this->taxonomy ) {
			return;
		}
		
		\wp_enqueue_media();
		
		\wp_enqueue_script(
			'tm-term-image',
			\get_template_directory_uri() . '/assets/js/admin-term-image.js',
			[ 'jquery', 'media-upload', 'media-views' ],
			TM_VERSION,
			true
		);
	}
	
	/**
	 * Ajoute les champs dans le formulaire d'ajout
	 * 
	 * @return void
	 */
	public function add_form_fields(): void {
		?>
		<div class="form-field term-image-wrap">
			<label for="tm_term_image"><?php \esc_html_e( 'Image de la destination', 'transfertmarrakech' ); ?></label>
			<div class="tm-term-image-container">
				<input type="hidden" id="tm_term_image" name="tm_term_image" value="" />
				<div class="tm-term-image-preview" style="margin-top: 10px;">
					<img src="" style="max-width: 300px; display: none;" />
				</div>
				<button type="button" class="button tm-upload-image-button">
					<?php \esc_html_e( 'Choisir une image', 'transfertmarrakech' ); ?>
				</button>
				<button type="button" class="button tm-remove-image-button" style="display: none;">
					<?php \esc_html_e( 'Supprimer l\'image', 'transfertmarrakech' ); ?>
				</button>
			</div>
			<p class="description"><?php \esc_html_e( 'Image qui sera affichée dans le carrousel des destinations.', 'transfertmarrakech' ); ?></p>
		</div>
		<?php
	}
	
	/**
	 * Ajoute les champs dans le formulaire d'édition
	 * 
	 * @param \WP_Term $term Terme
	 * @return void
	 */
	public function edit_form_fields( \WP_Term $term ): void {
		$image_id = \get_term_meta( $term->term_id, 'tm_term_image', true );
		$image_url = $image_id ? \wp_get_attachment_image_url( $image_id, 'medium' ) : '';
		?>
		<tr class="form-field term-image-wrap">
			<th scope="row">
				<label for="tm_term_image"><?php \esc_html_e( 'Image de la destination', 'transfertmarrakech' ); ?></label>
			</th>
			<td>
				<div class="tm-term-image-container">
					<input type="hidden" id="tm_term_image" name="tm_term_image" value="<?php echo \esc_attr( $image_id ); ?>" />
					<div class="tm-term-image-preview" style="margin-bottom: 10px;">
						<?php if ( $image_url ) : ?>
							<img src="<?php echo \esc_url( $image_url ); ?>" style="max-width: 300px; display: block;" />
						<?php else : ?>
							<img src="" style="max-width: 300px; display: none;" />
						<?php endif; ?>
					</div>
					<button type="button" class="button tm-upload-image-button">
						<?php \esc_html_e( 'Choisir une image', 'transfertmarrakech' ); ?>
					</button>
					<button type="button" class="button tm-remove-image-button" style="<?php echo $image_id ? '' : 'display: none;'; ?>">
						<?php \esc_html_e( 'Supprimer l\'image', 'transfertmarrakech' ); ?>
					</button>
				</div>
				<p class="description"><?php \esc_html_e( 'Image qui sera affichée dans le carrousel des destinations.', 'transfertmarrakech' ); ?></p>
			</td>
		</tr>
		<?php
	}
	
	/**
	 * Sauvegarde les meta données du terme
	 * 
	 * @param int $term_id ID du terme
	 * @return void
	 */
	public function save_term_meta( int $term_id ): void {
		if ( ! isset( $_POST['tm_term_image'] ) ) {
			return;
		}
		
		// Vérifie le nonce (si nécessaire)
		if ( ! \current_user_can( 'edit_term', $term_id ) ) {
			return;
		}
		
		$image_id = isset( $_POST['tm_term_image'] ) ? \absint( $_POST['tm_term_image'] ) : 0;
		
		if ( $image_id > 0 ) {
			\update_term_meta( $term_id, 'tm_term_image', $image_id );
		} else {
			\delete_term_meta( $term_id, 'tm_term_image' );
		}
	}
	
	/**
	 * Enregistre le champ meta
	 * 
	 * @return void
	 */
	public function register(): void {
		\register_term_meta(
			$this->taxonomy,
			'tm_term_image',
			[
				'type'              => 'integer',
				'description'       => __( 'Image de la destination pour le carrousel', 'transfertmarrakech' ),
				'single'            => true,
				'sanitize_callback' => 'absint',
				'auth_callback'     => function() {
					return \current_user_can( 'edit_terms' );
				},
				'show_in_rest'      => true,
			]
		);
	}
}

