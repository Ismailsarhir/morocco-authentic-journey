<?php
/**
 * REST Controller pour les Véhicules
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\REST;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use TM\Core\Constants;
use TM\Repository\PostRepository;
use TM\Utils\Sanitizer;

/**
 * Contrôleur REST pour les véhicules
 */
class VehicleRestController extends WP_REST_Controller {
	
	/**
	 * Namespace de l'API
	 * 
	 * @var string
	 */
	protected $namespace = 'tm/v1';
	
	/**
	 * Base de la route
	 * 
	 * @var string
	 */
	protected $rest_base = Constants::POST_TYPE_VEHICLE;
	
	/**
	 * Repository
	 * 
	 * @var PostRepository
	 */
	protected PostRepository $repository;
	
	/**
	 * Constructeur
	 */
	public function __construct() {
		// Utilise l'instance partagée du repository (optimisation)
		$this->repository = PostRepository::get_instance();
	}
	
	/**
	 * Enregistre les routes REST
	 * 
	 * @return void
	 */
	public function register_routes(): void {
		// Route pour lister les véhicules
		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
					'args'                => $this->get_collection_params(),
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
				],
			]
		);
		
		// Route pour un véhicule spécifique
		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
					'args'                => [
						'id' => [
							'description' => __( 'ID unique du véhicule', 'transfertmarrakech' ),
							'type'        => 'integer',
							'required'    => true,
						],
					],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
				],
				[
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
				],
			]
		);
	}
	
	/**
	 * Vérifie les permissions pour lister les véhicules
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		return true; // Lecture publique
	}
	
	/**
	 * Récupère la liste des véhicules
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$args = [
			'posts_per_page' => $request->get_param( 'per_page' ) ?? 10,
			'paged'          => $request->get_param( 'page' ) ?? 1,
		];
		
		// Filtre par disponibilité
		if ( $request->get_param( 'available' ) === 'true' ) {
			$args['meta_query'] = [
				[
					'key'     => 'tm_availability',
					'value'   => '1',
					'compare' => '=',
				],
			];
		}
		
		$posts = $this->repository->get_by_args( Constants::POST_TYPE_VEHICLE, $args );
		$data  = [];
		
		foreach ( $posts as $post ) {
			$item = $this->prepare_item_for_response( $post, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}
		
		return new \WP_REST_Response( $data, 200 );
	}
	
	/**
	 * Vérifie les permissions pour récupérer un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function get_item_permissions_check( $request ) {
		return true; // Lecture publique
	}
	
	/**
	 * Récupère un véhicule spécifique
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id   = (int) $request->get_param( 'id' );
		$post = $this->repository->get_by_id( $id );
		
		if ( ! $post || $post->post_type !== Constants::POST_TYPE_VEHICLE ) {
			return new \WP_Error(
				'rest_vehicle_not_found',
				__( 'Véhicule non trouvé', 'transfertmarrakech' ),
				[ 'status' => 404 ]
			);
		}
		
		$item = $this->prepare_item_for_response( $post, $request );
		return new \WP_REST_Response( $item, 200 );
	}
	
	/**
	 * Vérifie les permissions pour créer un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! \current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_cannot_create',
				__( 'Vous n\'avez pas les permissions pour créer un véhicule', 'transfertmarrakech' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		return true;
	}
	
	/**
	 * Crée un nouveau véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$post_data = [
			'post_type'    => Constants::POST_TYPE_VEHICLE,
			'post_title'   => \sanitize_text_field( $request->get_param( 'title' ) ),
			'post_content' => \wp_kses_post( $request->get_param( 'content' ) ),
			'post_status'  => 'publish',
		];
		
		$post_id = \wp_insert_post( $post_data, true );
		
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		
		// Sauvegarde des meta
		$this->update_post_meta( $post_id, $request );
		
		$post = \get_post( $post_id );
		$item = $this->prepare_item_for_response( $post, $request );
		
		return new \WP_REST_Response( $item, 201 );
	}
	
	/**
	 * Vérifie les permissions pour mettre à jour un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$post = \get_post( (int) $request->get_param( 'id' ) );
		
		if ( ! $post ) {
			return new \WP_Error(
				'rest_vehicle_not_found',
				__( 'Véhicule non trouvé', 'transfertmarrakech' ),
				[ 'status' => 404 ]
			);
		}
		
		if ( ! \current_user_can( 'edit_post', $post->ID ) ) {
			return new \WP_Error(
				'rest_cannot_update',
				__( 'Vous n\'avez pas les permissions pour modifier ce véhicule', 'transfertmarrakech' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		
		return true;
	}
	
	/**
	 * Met à jour un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id = (int) $request->get_param( 'id' );
		
		$post_data = [];
		if ( $request->get_param( 'title' ) ) {
			$post_data['post_title'] = \sanitize_text_field( $request->get_param( 'title' ) );
		}
		if ( $request->get_param( 'content' ) ) {
			$post_data['post_content'] = \wp_kses_post( $request->get_param( 'content' ) );
		}
		
		if ( ! empty( $post_data ) ) {
			$post_data['ID'] = $id;
			\wp_update_post( $post_data );
		}
		
		// Sauvegarde des meta
		$this->update_post_meta( $id, $request );
		
		$post = \get_post( $id );
		$item = $this->prepare_item_for_response( $post, $request );
		
		return new \WP_REST_Response( $item, 200 );
	}
	
	/**
	 * Vérifie les permissions pour supprimer un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		$post = \get_post( (int) $request->get_param( 'id' ) );
		
		if ( ! $post ) {
			return new \WP_Error(
				'rest_vehicle_not_found',
				__( 'Véhicule non trouvé', 'transfertmarrakech' ),
				[ 'status' => 404 ]
			);
		}
		
		if ( ! \current_user_can( 'delete_post', $post->ID ) ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'Vous n\'avez pas les permissions pour supprimer ce véhicule', 'transfertmarrakech' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		
		return true;
	}
	
	/**
	 * Supprime un véhicule
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		$id = (int) $request->get_param( 'id' );
		
		$result = \wp_delete_post( $id, true );
		
		if ( ! $result ) {
			return new \WP_Error(
				'rest_cannot_delete',
				__( 'Impossible de supprimer le véhicule', 'transfertmarrakech' ),
				[ 'status' => 500 ]
			);
		}
		
		return new \WP_REST_Response( [ 'deleted' => true ], 200 );
	}
	
	/**
	 * Prépare un post pour la réponse
	 * 
	 * @param WP_Post         $post    Objet post
	 * @param WP_REST_Request $request Requête
	 * @return array
	 */
	public function prepare_item_for_response( $post, $request ): array {
		$data = $this->repository->format_post( $post );
		
		// Utilise MetaHelper pour récupérer toutes les meta en une fois (optimisé)
		$meta = \TM\Utils\MetaHelper::get_vehicle_meta( $post->ID );
		
		$data['meta'] = [
			'vehicle_type'    => $meta['tm_vehicle_type'] ?? '',
			'seats'           => $meta['tm_seats'] ?? 0,
			'baggage_capacity' => $meta['tm_baggage_capacity'] ?? '',
			'gallery'         => $meta['tm_gallery'] ?? [],
			'availability'    => $meta['tm_availability'] ?? false,
			'daily_price'     => $meta['tm_daily_price'] ?? '',
		];
		
		return $data;
	}
	
	/**
	 * Met à jour les meta d'un post
	 * 
	 * @param int             $post_id ID du post
	 * @param WP_REST_Request $request Requête
	 * @return void
	 */
	protected function update_post_meta( int $post_id, $request ): void {
		$meta_fields = [
			'tm_vehicle_type',
			'tm_seats',
			'tm_baggage_capacity',
			'tm_gallery',
			'tm_availability',
			'tm_daily_price',
		];
		
		foreach ( $meta_fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$value = $request->get_param( $field );
				
				// Sanitization selon le type
				if ( $field === 'tm_seats' ) {
					$value = Sanitizer::sanitize_positive_int( $value );
				} elseif ( $field === 'tm_availability' ) {
					$value = Sanitizer::sanitize_boolean( $value );
				} elseif ( $field === 'tm_gallery' ) {
					$value = Sanitizer::sanitize_gallery( $value );
				} elseif ( $field === 'tm_daily_price' ) {
					$value = Sanitizer::sanitize_price( $value );
				} else {
					$value = \sanitize_text_field( $value );
				}
				
				\update_post_meta( $post_id, $field, $value );
			}
		}
	}
	
	/**
	 * Retourne les paramètres de collection
	 * 
	 * @return array
	 */
	public function get_collection_params(): array {
		return [
			'page'     => [
				'description' => __( 'Numéro de page', 'transfertmarrakech' ),
				'type'        => 'integer',
				'default'     => 1,
			],
			'per_page' => [
				'description' => __( 'Nombre d\'éléments par page', 'transfertmarrakech' ),
				'type'        => 'integer',
				'default'     => 10,
			],
			'available' => [
				'description' => __( 'Filtrer par disponibilité', 'transfertmarrakech' ),
				'type'        => 'string',
				'enum'        => [ 'true', 'false' ],
			],
		];
	}
}

