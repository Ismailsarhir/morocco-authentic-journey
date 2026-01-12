<?php
/**
 * Meta Box pour les Posts (articles standards)
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

/**
 * Classe pour gérer les meta boxes des posts
 */
class PostMeta extends MetaBox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct(
			'tm_post_meta',
			__( 'Post Options', 'transfertmarrakech' ),
			'post'
		);
	}
	
	/**
	 * Affiche le contenu de la meta box
	 * 
	 * @param WP_Post $post Objet post
	 * @return void
	 */
	public function render( $post ): void {
		$this->nonce_field();
		
		// Récupère la valeur actuelle
		$show_in_hero = \get_post_meta( $post->ID, 'tm_show_in_hero', true );
		$hero_video_id = \get_post_meta( $post->ID, 'tm_hero_video_id', true );
		
		// Checkbox pour afficher dans le Hero
		$this->checkbox_field( 
			'tm_show_in_hero', 
			__( 'Show in Hero', 'transfertmarrakech' ), 
			(bool) $show_in_hero 
		);
		
		// Sélection de la vidéo depuis la médiathèque pour le Hero
		$this->video_field( 
			'tm_hero_video_id', 
			__( 'Hero Video', 'transfertmarrakech' ), 
			$hero_video_id
		);
		
		?>
		<p>
			<small>
				<?php \esc_html_e( 'Note: If multiple posts are checked, only the most recent one will be displayed in the Hero.', 'transfertmarrakech' ); ?>
			</small>
		</p>
		<?php
	}
	
	/**
	 * Sauvegarde les données de la meta box
	 * 
	 * @param int $post_id ID du post
	 * @return void
	 */
	public function save( int $post_id ): void {
		// Vérifications de sécurité
		if ( ! $this->verify_nonce() ) {
			return;
		}
		
		if ( \defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		
		if ( ! \current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		
		// Vérifie que c'est bien un post
		$post = \get_post( $post_id );
		if ( ! $post || $post->post_type !== 'post' ) {
			return;
		}
		
		// Show in Hero checkbox
		if ( isset( $_POST['tm_show_in_hero'] ) ) {
			\update_post_meta( $post_id, 'tm_show_in_hero', '1' );
		} else {
			\delete_post_meta( $post_id, 'tm_show_in_hero' );
		}
		
		// Hero Video ID (from media library)
		if ( isset( $_POST['tm_hero_video_id'] ) && ! empty( $_POST['tm_hero_video_id'] ) ) {
			$video_id = \absint( $_POST['tm_hero_video_id'] );
			// Vérifie que c'est bien une vidéo
			$mime_type = \get_post_mime_type( $video_id );
			if ( $mime_type && \strpos( $mime_type, 'video/' ) === 0 ) {
				\update_post_meta( $post_id, 'tm_hero_video_id', $video_id );
			}
		} else {
			\delete_post_meta( $post_id, 'tm_hero_video_id' );
		}
	}
}

