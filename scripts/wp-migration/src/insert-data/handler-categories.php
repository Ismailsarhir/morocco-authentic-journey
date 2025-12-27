<?php

/**
 * Insert WP Category
 *
 * @author Brahim Ibrahimi
 */
namespace Scripts\Classes;

use Scripts\Models\Taxonomy;
use \Scripts\Shared\Logger;
use Scripts\Common\Utils;


class HandlerCategories {
	
	use Logger;

	const ERROR_FILE_PREFIX = '_error';
	const SUCCESS_FILE_PREFIX = '_success';
	/**
	 * Insert WP Category By Old Data.
	 */
	public static function insert_taxonomy(Taxonomy $taxonomy, $taxonomy_type='category', $args = []) {
		$term_id = null;
		$tax_log = '';
		$tax_title = $taxonomy->get_title();
		$tax_slug = $taxonomy->get_slug();
		$tax_slug = !empty($tax_slug) ? $tax_slug : sanitize_title($tax_title);

		$term_args = [];
		
		// term_metas
		$tax_args = $taxonomy->get_tax_args();

		$old_parent_cat_id = !empty($tax_args['term_metas']['old_parent_cat_id']) ? $tax_args['term_metas']['old_parent_cat_id'] : 0;
		$term_metas = !empty($tax_args['term_metas']) ? $tax_args['term_metas'] : [];
		$wpseo_metas = !empty($tax_args['wpseo_metas']) ? $tax_args['wpseo_metas'] : [];
		
		$description = !empty($term_metas['description']) ? $term_metas['description'] : '';
		if(isset($description)) {
			$term_args["description"] = $description;
		}

		$meta_key_old_id = $taxonomy_type.'_old_id';
		if(!empty($tax_args['term_metas']['old_type'])) {
			$meta_key_old_id = $tax_args['term_metas']['old_type'].'_old_id';
		}
		$old_id = $tax_args['term_metas'][$meta_key_old_id];

		$old_path = $taxonomy->get_old_path();
		$by_old_path = !empty($old_path) ? true : false;

		if (!empty($tax_title) && !empty($tax_slug)) {
			$term = self::get_taxonomy_by_meta($taxonomy_type, $meta_key_old_id, $old_id);
			$term_id = !empty($term) ? $term->term_id : 0;
			if (!empty($old_parent_cat_id)) {
				if(!empty($args['parent_meta']))
				{
					$meta_key_old_id = $args['parent_meta'];
				}
				$parent_cat = self::get_taxonomy_by_meta($taxonomy_type, $meta_key_old_id, $old_parent_cat_id);
				if(!empty($parent_cat) && ! is_wp_error( $parent_cat )) {
					$term_args['parent'] = $parent_cat->term_id;
				}
			}


			if(empty($term_id)) {
				$args = array_merge(['taxonomy' => $taxonomy_type], $term_args);
				$slug = wp_unique_term_slug( $tax_slug, (object) $args );
				$term_args['slug'] = $slug;
				
				$term = wp_insert_term($tax_title, $taxonomy_type, $term_args);
				self::update_old_path($term['term_id'], $old_path);

				if(is_array($term) && isset($term['term_id'])) {
					$term_id = $term['term_id'];
					$tax_log = $tax_title.' - created successfully';
					self::registerLog($tax_log, self::SUCCESS_FILE_PREFIX);
				}else {
					$tax_log = $tax_slug." There is a problem !!";
					self::registerLog($tax_log, self::ERROR_FILE_PREFIX);
				}

			} else {
				wp_update_term($term_id, $taxonomy_type, $term_args);
				$tax_log = $tax_title.' - exist';
				self::update_old_path($term_id, $old_path);

				self::registerLog($tax_log, self::SUCCESS_FILE_PREFIX);
			}
		}


		/*if there is no error :D */
		if ( !empty($term_id) && ! is_wp_error( $term_id ) ) {
			self::set_term_metas($term_id, $term_metas);
			self::set_wpseo_metas($term_id, $wpseo_metas, $taxonomy_type);
			if(!empty($tax_args) && !empty($tax_args['term_metas']['tax_logo_img'])){
				static::set_taxonomy_custom_header_image($term_id,$tax_args['term_metas']['tax_logo_img']);
			}
		}


		return $term_id;
	}

