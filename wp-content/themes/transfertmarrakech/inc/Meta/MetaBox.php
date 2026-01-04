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
	 * Affiche un champ multi-checkbox
	 * 
	 * @param string $name    Nom du champ
	 * @param string $label   Label du champ
	 * @param array  $options Options (value => label)
	 * @param array  $value   Valeurs actuelles (array)
	 * @return void
	 */
	protected function multi_checkbox_field( string $name, string $label, array $options, array $value = [] ): void {
		$selected = is_array( $value ) ? $value : [];
		?>
		<p>
			<strong><?php echo \esc_html( $label ); ?></strong>
			<br>
			<!-- Champ hidden pour s'assurer que le champ est toujours envoyé -->
			<input type="hidden" name="<?php echo \esc_attr( $name ); ?>[]" value="">
			<?php foreach ( $options as $option_value => $option_label ) : ?>
				<label style="display: block; margin: 5px 0;">
					<input 
						type="checkbox" 
						name="<?php echo \esc_attr( $name ); ?>[]" 
						value="<?php echo \esc_attr( $option_value ); ?>"
						<?php \checked( in_array( $option_value, $selected, true ), true ); ?>
					>
					<?php echo \esc_html( $option_label ); ?>
				</label>
			<?php endforeach; ?>
		</p>
		<?php
	}
	
	/**
	 * Affiche un champ pour les prix par nombre de personnes (repeater)
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param array  $value Valeur actuelle (array de tiers)
	 * @return void
	 */
	protected function price_tiers_field( string $name, string $label, array $value = [] ): void {
		$tiers = is_array( $value ) && ! empty( $value ) ? $value : [ [ 'min_persons' => '', 'max_persons' => '', 'price' => '', 'type' => '' ] ];
		$tour_type_options = [
			''        => __( '-- Tous types --', 'transfertmarrakech' ),
			'group'   => __( 'Group Tour', 'transfertmarrakech' ),
			'private' => __( 'Private Tour', 'transfertmarrakech' ),
			'shared'  => __( 'Shared Group', 'transfertmarrakech' ),
		];
		?>
		<p>
			<strong><?php echo \esc_html( $label ); ?></strong>
			<br>
			<small><?php \esc_html_e( 'Ajoutez des prix pour différentes tranches de nombre de personnes', 'transfertmarrakech' ); ?></small>
		</p>
		<div class="tm-price-tiers" data-field-name="<?php echo \esc_attr( $name ); ?>">
			<?php foreach ( $tiers as $index => $tier ) : ?>
				<div class="tm-price-tier-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: flex-end;">
					<div style="flex: 1;">
						<label><?php \esc_html_e( 'Min personnes', 'transfertmarrakech' ); ?></label>
						<input 
							type="number" 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $index ); ?>][min_persons]" 
							value="<?php echo \esc_attr( $tier['min_persons'] ?? '' ); ?>"
							min="1"
							class="widefat"
							required
						>
					</div>
					<div style="flex: 1;">
						<label><?php \esc_html_e( 'Max personnes', 'transfertmarrakech' ); ?></label>
						<input 
							type="number" 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $index ); ?>][max_persons]" 
							value="<?php echo \esc_attr( $tier['max_persons'] ?? '' ); ?>"
							min="1"
							class="widefat"
							required
						>
					</div>
					<div style="flex: 1;">
						<label><?php \esc_html_e( 'Prix (USD/personne)', 'transfertmarrakech' ); ?></label>
						<input 
							type="text" 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $index ); ?>][price]" 
							value="<?php echo \esc_attr( $tier['price'] ?? '' ); ?>"
							placeholder="50.00"
							class="widefat"
							required
						>
					</div>
					<div style="flex: 1;">
						<label><?php \esc_html_e( 'Type (optionnel)', 'transfertmarrakech' ); ?></label>
						<select 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $index ); ?>][type]" 
							class="widefat"
						>
							<?php foreach ( $tour_type_options as $option_value => $option_label ) : ?>
								<option value="<?php echo \esc_attr( $option_value ); ?>" <?php \selected( $tier['type'] ?? '', $option_value ); ?>>
									<?php echo \esc_html( $option_label ); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</div>
					<div>
						<button type="button" class="button tm-remove-tier" style="margin-bottom: 2px;"><?php \esc_html_e( 'Supprimer', 'transfertmarrakech' ); ?></button>
					</div>
				</div>
			<?php endforeach; ?>
			<button type="button" class="button tm-add-tier"><?php \esc_html_e( '+ Ajouter un prix', 'transfertmarrakech' ); ?></button>
		</div>
		<script>
		(function($) {
			$(document).ready(function() {
				var $container = $('.tm-price-tiers[data-field-name="<?php echo \esc_js( $name ); ?>"]');
				var tierIndex = <?php echo count( $tiers ); ?>;
				
				// Ajouter un nouveau tier
				$container.on('click', '.tm-add-tier', function(e) {
					e.preventDefault();
					var $row = $container.find('.tm-price-tier-row').first().clone();
					$row.find('input, select').each(function() {
						var name = $(this).attr('name');
						if (name) {
							$(this).attr('name', name.replace(/\[\d+\]/, '[' + tierIndex + ']'));
							$(this).val('');
						}
					});
					$container.find('.tm-add-tier').before($row);
					tierIndex++;
				});
				
				// Supprimer un tier
				$container.on('click', '.tm-remove-tier', function(e) {
					e.preventDefault();
					if ($container.find('.tm-price-tier-row').length > 1) {
						$(this).closest('.tm-price-tier-row').remove();
					} else {
						alert('<?php echo \esc_js( __( 'Vous devez avoir au moins un prix.', 'transfertmarrakech' ) ); ?>');
					}
				});
			});
		})(jQuery);
		</script>
		<?php
	}
	
	/**
	 * Affiche un champ pour l'itinéraire avec titre général et places répétables
	 * 
	 * @param string $title_name  Nom du champ pour le titre général
	 * @param string $places_name Nom du champ pour les places
	 * @param string $label       Label du champ
	 * @param string $title_value Valeur du titre général
	 * @param array  $places      Valeur des places (array)
	 * @return void
	 */
	protected function itinerary_field( string $title_name, string $places_name, string $label, string $title_value = '', array $places = [] ): void {
		$places = is_array( $places ) && ! empty( $places ) ? $places : [ [ 'time' => '', 'title' => '', 'description' => '' ] ];
		?>
		<p>
			<strong><?php echo \esc_html( $label ); ?></strong>
		</p>
		
		<!-- Titre général -->
		<p>
			<label><strong><?php \esc_html_e( 'Titre général', 'transfertmarrakech' ); ?></strong></label>
			<input 
				type="text" 
				name="<?php echo \esc_attr( $title_name ); ?>" 
				value="<?php echo \esc_attr( $title_value ); ?>"
				placeholder="<?php \esc_attr_e( 'Ex: 10-Hour Essaouira Trip from Marrakech', 'transfertmarrakech' ); ?>"
				class="widefat"
			>
		</p>
		
		<!-- Places répétables -->
		<p>
			<label><strong><?php \esc_html_e( 'Places / Étapes', 'transfertmarrakech' ); ?></strong></label>
			<br>
			<small><?php \esc_html_e( 'Ajoutez les différentes étapes de l\'itinéraire avec leur heure, titre et description', 'transfertmarrakech' ); ?></small>
		</p>
		<div class="tm-itinerary-places" data-field-name="<?php echo \esc_attr( $places_name ); ?>">
			<?php foreach ( $places as $index => $place ) : ?>
				<div class="tm-itinerary-place-row" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">
					<div style="display: flex; gap: 10px; margin-bottom: 10px;">
						<div style="flex: 0 0 120px;">
							<label><?php \esc_html_e( 'Heure', 'transfertmarrakech' ); ?></label>
							<input 
								type="text" 
								name="<?php echo \esc_attr( $places_name ); ?>[<?php echo \esc_attr( $index ); ?>][time]" 
								value="<?php echo \esc_attr( $place['time'] ?? '' ); ?>"
								placeholder="<?php \esc_attr_e( 'Ex: 08:00', 'transfertmarrakech' ); ?>"
								class="widefat"
							>
						</div>
						<div style="flex: 1;">
							<label><?php \esc_html_e( 'Titre de la place', 'transfertmarrakech' ); ?></label>
							<input 
								type="text" 
								name="<?php echo \esc_attr( $places_name ); ?>[<?php echo \esc_attr( $index ); ?>][title]" 
								value="<?php echo \esc_attr( $place['title'] ?? '' ); ?>"
								placeholder="<?php \esc_attr_e( 'Ex: Pick-up from your hotel', 'transfertmarrakech' ); ?>"
								class="widefat"
							>
						</div>
						<div>
							<button type="button" class="button tm-remove-place" style="margin-top: 20px;"><?php \esc_html_e( 'Supprimer', 'transfertmarrakech' ); ?></button>
						</div>
					</div>
					<div>
						<label><?php \esc_html_e( 'Description', 'transfertmarrakech' ); ?></label>
						<textarea 
							name="<?php echo \esc_attr( $places_name ); ?>[<?php echo \esc_attr( $index ); ?>][description]" 
							rows="3"
							class="widefat"
							placeholder="<?php \esc_attr_e( 'Description détaillée de cette étape...', 'transfertmarrakech' ); ?>"
						><?php echo \esc_textarea( $place['description'] ?? '' ); ?></textarea>
					</div>
				</div>
			<?php endforeach; ?>
			<button type="button" class="button tm-add-place"><?php \esc_html_e( '+ Ajouter une place', 'transfertmarrakech' ); ?></button>
		</div>
		<script>
		(function($) {
			$(document).ready(function() {
				var $container = $('.tm-itinerary-places[data-field-name="<?php echo \esc_js( $places_name ); ?>"]');
				var placeIndex = <?php echo count( $places ); ?>;
				
				// Ajouter une nouvelle place
				$container.on('click', '.tm-add-place', function(e) {
					e.preventDefault();
					var $row = $container.find('.tm-itinerary-place-row').first().clone();
					$row.find('input, textarea').each(function() {
						var name = $(this).attr('name');
						if (name) {
							$(this).attr('name', name.replace(/\[\d+\]/, '[' + placeIndex + ']'));
							$(this).val('');
						}
					});
					$container.find('.tm-add-place').before($row);
					placeIndex++;
				});
				
				// Supprimer une place
				$container.on('click', '.tm-remove-place', function(e) {
					e.preventDefault();
					if ($container.find('.tm-itinerary-place-row').length > 1) {
						$(this).closest('.tm-itinerary-place-row').remove();
					} else {
						alert('<?php echo \esc_js( __( 'Vous devez avoir au moins une place.', 'transfertmarrakech' ) ); ?>');
					}
				});
			});
		})(jQuery);
		</script>
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

