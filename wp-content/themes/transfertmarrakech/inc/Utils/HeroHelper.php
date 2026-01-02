<?php
/**
 * Helper pour récupérer les données du Hero
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

namespace TM\Utils;

use TM\Repository\PostRepository;

/**
 * Classe helper pour le Hero
 */
class HeroHelper {
	
	/**
	 * Récupère le post à afficher dans le Hero
	 * Retourne le post le plus récent avec la meta 'tm_show_in_hero' = '1'
	 * 
	 * @return WP_Post|null
	 */
	public static function get_hero_post() {
		$args = [
			'post_type'      => 'post',
			'posts_per_page' => 1,
			'post_status'    => 'publish',
			'meta_query'     => [
				[
					'key'   => 'tm_show_in_hero',
					'value' => '1',
					'compare' => '=',
				],
			],
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true, // Optimisation : ne compte pas le total
			'update_post_meta_cache' => true, // Optimisation : charge les meta en une fois
		];
		
		$query = new \WP_Query( $args );
		
		if ( $query->have_posts() ) {
			$post = $query->posts[0];
			\wp_reset_postdata();
			return $post;
		}
		
		\wp_reset_postdata();
		return null;
	}
	
	/**
	 * Récupère le titre du Hero (titre du post)
	 * Optimisé pour utiliser la propriété de l'objet directement
	 * 
	 * @param \WP_Post $post Post object
	 * @return string
	 */
	public static function get_hero_title( \WP_Post $post ): string {
		return \TM\Utils\MetaHelper::get_post_title( $post );
	}
	
	/**
	 * Récupère l'URL de la vidéo YouTube pour le Hero
	 * 
	 * @param WP_Post $post Post object
	 * @return string
	 */
	public static function get_hero_video_url( $post ): string {
		$video_url = \get_post_meta( $post->ID, 'tm_hero_video_url', true );
		
		if ( empty( $video_url ) ) {
			return '';
		}
		
		// Convertit l'URL YouTube en format embed si nécessaire
		return self::convert_youtube_url_to_embed( $video_url );
	}
	
	/**
	 * Convertit une URL YouTube en URL embed
	 * 
	 * @param string $url URL YouTube
	 * @return string URL embed
	 */
	private static function convert_youtube_url_to_embed( string $url ): string {
		// Extrait l'ID de la vidéo depuis différentes formats d'URL
		$video_id = '';
		
		// Format: https://www.youtube.com/watch?v=VIDEO_ID
		if ( \preg_match( '/youtube\.com\/watch\?v=([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
			$video_id = $matches[1];
		}
		// Format: https://youtu.be/VIDEO_ID
		elseif ( \preg_match( '/youtu\.be\/([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
			$video_id = $matches[1];
		}
		// Format: https://www.youtube.com/embed/VIDEO_ID ou youtube-nocookie.com/embed/VIDEO_ID
		elseif ( \preg_match( '/youtube(?:-nocookie)?\.com\/embed\/([a-zA-Z0-9_-]+)/', $url, $matches ) ) {
			$video_id = $matches[1];
		}
		
		if ( empty( $video_id ) ) {
			return $url; // Retourne l'URL originale si on ne peut pas extraire l'ID
		}
		
		// Retourne l'URL embed avec les paramètres optimisés pour mobile et desktop
		// playsinline=1 : essentiel pour iOS/mobile
		// controls=0 : masque les contrôles pour un fond vidéo
		// fs=0 : désactive le plein écran
		// rel=0 : ne montre pas de vidéos suggérées
		return \sprintf( 
			'https://www.youtube-nocookie.com/embed/%s?autoplay=1&mute=1&loop=1&playlist=%s&controls=0&playsinline=1&rel=0&modestbranding=1&iv_load_policy=3&cc_load_policy=0&fs=0',
			$video_id,
			$video_id
		);
	}
}

