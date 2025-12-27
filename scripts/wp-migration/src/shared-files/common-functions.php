<?php

namespace Scripts\Shared;
use \Scripts\Shared\Logger;
use Scripts\Common\Utils;

trait CommonFunctions {
	use Logger;

	public function get_wp_taxonomy_by_meta($taxonomy_type, $meta_key, $meta_value) {
			$args = [
				'hide_empty' => false,
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

	public function get_wp_post_by_meta($post_type, $meta_key, $meta_value) {
		static $cache = [];
		$cache_key = $post_type.':'.$meta_key.':'.$meta_value;

		if(!empty($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$args = [
			'meta_query' => [
			    [
					'key'       => $meta_key,
					'value'     => $meta_value,
					'compare'   => '='
				]
			],
			'post_type'  => $post_type,
			'post_status' => 'any',
			'posts_per_page' => 1

		];
		$post = get_posts($args);

		$post_id = !empty($post[0]) ? reset($post)->ID : null;
		$cache[$cache_key] = $post_id;

		return $post_id;
	}


	public function get_user_id($owner_id, $meta_key = 'old_user_id'){
		$args = [
			'meta_query' => array(
				array(
					'key' => $meta_key,
					'value' => $owner_id,
					'compare' => '='
				),
			)
		];
		$users = get_users($args) ;

		return !empty($users) ? $users[0]->ID : 0;
	}

	public function get_entity_by_old_id($table_name, $old_id)
	{
		global $wpdb;

		$query = "
			SELECT id FROM {$wpdb->prefix}$table_name WHERE old_{$table_name}_id = $old_id
		";

		$entity = $wpdb->get_row($query);

		return !empty($entity) ? $entity->id : false;
	}

	static function validateDate($date, $format = 'Y-m-d')
	{
		$date_obj = \DateTime::createFromFormat($format, $date);
		return $date_obj && $date_obj->format($format) === $date;
	}

	// Prepare taxonomies of product/post
	// $terms : array of ids / slugs
	// $taxonomies : array of taxonomy slugs (exemple: ['salons', 'post_tag'])
	protected function prepare_taxonomies_entity($terms, $taxonomies, $meta_key = '')
	{
		$taxonomies_data = [];
		if(!empty($terms) && !empty($taxonomies)) {
			foreach($taxonomies as $taxonomy) {
				foreach($terms as $term) {
					$term_obj = $this->get_term_by_id_or_slug($taxonomy, $term, $meta_key);
					if(!empty($term_obj)) {
						$taxonomies_data[$taxonomy][] = $term_obj;
					}
				}
			}
		}

		return $taxonomies_data;
	}

	/**
	 * Get term by id or slug
	 * @param  string $taxonomy
	 * @param  integer | string $identifier
	 * @return array
	 */
	public function get_term_by_id_or_slug($taxonomy, $identifier, $meta_key='')
	{
		$term = [];

		if(!empty($taxonomy) && !empty($identifier)) {
			$term_id = $term_slug = '';
			if(is_numeric($identifier)) {
				$meta_key_old_id = !empty($meta_key) ? $meta_key : "{$taxonomy}_old_id";
				$tax_obj = $this->get_wp_taxonomy_by_meta($taxonomy, $meta_key_old_id, $identifier);
				if(!empty($tax_obj)) {
					$term_id = $tax_obj->term_id;
					$term_slug = $tax_obj->slug;
				}
			}else{
				$term_slug = $identifier;
				$term_id = \RW_Category::rw_get_term_id_by_slug($term_slug, $taxonomy);
			}

			if(!empty($term_id) && !empty($term_slug)) {
				$term = [
					'term_id' => $term_id,
					'term_slug' => $term_slug
				];
			}
		}
		return $term;
	}


	/**
	 * Function to upload images and return an array contain id and url of images uploaded
	 * @param  [array] $images_data
	 * @return [array]
	 */
	public function download_images($images_data)
	{
		$datas = [];
		if (!empty($images_data)) {
			foreach($images_data as $data) {
				$img_url = !empty($data['img_url']) ? $data['img_url'] : '';
				$img_meta_key = !empty($data['img_meta_key']) ? $data['img_meta_key'] : 'old_image_url';

				$thumb_path = parse_url($img_url, PHP_URL_PATH);
				$default_thumb_name = pathinfo($thumb_path, PATHINFO_FILENAME);

				$img_title = !empty($data['img_title']) ? $data['img_title'] : $default_thumb_name;
				$img_desc = !empty($data['img_desc']) ? $data['img_desc'] : '';

				if (!empty($img_url)) {
					$attach_id = Utils::upload_image_by_meta($img_url, $img_title, $img_desc, false, $img_meta_key, $img_url);
					
					if(is_int($attach_id)) {

						$img_credit = !empty($data['img_credit']) ? $data['img_credit'] : '';
						if(!empty($img_credit)) {
							update_post_meta( $attach_id, "copy_right_for_media", $img_credit);
						}
						array_push($datas, [
							'image_id' => $attach_id,
							'image_url' => wp_get_attachment_url($attach_id),
							'old_img_url' => $img_url
						]);
						$img_log = "The image << $img_url >> was uploaded";
						self::registerLog($img_log, '_success');
					}else{
						$img_log = "Upload failed << $img_url >>";
						self::registerLog($img_log, '_error');
					}
				}
			}

		}
		return $datas;
	}

	static function upload_file_by_meta($file, $title, $desc, $post_id, $meta_key, $meta_value, $local_file=false, $check_hash=false, $args_extras=false){

		$args = array(
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_query'  => array(
				array(
					'key'     => $meta_key,
					'value'   => $meta_value
				)
			)
		);
		$posts = get_posts($args);
		if(count($posts)){
			return $posts[0]->ID;
		}

		$requestargs = array(
			'timeout' => 20,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0',
			'check_hash' => $check_hash
		);

		if(!empty($args_extras)) {
			$requestargs = array_merge($requestargs, $args_extras);
		}

		println_flush ("----downloading file : $file \n");
		$attach_id = self::upload_file($file, $title, $desc, $post_id, $local_file, $requestargs);
		update_post_meta($attach_id, $meta_key, $meta_value);
		return $attach_id;
	}

	static function upload_file($src_file, $title='', $description='' , $post_id ='', $local_file=false, $args=[]) {	
		if(!$local_file)
		{
			$tmp = Utils::download_url($src_file, 300, $args);
			if (!empty($args['check_hash'])) 
			{
				$attach_hash = md5_file($tmp);
				$query_args = array(
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'meta_query'  => array(
						array(
							'key'     => 'attach_hash',
							'value'   => $attach_hash
						)
					)
				);
				$attachments = get_posts($query_args);

				if(count($attachments)){
					return $attachments[0]->ID;
				}
			}
		} else {
			$tmp = $src_file;
		}

		$file_array = array();

		$default_extenstions = ['pdf', 'PDF'];
		if(!empty($args['file_extensions']) && is_array($args['file_extensions'])) {
			$default_extenstions = wp_parse_args($args['file_extensions'], $default_extenstions);
		}

		preg_match('/[^\?]+\.('.implode('|', $default_extenstions).')/i', $src_file, $matches);

		$file_array['name'] =  basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if ( is_wp_error( $tmp ) ) {
			@unlink($file_array['tmp_name']);
			$file_array['tmp_name'] = '';
		}

		$attach_id = media_handle_sideload($file_array, $post_id, $description, array('post_title' => $title, 'post_excerpt' => $description));
		
		if (!empty($args['check_hash']) && !empty($attach_hash)) 
		{
			update_post_meta($attach_id, 'attach_hash', $attach_hash);
		}

		return $attach_id;
	}

	public function url_is_image($url)
	{
		$is_image = false;
		if(!empty($url) && filter_var($url, FILTER_VALIDATE_URL) !== false)
		{
			$pos = strrpos($url, ".");
			if ($pos !== false && filter_var($url, FILTER_VALIDATE_URL)) {
				$extension = strtolower(trim(substr($url, $pos)));
				$allowed_extensions = [".gif", ".jpg", ".jpeg", ".png"];
				if (in_array($extension, $allowed_extensions)) {
					$is_image = true;
				}
			}
		}
		return $is_image;
	}
}