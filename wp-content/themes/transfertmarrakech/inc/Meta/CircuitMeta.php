<?php
/**
 * Meta Box pour les Circuits
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Meta;

use TM\Core\Constants;

/**
 * Classe pour gérer les meta boxes des circuits
 */
class CircuitMeta extends MetaBox {
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		parent::__construct(
			'tm_circuit_meta',
			__( 'Informations du circuit', 'transfertmarrakech' ),
			Constants::POST_TYPE_CIRCUIT
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
		
		// Utilise le helper pour récupérer toutes les meta en une fois (plus efficace)
		// Note: We'll need to add get_circuit_meta method to MetaHelper later
		$meta = $this->get_circuit_meta( $post->ID );
		
		$location          = $meta[ Constants::META_CIRCUIT_LOCATION ] ?? '';
		$duration_days     = $meta[ Constants::META_CIRCUIT_DURATION_DAYS ] ?? '';
		$highlights        = $meta[ Constants::META_CIRCUIT_HIGHLIGHTS ] ?? '';
		$pickup_info       = $meta[ Constants::META_CIRCUIT_PICKUP_INFO ] ?? '';
		$meeting_point     = $meta[ Constants::META_CIRCUIT_MEETING_POINT ] ?? '';
		$difficulty        = $meta[ Constants::META_CIRCUIT_DIFFICULTY ] ?? '';
		$languages         = $meta[ Constants::META_CIRCUIT_LANGUAGES ] ?? [];
		$tags              = $meta[ Constants::META_CIRCUIT_TAGS ] ?? [];
		$itinerary_days    = $meta[ Constants::META_CIRCUIT_ITINERARY_DAYS ] ?? [];
		$included          = $meta[ Constants::META_CIRCUIT_INCLUDED ] ?? '';
		$excluded          = $meta[ Constants::META_CIRCUIT_EXCLUDED ] ?? '';
		$not_suitable      = $meta[ Constants::META_CIRCUIT_NOT_SUITABLE ] ?? '';
		$important_info    = $meta[ Constants::META_CIRCUIT_IMPORTANT_INFO ] ?? '';
		$what_to_bring     = $meta[ Constants::META_CIRCUIT_WHAT_TO_BRING ] ?? '';
		$not_allowed       = $meta[ Constants::META_CIRCUIT_NOT_ALLOWED ] ?? '';
		$know_before_go    = $meta[ Constants::META_CIRCUIT_KNOW_BEFORE_GO ] ?? '';
		$cancellation      = $meta[ Constants::META_CIRCUIT_CANCELLATION ] ?? '';
		$price_tiers       = $meta[ Constants::META_CIRCUIT_PRICE_TIERS ] ?? [];
		$vehicle_ids       = $meta[ Constants::META_CIRCUIT_VEHICLES ] ?? [];
		
		// Localisation
		$this->text_field( Constants::META_CIRCUIT_LOCATION, __( 'Localisation', 'transfertmarrakech' ), $location, __( 'Ex: Marrakech', 'transfertmarrakech' ) );
		
		// Durée en jours
		$this->number_field( Constants::META_CIRCUIT_DURATION_DAYS, __( 'Durée (nombre de jours)', 'transfertmarrakech' ), $duration_days );
		
		// Points forts (Highlights)
		$this->textarea_field( Constants::META_CIRCUIT_HIGHLIGHTS, __( 'Highlights (une ligne par point fort)', 'transfertmarrakech' ), $highlights, 5 );
		
		// Informations de prise en charge (Pickup Info)
		$this->textarea_field( Constants::META_CIRCUIT_PICKUP_INFO, __( 'Pickup Information', 'transfertmarrakech' ), $pickup_info, 3 );
		
		// Point de rendez-vous (Meeting Point)
		$this->text_field( Constants::META_CIRCUIT_MEETING_POINT, __( 'Meeting Point', 'transfertmarrakech' ), $meeting_point, __( 'Ex: Marrakech, Maroc', 'transfertmarrakech' ) );
		
		// Difficulté
		$difficulty_options = [
			'easy'   => __( 'Easy', 'transfertmarrakech' ),
			'medium' => __( 'Medium', 'transfertmarrakech' ),
			'hard'   => __( 'Hard', 'transfertmarrakech' ),
		];
		$this->select_field( Constants::META_CIRCUIT_DIFFICULTY, __( 'Difficulté', 'transfertmarrakech' ), $difficulty_options, $difficulty );
		
		// Langues (multi-select)
		$language_options = [
			'english'   => __( 'English', 'transfertmarrakech' ),
			'french'    => __( 'French', 'transfertmarrakech' ),
			'spanish'   => __( 'Spanish', 'transfertmarrakech' ),
			'portuguese' => __( 'Portuguese', 'transfertmarrakech' ),
			'arabic'    => __( 'Arabic', 'transfertmarrakech' ),
			'german'    => __( 'German', 'transfertmarrakech' ),
			'italian'   => __( 'Italian', 'transfertmarrakech' ),
			'slovenian' => __( 'Slovenian', 'transfertmarrakech' ),
			'dutch'     => __( 'Dutch', 'transfertmarrakech' ),
		];
		$this->multi_checkbox_field( Constants::META_CIRCUIT_LANGUAGES, __( 'Langues disponibles', 'transfertmarrakech' ), $language_options, $languages );
		
		// Tags/Catégories (multi-select)
		$tag_options = [
			'photography'      => __( 'Photography', 'transfertmarrakech' ),
			'historical'       => __( 'Historical', 'transfertmarrakech' ),
			'sightseeing'      => __( 'Sightseeing', 'transfertmarrakech' ),
			'adventure'        => __( 'Adventure', 'transfertmarrakech' ),
			'adventure sports' => __( 'Adventure Sports', 'transfertmarrakech' ),
			'Paragliding'      => __( 'Paragliding', 'transfertmarrakech' ),
			'ballooning'       => __( 'Ballooning', 'transfertmarrakech' ),
			'architectural'    => __( 'Architectural', 'transfertmarrakech' ),
			'cultural'         => __( 'Cultural', 'transfertmarrakech' ),
			'nature'           => __( 'Nature', 'transfertmarrakech' ),
			'gastronomical'    => __( 'Gastronomical', 'transfertmarrakech' ),
			'Desert'           => __( 'Desert', 'transfertmarrakech' ),
			'atv'              => __( 'ATV', 'transfertmarrakech' ),
		];
		$this->multi_checkbox_field( Constants::META_CIRCUIT_TAGS, __( 'Tags/Catégories', 'transfertmarrakech' ), $tag_options, $tags );
		
		// Itinéraire par jours
		$this->itinerary_days_field( Constants::META_CIRCUIT_ITINERARY_DAYS, __( 'Itinerary by Days', 'transfertmarrakech' ), $itinerary_days );
		
		// Inclus (What's Included)
		$this->textarea_field( Constants::META_CIRCUIT_INCLUDED, __( 'What\'s Included (une ligne par item)', 'transfertmarrakech' ), $included, 5 );
		
		// Exclus (What's Excluded)
		$this->textarea_field( Constants::META_CIRCUIT_EXCLUDED, __( 'What\'s Excluded (une ligne par item)', 'transfertmarrakech' ), $excluded, 5 );
		
		// Not Suitable For
		$this->textarea_field( Constants::META_CIRCUIT_NOT_SUITABLE, __( 'Not Suitable For (une ligne par item)', 'transfertmarrakech' ), $not_suitable, 3 );
		
		// Important Information
		$this->textarea_field( Constants::META_CIRCUIT_IMPORTANT_INFO, __( 'Important Information', 'transfertmarrakech' ), $important_info, 5 );
		
		// What to Bring
		$this->textarea_field( Constants::META_CIRCUIT_WHAT_TO_BRING, __( 'What to Bring (une ligne par item)', 'transfertmarrakech' ), $what_to_bring, 5 );
		
		// Not Allowed
		$this->textarea_field( Constants::META_CIRCUIT_NOT_ALLOWED, __( 'Not Allowed (une ligne par item)', 'transfertmarrakech' ), $not_allowed, 3 );
		
		// Know Before You Go
		$this->textarea_field( Constants::META_CIRCUIT_KNOW_BEFORE_GO, __( 'Know Before You Go', 'transfertmarrakech' ), $know_before_go, 5 );
		
		// Politique d'annulation (Cancellation Policy)
		$this->textarea_field( Constants::META_CIRCUIT_CANCELLATION, __( 'Cancellation Policy', 'transfertmarrakech' ), $cancellation, 3 );
		
		// Prix par nombre de personnes
		$this->price_tiers_field( Constants::META_CIRCUIT_PRICE_TIERS, __( 'Prix par nombre de personnes', 'transfertmarrakech' ), $price_tiers );
		
		// Véhicules associés
		$this->post_select_field( Constants::META_CIRCUIT_VEHICLES, __( 'Véhicules disponibles', 'transfertmarrakech' ), Constants::POST_TYPE_VEHICLE, $vehicle_ids );
	}
	
