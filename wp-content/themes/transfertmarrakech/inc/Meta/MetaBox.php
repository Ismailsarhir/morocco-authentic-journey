<?php
/**
 * Classe de base pour les Meta Boxes
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

/**
 * Classe abstraite pour gérer les meta boxes
 */
abstract class MetaBox {
	
	/**
	 * ID de la meta box
	 * 
	 * @var string
	 */
	protected string $meta_box_id;
	
	/**
	 * Titre de la meta box
	 * 
	 * @var string
	 */
	protected string $title;
	
	/**
	 * Post type associé
	 * 
	 * @var string
	 */
	protected string $post_type;
	
	/**
	 * Contexte de la meta box
	 * 
	 * @var string
	 */
	protected string $context = 'normal';
	
	/**
	 * Priorité de la meta box
	 * 
	 * @var string
	 */
	protected string $priority = 'high';
	
	/**
	 * Constructeur
	 * 
	 * @param string $meta_box_id ID unique de la meta box
	 * @param string $title       Titre de la meta box
	 * @param string $post_type   Post type associé
	 */
	public function __construct( string $meta_box_id, string $title, string $post_type ) {
		$this->meta_box_id = $meta_box_id;
		$this->title       = $title;
		$this->post_type   = $post_type;
	}
	
	/**
	 * Enregistre la meta box
	 * 
	 * @return void
	 */
	public function register(): void {
		// Enregistre la meta box sur le hook approprié (uniquement dans l'admin)
		\add_action( 'add_meta_boxes', [ $this, 'add_meta_box' ] );
	}
	
	/**
	 * Ajoute la meta box (appelé sur le hook add_meta_boxes)
	 * 
	 * @return void
	 */
	public function add_meta_box(): void {
		\add_meta_box(
			$this->meta_box_id,
			$this->title,
			[ $this, 'render' ],
			$this->post_type,
			$this->context,
			$this->priority
		);
	}
	
	/**
	 * Affiche le contenu de la meta box
	 * À surcharger dans les classes enfants
	 * 
	 * @param WP_Post $post Objet post
	 * @return void
	 */
	abstract public function render( $post ): void;
	
	/**
	 * Sauvegarde les données de la meta box
	 * À surcharger dans les classes enfants
	 * 
	 * @param int $post_id ID du post
	 * @return void
	 */
	abstract public function save( int $post_id ): void;
	
	/**
	 * Génère un champ nonce
	 * 
	 * @return void
	 */
	protected function nonce_field(): void {
		\wp_nonce_field( $this->meta_box_id . '_save', $this->meta_box_id . '_nonce' );
	}
	
	/**
	 * Vérifie le nonce
	 * 
	 * @return bool
	 */
	protected function verify_nonce(): bool {
		if ( ! isset( $_POST[ $this->meta_box_id . '_nonce' ] ) ) {
			return false;
		}
		
		return \wp_verify_nonce(
			$_POST[ $this->meta_box_id . '_nonce' ],
			$this->meta_box_id . '_save'
		);
	}
	
