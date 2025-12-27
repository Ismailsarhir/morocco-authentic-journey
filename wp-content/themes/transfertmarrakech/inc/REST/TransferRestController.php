<?php
/**
 * REST Controller pour les Transferts
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
use TM\Repository\PostRepository;

/**
 * Contrôleur REST pour les transferts
 */
class TransferRestController extends WP_REST_Controller {
	
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
	protected $rest_base = 'transferts';
	
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
		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
				],
			]
		);
		
		\register_rest_route(
			$this->namespace,
			'/' . $this->rest_base . '/(?P<id>[\d]+)',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_item' ],
					'permission_callback' => [ $this, 'get_item_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
				],
			]
		);
	}
	
	/**
	 * Vérifie les permissions pour lister les transferts
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool
	 */
	public function get_items_permissions_check( $request ) {
		return true;
	}
	
	/**
	 * Récupère la liste des transferts
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response
	 */
	public function get_items( $request ) {
		$posts = $this->repository->get_by_args( 'transferts' );
		$data  = [];
		
		foreach ( $posts as $post ) {
			$item = $this->prepare_item_for_response( $post, $request );
			$data[] = $this->prepare_response_for_collection( $item );
		}
		
		return new \WP_REST_Response( $data, 200 );
	}
	
	/**
	 * Vérifie les permissions pour récupérer un transfert
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool
	 */
	public function get_item_permissions_check( $request ) {
		return true;
	}
	
	/**
	 * Récupère un transfert spécifique
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$id   = (int) $request->get_param( 'id' );
		$post = $this->repository->get_by_id( $id );
		
		if ( ! $post || $post->post_type !== 'transferts' ) {
			return new \WP_Error(
				'rest_transfer_not_found',
				__( 'Transfert non trouvé', 'transfertmarrakech' ),
				[ 'status' => 404 ]
			);
		}
		
		$item = $this->prepare_item_for_response( $post, $request );
		return new \WP_REST_Response( $item, 200 );
	}
	
	/**
	 * Vérifie les permissions pour créer un transfert
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! \current_user_can( 'edit_posts' ) ) {
			return new \WP_Error(
				'rest_cannot_create',
				__( 'Vous n\'avez pas les permissions', 'transfertmarrakech' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		return true;
	}
	
	/**
	 * Crée un nouveau transfert
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		$post_id = \wp_insert_post( [
			'post_type'    => 'transferts',
			'post_title'   => \sanitize_text_field( $request->get_param( 'title' ) ),
			'post_content' => \wp_kses_post( $request->get_param( 'content' ) ),
			'post_status'  => 'publish',
		], true );
		
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}
		
		$this->update_post_meta( $post_id, $request );
		
		$post = \get_post( $post_id );
		$item = $this->prepare_item_for_response( $post, $request );
		
		return new \WP_REST_Response( $item, 201 );
	}
	
	/**
	 * Vérifie les permissions pour mettre à jour un transfert
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return bool|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		$post = \get_post( (int) $request->get_param( 'id' ) );
		
		if ( ! $post || ! \current_user_can( 'edit_post', $post->ID ) ) {
			return new \WP_Error(
				'rest_cannot_update',
				__( 'Vous n\'avez pas les permissions', 'transfertmarrakech' ),
				[ 'status' => \rest_authorization_required_code() ]
			);
		}
		
		return true;
	}
	
	/**
	 * Met à jour un transfert
	 * 
	 * @param WP_REST_Request $request Requête
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		$id = (int) $request->get_param( 'id' );
		
		if ( $request->get_param( 'title' ) ) {
			\wp_update_post( [
				'ID'         => $id,
				'post_title' => \sanitize_text_field( $request->get_param( 'title' ) ),
			] );
		}
		
		$this->update_post_meta( $id, $request );
		
		$post = \get_post( $id );
		$item = $this->prepare_item_for_response( $post, $request );
		
		return new \WP_REST_Response( $item, 200 );
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
		$meta = \TM\Utils\MetaHelper::get_transfer_meta( $post->ID );
		
		$data['meta'] = [
			'transfer_type'     => $meta['tm_transfer_type'] ?? '',
			'price'             => $meta['tm_price'] ?? '',
			'pickup'            => $meta['tm_pickup'] ?? '',
			'dropoff'           => $meta['tm_dropoff'] ?? '',
			'duration_estimate' => $meta['tm_duration_estimate'] ?? '',
			'description'       => $meta['tm_description'] ?? '',
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
			'tm_transfer_type',
			'tm_price',
			'tm_pickup',
			'tm_dropoff',
			'tm_duration_estimate',
			'tm_description',
		];
		
		foreach ( $meta_fields as $field ) {
			if ( $request->has_param( $field ) ) {
				$value = $request->get_param( $field );
				
				if ( $field === 'tm_price' ) {
					$value = \number_format( \floatval( $value ), 2, '.', '' );
				} elseif ( $field === 'tm_description' ) {
					$value = \sanitize_textarea_field( $value );
				} else {
					$value = \sanitize_text_field( $value );
				}
				
				\update_post_meta( $post_id, $field, $value );
			}
		}
	}
}