	/**
	 * Affiche un champ pour l'itinéraire par jours avec étapes, accommodations, repas, transport
	 * 
	 * @param string $name  Nom du champ
	 * @param string $label Label du champ
	 * @param array  $days  Valeur des jours (array)
	 * @return void
	 */
	protected function itinerary_days_field( string $name, string $label, array $days = [] ): void {
		$days = is_array( $days ) && ! empty( $days ) ? $days : [ [
			'day_title'       => '',
			'steps'           => [ [ 'title' => '', 'description' => '' ] ],
			'accommodations'  => '',
			'meals'           => '',
			'transportation'  => '',
		] ];
		?>
		<p>
			<strong><?php echo \esc_html( $label ); ?></strong>
			<br>
			<small><?php \esc_html_e( 'Ajoutez les jours du circuit avec leurs étapes, accommodations, repas et transport', 'transfertmarrakech' ); ?></small>
		</p>
		<div class="tm-itinerary-days" data-field-name="<?php echo \esc_attr( $name ); ?>">
			<?php foreach ( $days as $day_index => $day ) : ?>
				<div class="tm-itinerary-day-row" style="border: 2px solid #ddd; padding: 20px; margin-bottom: 20px; background: #f9f9f9;">
					<h3 style="margin-top: 0;"><?php printf( __( 'Day %d', 'transfertmarrakech' ), $day_index + 1 ); ?></h3>
					
					<!-- Day Title (e.g., "Day 1: Marrakech - Kasbah Ait Benhaddou - Ouarzazate - Tinghir") -->
					<p>
						<label><strong><?php \esc_html_e( 'Day Title', 'transfertmarrakech' ); ?></strong></label>
						<input 
							type="text" 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][day_title]" 
							value="<?php echo \esc_attr( $day['day_title'] ?? '' ); ?>"
							placeholder="<?php \esc_attr_e( 'Ex: Day 1: Marrakech - Tinghir', 'transfertmarrakech' ); ?>"
							class="widefat"
						>
					</p>
					