	/**
	 * Affiche un champ texte
	 * 
	 * @param string $name        Nom du champ
	 * @param string $label       Label du champ
	 * @param mixed  $value       Valeur actuelle
	 * @param string $placeholder Placeholder
	 * @return void
	 */
	protected function text_field( string $name, string $label, $value = '', string $placeholder = '' ): void {
		$value = \esc_attr( $value );
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<input 
				type="text" 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>" 
				value="<?php echo $value; ?>"
				placeholder="<?php echo \esc_attr( $placeholder ); ?>"
				class="widefat"
			>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ nombre
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param mixed  $value Valeur actuelle
	 * @return void
	 */
	protected function number_field( string $name, string $label, $value = '' ): void {
		$value = \esc_attr( $value );
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<input 
				type="number" 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>" 
				value="<?php echo $value; ?>"
				class="widefat"
				min="0"
				step="1"
			>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ textarea
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param mixed  $value Valeur actuelle
	 * @param int    $rows  Nombre de lignes
	 * @return void
	 */
	protected function textarea_field( string $name, string $label, $value = '', int $rows = 5 ): void {
		$value = \esc_textarea( $value );
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<textarea 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>" 
				rows="<?php echo \esc_attr( $rows ); ?>"
				class="widefat"
			><?php echo $value; ?></textarea>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ select
	 * 
	 * @param string $name    Nom du champ
	 * @param string $label   Label du champ
	 * @param array  $options Options (value => label)
	 * @param mixed  $value   Valeur actuelle
	 * @return void
	 */
	protected function select_field( string $name, string $label, array $options, $value = '' ): void {
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<select id="<?php echo \esc_attr( $name ); ?>" name="<?php echo \esc_attr( $name ); ?>" class="widefat">
				<option value=""><?php \esc_html_e( '-- Sélectionner --', 'transfertmarrakech' ); ?></option>
				<?php foreach ( $options as $option_value => $option_label ) : ?>
					<option value="<?php echo \esc_attr( $option_value ); ?>" <?php \selected( $value, $option_value ); ?>>
						<?php echo \esc_html( $option_label ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ checkbox
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param bool   $value Valeur actuelle
	 * @return void
	 */
	protected function checkbox_field( string $name, string $label, bool $value = false ): void {
		?>
		<p>
			<label>
				<input 
					type="checkbox" 
					name="<?php echo \esc_attr( $name ); ?>" 
					value="1"
					<?php \checked( $value, true ); ?>
				>
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ pour la galerie (IDs d'attachments)
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param array  $value Valeur actuelle (array d'IDs)
	 * @return void
	 */
	protected function gallery_field( string $name, string $label, array $value = [] ): void {
		$ids = is_array( $value ) ? $value : [];
		$ids_string = implode( ',', array_map( '\absint', $ids ) );
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<input 
				type="hidden" 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>" 
				value="<?php echo \esc_attr( $ids_string ); ?>"
				class="tm-gallery-ids"
			>
			<button 
				type="button" 
				class="button tm-gallery-button"
				data-target="<?php echo \esc_attr( $name ); ?>"
			>
				<?php \esc_html_e( 'Gérer la galerie', 'transfertmarrakech' ); ?>
			</button>
			<div class="tm-gallery-preview" style="margin-top: 10px;">
				<?php if ( ! empty( $ids ) ) : ?>
					<?php foreach ( $ids as $id ) : ?>
						<?php 
						$image = \wp_get_attachment_image_src( $id, 'thumbnail' );
						if ( $image ) :
						?>
							<div class="tm-gallery-item" style="display: inline-block; margin: 5px; position: relative;">
								<img 
									src="<?php echo \esc_url( $image[0] ); ?>" 
									style="width: 80px; height: 80px; object-fit: cover; display: block;"
									alt=""
								>
								<button 
									type="button" 
									class="button-link tm-remove-image" 
									data-id="<?php echo \esc_attr( $id ); ?>"
									style="position: absolute; top: 0; right: 0; background: rgba(0,0,0,0.7); color: white; border: none; cursor: pointer; padding: 2px 5px; font-size: 12px; line-height: 1;"
									title="<?php \esc_attr_e( 'Supprimer cette image', 'transfertmarrakech' ); ?>"
								>×</button>
							</div>
						<?php endif; ?>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ pour sélectionner des posts (multi-select)
	 * 
	 * @param string $name      Nom du champ
	 * @param string $label     Label du champ
	 * @param string $post_type Post type à sélectionner
	 * @param array  $value     Valeur actuelle (array d'IDs)
	 * @return void
	 */
	protected function post_select_field( string $name, string $label, string $post_type, array $value = [] ): void {
		$posts = \get_posts( [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );
		
		$selected_ids = is_array( $value ) ? $value : [];
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<!-- Champ hidden pour s'assurer que le champ est toujours envoyé -->
			<input type="hidden" name="<?php echo \esc_attr( $name ); ?>[]" value="">
			<select 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>[]" 
				class="widefat" 
				multiple
				size="5"
				style="min-height: 120px;"
			>
				<?php foreach ( $posts as $post ) : ?>
					<option value="<?php echo \esc_attr( $post->ID ); ?>" <?php \selected( in_array( $post->ID, $selected_ids, true ), true ); ?>>
						<?php echo \esc_html( $post->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<small><?php \esc_html_e( 'Maintenez Ctrl/Cmd pour sélectionner plusieurs éléments', 'transfertmarrakech' ); ?></small>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ pour sélectionner un seul post
	 * 
	 * @param string $name      Nom du champ
	 * @param string $label     Label du champ
	 * @param string $post_type Post type à sélectionner
	 * @param mixed  $value     Valeur actuelle (ID unique)
	 * @return void
	 */
	protected function single_post_select_field( string $name, string $label, string $post_type, $value = '' ): void {
		$posts = \get_posts( [
			'post_type'      => $post_type,
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		] );
		
		$selected_id = ! empty( $value ) ? \absint( $value ) : 0;
		?>
		<p>
			<label for="<?php echo \esc_attr( $name ); ?>">
				<strong><?php echo \esc_html( $label ); ?></strong>
			</label>
			<br>
			<select 
				id="<?php echo \esc_attr( $name ); ?>" 
				name="<?php echo \esc_attr( $name ); ?>" 
				class="widefat"
			>
				<option value=""><?php \esc_html_e( '-- Sélectionner --', 'transfertmarrakech' ); ?></option>
				<?php foreach ( $posts as $post ) : ?>
					<option value="<?php echo \esc_attr( $post->ID ); ?>" <?php \selected( $selected_id, $post->ID ); ?>>
						<?php echo \esc_html( $post->post_title ); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}