	/**
	 * set category metas
	 */
	protected static function set_term_metas($term_id, $term_metas){

		if (!empty($term_metas) && !empty($term_id)) {

			foreach($term_metas as $key => $value) {

				update_term_meta( $term_id, $key, $value );
			}
		}
	}

	/**
	 * set category seo metas
	 */
	protected static function set_wpseo_metas($term_id, $wpseo_metas, $taxonomy_type){

		$wpseo_taxonomy_meta = get_option('wpseo_taxonomy_meta');

		if ($wpseo_taxonomy_meta === false) {
			$wpseo_taxonomy_meta = [$taxonomy_type => []];
		} else {
			$wpseo_taxonomy_meta = maybe_unserialize( $wpseo_taxonomy_meta );
		}

		if(!empty($wpseo_metas['meta_title'])) {
			$wpseo_taxonomy_meta[$taxonomy_type][$term_id]['wpseo_title'] = $wpseo_metas['meta_title'];
		}

		if(!empty($wpseo_metas['meta_desc'])) {
			$wpseo_taxonomy_meta[$taxonomy_type][$term_id]['wpseo_desc'] = $wpseo_metas['meta_desc'];
		}

		update_option( "wpseo_taxonomy_meta", $wpseo_taxonomy_meta, true );
	}

	/**
	 * get category by meta
	 */

	public static function get_taxonomy_by_meta($taxonomy_type, $meta_key, $meta_value) {

		$args = [
			'hide_empty' => false, // also retrieve terms which are not used yet
			'meta_query' => [
			    [
			       'key'       => $meta_key,
			       'value'     => $meta_value,
			       'compare'   => '=',
			       'type'      => 'BINARY'
			    ]
			],
			'taxonomy'  => $taxonomy_type,
		];
		$terms = get_terms( $args );

		return !empty($terms) ? $terms[0] : [];
	}
	
	/**
	 * set image taxonomie
	 */
	public static function set_taxonomy_custom_header_image($term_id,$taxonomy_logo){
		if (!empty($term_id) && !empty($taxonomy_logo)) {
			$option = get_option('custom-header-image', []);

			$url = !empty($taxonomy_logo['url']) ? $taxonomy_logo['url'] : "";
			$title = !empty($taxonomy_logo['name']) ? $taxonomy_logo['name'] : "";
			$description = !empty($taxonomy_logo['description']) ? $taxonomy_logo['description'] : "";
			$tax_img_meta = !empty($taxonomy_logo['tax_img_meta']) ? $taxonomy_logo['tax_img_meta'] : "";
			$is_local = !empty($taxonomy_logo['is_local']) ? $taxonomy_logo['is_local'] : false;

			if(!empty($url) && !empty($tax_img_meta)){
				$attach_id = Utils::upload_image_by_meta($url, $title, $description, false, $tax_img_meta , $url, $is_local);

				if(is_int($attach_id)){
					$img_args = [
						'custom-header-image' => [$attach_id],
					];

					$option[$term_id] = $img_args;
					update_option('custom-header-image',$option);
					if(!empty($taxonomy_logo['meta_name'])){
						update_term_meta(  $term_id, $taxonomy_logo['meta_name'] ,  $attach_id) ;
					}else{
						update_term_meta(  $term_id, 'custom-header-image' ,  $attach_id) ;
					}
				}
			}
		}
	}

	protected static function update_old_path($term_id, $old_path)
	{
	    if(!defined('URL_BY_OLD_PATH') || !URL_BY_OLD_PATH){
	    	return;
	    }

	    if($old_path){
	    	global $wpdb ;
		    $sql = "
				UPDATE `{$wpdb->prefix}terms` SET `old_path` = '$old_path' WHERE `term_id` =  $term_id;
		    ";
		    $wpdb->query($sql);

	    }
	}

	protected function rw_get_term_id_by_old_path_slug($old_path, $term_slug)
	{
		global $wpdb;
		if(empty($old_path)) return false;

		$sql = 'SELECT term_id FROM '. $wpdb->prefix. 'terms WHERE old_path ="'.$old_path.'"';
		$taxonomy = $wpdb->get_row( $sql) ;
		if(!empty($taxonomy))
		{
			$term_id = $taxonomy->term_id;
		}

		return $term_id;
	}
}