					<!-- Steps -->
					<p>
						<label><strong><?php \esc_html_e( 'Steps / Activities', 'transfertmarrakech' ); ?></strong></label>
						<br>
						<small><?php \esc_html_e( 'Add steps for this day', 'transfertmarrakech' ); ?></small>
					</p>
					<div class="tm-day-steps" data-day-index="<?php echo \esc_attr( $day_index ); ?>" data-field-name="<?php echo \esc_attr( $name ); ?>">
						<?php
						$steps = isset( $day['steps'] ) && is_array( $day['steps'] ) && ! empty( $day['steps'] ) 
							? $day['steps'] 
							: [ [ 'title' => '', 'description' => '' ] ];
						foreach ( $steps as $step_index => $step ) :
						?>
							<div class="tm-day-step-row" style="border: 1px solid #ccc; padding: 15px; margin-bottom: 10px; background: #fff;">
								<div style="display: flex; gap: 10px; margin-bottom: 10px;">
									<div style="flex: 1;">
										<label><?php printf( __( 'Step %d - Title', 'transfertmarrakech' ), $step_index + 1 ); ?></label>
										<input 
											type="text" 
											name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][steps][<?php echo \esc_attr( $step_index ); ?>][title]" 
											value="<?php echo \esc_attr( $step['title'] ?? '' ); ?>"
											placeholder="<?php \esc_attr_e( 'Ex: Depart from Marrakech', 'transfertmarrakech' ); ?>"
											class="widefat"
										>
									</div>
									<div>
										<button type="button" class="button tm-remove-step" style="margin-top: 20px;"><?php \esc_html_e( 'Remove', 'transfertmarrakech' ); ?></button>
									</div>
								</div>
								<div>
									<label><?php \esc_html_e( 'Description', 'transfertmarrakech' ); ?></label>
									<textarea 
										name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][steps][<?php echo \esc_attr( $step_index ); ?>][description]" 
										rows="3"
										class="widefat"
										placeholder="<?php \esc_attr_e( 'Detailed description...', 'transfertmarrakech' ); ?>"
									><?php echo \esc_textarea( $step['description'] ?? '' ); ?></textarea>
								</div>
							</div>
						<?php endforeach; ?>
						<button type="button" class="button tm-add-step" data-day-index="<?php echo \esc_attr( $day_index ); ?>"><?php \esc_html_e( '+ Add Step', 'transfertmarrakech' ); ?></button>
					</div>
					
					<!-- Accommodations -->
					<p>
						<label><strong><?php \esc_html_e( 'Accommodations', 'transfertmarrakech' ); ?></strong></label>
						<textarea 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][accommodations]" 
							rows="2"
							class="widefat"
							placeholder="<?php \esc_attr_e( 'Ex: Hotel, Tinghir', 'transfertmarrakech' ); ?>"
						><?php echo \esc_textarea( $day['accommodations'] ?? '' ); ?></textarea>
					</p>
					
					<!-- Meals -->
					<p>
						<label><strong><?php \esc_html_e( 'Meals', 'transfertmarrakech' ); ?></strong></label>
						<textarea 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][meals]" 
							rows="2"
							class="widefat"
							placeholder="<?php \esc_attr_e( 'Ex: Dinner', 'transfertmarrakech' ); ?>"
						><?php echo \esc_textarea( $day['meals'] ?? '' ); ?></textarea>
					</p>
					
					<!-- Transportation -->
					<p>
						<label><strong><?php \esc_html_e( 'Transportation', 'transfertmarrakech' ); ?></strong></label>
						<textarea 
							name="<?php echo \esc_attr( $name ); ?>[<?php echo \esc_attr( $day_index ); ?>][transportation]" 
							rows="2"
							class="widefat"
							placeholder="<?php \esc_attr_e( 'Ex: Air-conditioned minibus', 'transfertmarrakech' ); ?>"
						><?php echo \esc_textarea( $day['transportation'] ?? '' ); ?></textarea>
					</p>
					
					<button type="button" class="button tm-remove-day"><?php \esc_html_e( 'Remove Day', 'transfertmarrakech' ); ?></button>
				</div>
			<?php endforeach; ?>
			<button type="button" class="button tm-add-day"><?php \esc_html_e( '+ Add Day', 'transfertmarrakech' ); ?></button>
		</div>
		<script>
		(function($) {
			$(document).ready(function() {
				var $container = $('.tm-itinerary-days[data-field-name="<?php echo \esc_js( $name ); ?>"]');
				var dayIndex = <?php echo count( $days ); ?>;
				
				// Add new day
				$container.on('click', '.tm-add-day', function(e) {
					e.preventDefault();
					var $dayRow = $container.find('.tm-itinerary-day-row').first().clone();
					// Update all field names in the cloned row
					$dayRow.find('input, textarea, select').each(function() {
						var name = $(this).attr('name');
						if (name) {
							// Replace day index in name
							$(this).attr('name', name.replace(/\[\d+\]/, '[' + dayIndex + ']'));
							// Clear values
							if ($(this).is(':checkbox, :radio')) {
								$(this).prop('checked', false);
							} else {
								$(this).val('');
							}
						}
					});
					// Update day number in title
					$dayRow.find('h3').text('<?php echo \esc_js( __( 'Day', 'transfertmarrakech' ) ); ?> ' + (dayIndex + 1));
					// Update data-day-index for steps
					$dayRow.find('.tm-day-steps').attr('data-day-index', dayIndex);
					// Reset steps to one empty step
					var $stepsContainer = $dayRow.find('.tm-day-steps');
					$stepsContainer.find('.tm-day-step-row').slice(1).remove();
					$stepsContainer.find('input, textarea').val('');
					// Update step indices
					$stepsContainer.find('.tm-day-step-row').each(function(stepIdx) {
						$(this).find('input, textarea').each(function() {
							var stepName = $(this).attr('name');
							if (stepName) {
								$(this).attr('name', stepName.replace(/\[steps\]\[\d+\]/, '[steps][0]'));
							}
						});
					});
					$container.find('.tm-add-day').before($dayRow);
					dayIndex++;
				});
				
				// Remove day
				$container.on('click', '.tm-remove-day', function(e) {
					e.preventDefault();
					if ($container.find('.tm-itinerary-day-row').length > 1) {
						$(this).closest('.tm-itinerary-day-row').remove();
					} else {
						alert('<?php echo \esc_js( __( 'Vous devez avoir au moins un jour.', 'transfertmarrakech' ) ); ?>');
					}
				});
				
				// Add step to a specific day
				$container.on('click', '.tm-add-step', function(e) {
					e.preventDefault();
					var dayIdx = $(this).data('day-index');
					var $stepsContainer = $(this).closest('.tm-day-steps');
					var stepIndex = $stepsContainer.find('.tm-day-step-row').length;
					var $stepRow = $stepsContainer.find('.tm-day-step-row').first().clone();
					$stepRow.find('input, textarea').each(function() {
						var stepName = $(this).attr('name');
						if (stepName) {
							$(this).attr('name', stepName.replace(/\[steps\]\[\d+\]/, '[steps][' + stepIndex + ']'));
							$(this).val('');
						}
					});
					$stepRow.find('label').first().text('<?php echo \esc_js( __( 'Step', 'transfertmarrakech' ) ); ?> ' + (stepIndex + 1) + ' - <?php echo \esc_js( __( 'Title', 'transfertmarrakech' ) ); ?>');
					$(this).before($stepRow);
				});
				
				// Remove step
				$container.on('click', '.tm-remove-step', function(e) {
					e.preventDefault();
					var $stepsContainer = $(this).closest('.tm-day-steps');
					if ($stepsContainer.find('.tm-day-step-row').length > 1) {
						$(this).closest('.tm-day-step-row').remove();
						// Renumber remaining steps
						$stepsContainer.find('.tm-day-step-row').each(function(stepIdx) {
							$(this).find('input, textarea').each(function() {
								var stepName = $(this).attr('name');
								if (stepName) {
									$(this).attr('name', stepName.replace(/\[steps\]\[\d+\]/, '[steps][' + stepIdx + ']'));
								}
							});
							$(this).find('label').first().text('<?php echo \esc_js( __( 'Step', 'transfertmarrakech' ) ); ?> ' + (stepIdx + 1) + ' - <?php echo \esc_js( __( 'Title', 'transfertmarrakech' ) ); ?>');
						});
					} else {
						alert('<?php echo \esc_js( __( 'Vous devez avoir au moins une étape.', 'transfertmarrakech' ) ); ?>');
					}
				});
			});
		})(jQuery);
		</script>
		<?php
	}
	
	/**
	 * Get circuit meta data (temporary helper until MetaHelper is updated)
	 * 
	 * @param int $circuit_id Circuit ID
	 * @return array
	 */
	private function get_circuit_meta( int $circuit_id ): array {
		return [
			Constants::META_CIRCUIT_LOCATION          => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_LOCATION, true ),
			Constants::META_CIRCUIT_DURATION_DAYS     => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_DURATION_DAYS, true ),
			Constants::META_CIRCUIT_HIGHLIGHTS        => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_HIGHLIGHTS, true ),
			Constants::META_CIRCUIT_PICKUP_INFO       => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_PICKUP_INFO, true ),
			Constants::META_CIRCUIT_MEETING_POINT     => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_MEETING_POINT, true ),
			Constants::META_CIRCUIT_DIFFICULTY        => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_DIFFICULTY, true ),
			Constants::META_CIRCUIT_LANGUAGES         => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_LANGUAGES, true ) ?: [],
			Constants::META_CIRCUIT_TAGS              => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_TAGS, true ) ?: [],
			Constants::META_CIRCUIT_ITINERARY_DAYS    => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_ITINERARY_DAYS, true ) ?: [],
			Constants::META_CIRCUIT_INCLUDED          => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_INCLUDED, true ),
			Constants::META_CIRCUIT_EXCLUDED          => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_EXCLUDED, true ),
			Constants::META_CIRCUIT_NOT_SUITABLE      => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_NOT_SUITABLE, true ),
			Constants::META_CIRCUIT_IMPORTANT_INFO    => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_IMPORTANT_INFO, true ),
			Constants::META_CIRCUIT_WHAT_TO_BRING     => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_WHAT_TO_BRING, true ),
			Constants::META_CIRCUIT_NOT_ALLOWED       => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_NOT_ALLOWED, true ),
			Constants::META_CIRCUIT_KNOW_BEFORE_GO    => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_KNOW_BEFORE_GO, true ),
			Constants::META_CIRCUIT_CANCELLATION      => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_CANCELLATION, true ),
			Constants::META_CIRCUIT_PRICE_TIERS       => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_PRICE_TIERS, true ) ?: [],
			Constants::META_CIRCUIT_VEHICLES          => \get_post_meta( $circuit_id, Constants::META_CIRCUIT_VEHICLES, true ) ?: [],
		];
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
		
		// Localisation
		if ( isset( $_POST[ Constants::META_CIRCUIT_LOCATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_LOCATION, \sanitize_text_field( $_POST[ Constants::META_CIRCUIT_LOCATION ] ) );
		}
		
		// Durée en jours
		if ( isset( $_POST[ Constants::META_CIRCUIT_DURATION_DAYS ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_DURATION_DAYS, \absint( $_POST[ Constants::META_CIRCUIT_DURATION_DAYS ] ) );
		}
		
		// Highlights
		if ( isset( $_POST[ Constants::META_CIRCUIT_HIGHLIGHTS ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_HIGHLIGHTS, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_HIGHLIGHTS ] ) );
		}
		
		// Pickup Info
		if ( isset( $_POST[ Constants::META_CIRCUIT_PICKUP_INFO ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_PICKUP_INFO, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_PICKUP_INFO ] ) );
		}
		
		// Meeting Point
		if ( isset( $_POST[ Constants::META_CIRCUIT_MEETING_POINT ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_MEETING_POINT, \sanitize_text_field( $_POST[ Constants::META_CIRCUIT_MEETING_POINT ] ) );
		}
		
		// Difficulté
		if ( isset( $_POST[ Constants::META_CIRCUIT_DIFFICULTY ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_DIFFICULTY, \sanitize_text_field( $_POST[ Constants::META_CIRCUIT_DIFFICULTY ] ) );
		}
		
		// Langues
		if ( isset( $_POST[ Constants::META_CIRCUIT_LANGUAGES ] ) && is_array( $_POST[ Constants::META_CIRCUIT_LANGUAGES ] ) ) {
			$languages = array_map( 'sanitize_text_field', $_POST[ Constants::META_CIRCUIT_LANGUAGES ] );
			$languages = array_values( array_filter( $languages, function( $item ) {
				return ! empty( $item ) && is_string( $item );
			} ) );
			\update_post_meta( $post_id, Constants::META_CIRCUIT_LANGUAGES, $languages );
		} else {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_LANGUAGES, [] );
		}
		
		// Tags
		if ( isset( $_POST[ Constants::META_CIRCUIT_TAGS ] ) && is_array( $_POST[ Constants::META_CIRCUIT_TAGS ] ) ) {
			$tags = array_map( 'sanitize_text_field', $_POST[ Constants::META_CIRCUIT_TAGS ] );
			// Filtre les valeurs vides (chaînes vides, null, false)
			$tags = array_values( array_filter( $tags, function( $item ) {
				return ! empty( $item ) && is_string( $item );
			} ) );
			\update_post_meta( $post_id, Constants::META_CIRCUIT_TAGS, $tags );
		} else {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_TAGS, [] );
		}
		
		// Itinéraire par jours
		if ( isset( $_POST[ Constants::META_CIRCUIT_ITINERARY_DAYS ] ) && is_array( $_POST[ Constants::META_CIRCUIT_ITINERARY_DAYS ] ) ) {
			$days = [];
			foreach ( $_POST[ Constants::META_CIRCUIT_ITINERARY_DAYS ] as $day ) {
				if ( ! is_array( $day ) ) {
					continue;
				}
				
				$day_title       = isset( $day['day_title'] ) ? \sanitize_text_field( $day['day_title'] ) : '';
				$accommodations  = isset( $day['accommodations'] ) ? \sanitize_textarea_field( $day['accommodations'] ) : '';
				$meals           = isset( $day['meals'] ) ? \sanitize_textarea_field( $day['meals'] ) : '';
				$transportation  = isset( $day['transportation'] ) ? \sanitize_textarea_field( $day['transportation'] ) : '';
				
				// Steps
				$steps = [];
				if ( isset( $day['steps'] ) && is_array( $day['steps'] ) ) {
					foreach ( $day['steps'] as $step ) {
						if ( ! is_array( $step ) ) {
							continue;
						}
						$step_title = isset( $step['title'] ) ? \sanitize_text_field( $step['title'] ) : '';
						$step_description = isset( $step['description'] ) ? \sanitize_textarea_field( $step['description'] ) : '';
						
						if ( ! empty( $step_title ) || ! empty( $step_description ) ) {
							$steps[] = [
								'title'       => $step_title,
								'description' => $step_description,
							];
						}
					}
				}
				
				// Keep day if it has at least a title or steps
				if ( ! empty( $day_title ) || ! empty( $steps ) ) {
					$days[] = [
						'day_title'      => $day_title,
						'steps'          => $steps,
						'accommodations' => $accommodations,
						'meals'          => $meals,
						'transportation' => $transportation,
					];
				}
			}
			\update_post_meta( $post_id, Constants::META_CIRCUIT_ITINERARY_DAYS, $days );
		} else {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_ITINERARY_DAYS, [] );
		}
		
		// Inclus
		if ( isset( $_POST[ Constants::META_CIRCUIT_INCLUDED ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_INCLUDED, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_INCLUDED ] ) );
		}
		
		// Exclus
		if ( isset( $_POST[ Constants::META_CIRCUIT_EXCLUDED ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_EXCLUDED, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_EXCLUDED ] ) );
		}
		
		// Not Suitable
		if ( isset( $_POST[ Constants::META_CIRCUIT_NOT_SUITABLE ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_NOT_SUITABLE, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_NOT_SUITABLE ] ) );
		}
		
		// Important Info
		if ( isset( $_POST[ Constants::META_CIRCUIT_IMPORTANT_INFO ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_IMPORTANT_INFO, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_IMPORTANT_INFO ] ) );
		}
		
		// What to Bring
		if ( isset( $_POST[ Constants::META_CIRCUIT_WHAT_TO_BRING ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_WHAT_TO_BRING, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_WHAT_TO_BRING ] ) );
		}
		
		// Not Allowed
		if ( isset( $_POST[ Constants::META_CIRCUIT_NOT_ALLOWED ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_NOT_ALLOWED, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_NOT_ALLOWED ] ) );
		}
		
		// Know Before Go
		if ( isset( $_POST[ Constants::META_CIRCUIT_KNOW_BEFORE_GO ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_KNOW_BEFORE_GO, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_KNOW_BEFORE_GO ] ) );
		}
		
		// Cancellation
		if ( isset( $_POST[ Constants::META_CIRCUIT_CANCELLATION ] ) ) {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_CANCELLATION, \sanitize_textarea_field( $_POST[ Constants::META_CIRCUIT_CANCELLATION ] ) );
		}
		
		// Prix par nombre de personnes
		if ( isset( $_POST[ Constants::META_CIRCUIT_PRICE_TIERS ] ) && is_array( $_POST[ Constants::META_CIRCUIT_PRICE_TIERS ] ) ) {
			$tiers = [];
			foreach ( $_POST[ Constants::META_CIRCUIT_PRICE_TIERS ] as $tier ) {
				if ( ! is_array( $tier ) ) {
					continue;
				}
				
				$min_persons = isset( $tier['min_persons'] ) ? \absint( $tier['min_persons'] ) : 0;
				$max_persons = isset( $tier['max_persons'] ) ? \absint( $tier['max_persons'] ) : 0;
				$price_value = isset( $tier['price'] ) ? \TM\Utils\MetaHelper::format_price_for_save( $tier['price'] ) : '';
				$tier_type = isset( $tier['type'] ) ? \sanitize_text_field( $tier['type'] ) : '';
				
				if ( $min_persons > 0 && $max_persons >= $min_persons && ! empty( $price_value ) ) {
					$tiers[] = [
						'min_persons' => $min_persons,
						'max_persons' => $max_persons,
						'price'       => $price_value,
						'type'        => $tier_type,
					];
				}
			}
			\update_post_meta( $post_id, Constants::META_CIRCUIT_PRICE_TIERS, $tiers );
		} else {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_PRICE_TIERS, [] );
		}
		
		// Véhicules associés
		if ( isset( $_POST[ Constants::META_CIRCUIT_VEHICLES ] ) && is_array( $_POST[ Constants::META_CIRCUIT_VEHICLES ] ) ) {
			$vehicle_ids = array_filter( 
				array_map( 'absint', $_POST[ Constants::META_CIRCUIT_VEHICLES ] ),
				function( $id ) {
					return $id > 0;
				}
			);
			$vehicle_ids = array_values( $vehicle_ids );
			\update_post_meta( $post_id, Constants::META_CIRCUIT_VEHICLES, $vehicle_ids );
		} else {
			\update_post_meta( $post_id, Constants::META_CIRCUIT_VEHICLES, [] );
		}
	}
}

