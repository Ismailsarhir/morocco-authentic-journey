<?php

namespace Scripts\Common;

Class Utils {
	static function rrmdir($dir) {
		if (is_dir($dir)) {
			$objects = scandir($dir);
			foreach ($objects as $object) {
				if ($object != "." && $object != "..") {
					if (filetype($dir."/".$object) == "dir") {
						rrmdir($dir."/".$object); 
					}
					else {
						unlink ($dir."/".$object);
					}
				}
			}
			reset($objects);
			rmdir($dir);
		}
	}

	/**
	 * get_imported_time description
	 * 
	 * Calculer le temps d'import d'une entité et retourner un message detailé
	 * 
	 * @param  double $start_time : à initialiser au debut de script
	 * @param  double $start_time_entity : à initialiser au debut de foreach
	 * @param  integer $index : c'est l'iteration actuel (à initialiser au debut de foreach)
	 * @param  integer $foundRows : c'est le nombre total des items trouvés
	 * @return string
	 */
	public static function get_imported_time($start_time, $start_time_entity, $index, $foundRows)
	{
		$msg = "";
		if(
			!empty($start_time) && 
			!empty($index) && 
			!empty($start_time_entity) && 
			!empty($foundRows)
		)
		{
			$last_time = time();
			$time_diff = $last_time - $start_time;
			$time_left = ($time_diff / $index) * ($foundRows - $index);

			$jours = (int) ($time_left / (60*60*24));
			$reste =  $time_left % (60*60*24);
			$heures = (int)( $reste / (60*60));
			$reste =  $reste % (60*60);
			$minutes = (int)( $reste / (60));
			$secondes = (int)( $reste % (60));

			$time_diff_entity = $last_time - $start_time_entity;

			$msg = "L'entity est importé en {$time_diff_entity} secondes\n";
			$msg .= "Temps restant : {$jours} jours {$heures} heures {$minutes} minutes {$secondes} secondes";
		}
		return $msg;
	}

	public static function calculate_time_spent($start_timestamp) {
		$time_difference = time() - $start_timestamp;

		$hours = floor($time_difference / 3600);
		$minutes = floor(($time_difference % 3600) / 60);
		$seconds = $time_difference % 60;

		return "{$hours} hours, {$minutes} minutes, {$seconds} seconds";
	}

	static function upload_image_by_meta($image, $title, $desc, $post_id, $meta_key, $meta_value, $local_file = false, $convert_to_webp = false, $check_hash = false, $args_extras = false) {
		$args = [
			'post_type'   => 'attachment',
			'post_status' => 'inherit',
			'meta_query'  => [
				[
					'key'   => $meta_key,
					'value' => $meta_value
				]
			]
		];
		$posts = get_posts($args);
		if(count($posts)) {
			return $posts[0]->ID;
		}
		$requestargs = [
			'timeout' => 20,
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.8; rv:20.0) Gecko/20100101 Firefox/20.0',
			'check_hash' => $check_hash
		];

		if($args_extras) {
			$requestargs = array_merge($requestargs, $args_extras);
		}

		println_info_flush ("----downloading imag : $image \n");
		$attach_id = self::upload_img($image, $title, $desc, $post_id, $local_file, $requestargs, $convert_to_webp) ;
		update_post_meta($attach_id, $meta_key, $meta_value);
		return $attach_id;
	}

	static function upload_img($src_img, $title = '', $description = '', $post_id = '', $local_file = false, $args = [], $convert_to_webp = false) {	
		if(!$local_file) {
			$tmp = self::download_url($src_img, 300, $args);
			if (!empty($args['check_hash'])) {
				$attach_hash = md5_file($tmp);
				$query_args = [
					'post_type'   => 'attachment',
					'post_status' => 'inherit',
					'meta_query'  => [
						[
							'key'     => 'attach_hash',
							'value'   => $attach_hash
						]
					]
				];

				$attachments = get_posts($query_args);
				if(count($attachments)) {
					return $attachments[0]->ID;
				}
			}
		} else {
			$tmp = $src_img;
		}

		if($convert_to_webp && !is_wp_error($tmp)) {
			$tmp = self::convert_to_webp($tmp);
		}

		$file_array = [];
		$regix_types = apply_filters('custom_regix_types_attachment','/[^\?]+\.(jpe?g|jpe|gif|png|svg|webp)/i');
		preg_match($regix_types, $src_img, $matches);
		$file_array['name'] =  basename($matches[0]);
		$file_array['tmp_name'] = $tmp;

		// If error storing temporarily, unlink
		if (is_wp_error($tmp)) {
			if (!empty($file_array['tmp_name']) && !is_wp_error($file_array['tmp_name'])) {
				@unlink($file_array['tmp_name']);
			}
			$file_array['tmp_name'] = '';
		}
		// do the validation and storage stuff

		$attach_id = media_handle_sideload( $file_array, $post_id , $description, array('post_title' => $title, 'post_excerpt' => $description));

		do_action('RW_Utils_upload_img', $attach_id);
		
		if (!empty($args['check_hash']) && !empty($attach_hash)) {
			update_post_meta($attach_id, 'attach_hash', $attach_hash);
		}	

		return $attach_id;
	}

	static function download_url($url, $timeout = 300, $args = []) {
		//WARNING: The file is not automatically deleted, The script must unlink() the file.
		if (!$url) {
			return new \WP_Error('http_no_url', __('Invalid URL Provided.'));
		}

		$url_filename = basename(parse_url($url, PHP_URL_PATH));

		$tmpfname = wp_tempnam($url_filename);
		if (!$tmpfname) {
			return new \WP_Error('http_no_file', __('Could not create Temporary file.'));
		}

		$defaults = [
			'timeout'  => $timeout,
			'stream'   => true,
			'filename' => $tmpfname,
		];

		$args = wp_parse_args($args, $defaults);

		$response = wp_safe_remote_get($url, $args);

		if (is_wp_error($response)) {
			unlink($tmpfname);
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code($response);

		if (200 != $response_code) {
			$data = [
				'code' => $response_code,
			];

			// Retrieve a sample of the response body for debugging purposes.
			$tmpf = fopen($tmpfname, 'rb');
			if ($tmpf) {
				/**
				 * Filters the maximum error response body size in `download_url()`.
				 *
				 * @since 5.1.0
				 *
				 * @see download_url()
				 *
				 * @param int $size The maximum error response body size. Default 1 KB.
				 */
				$response_size = apply_filters('download_url_error_max_body_size', KB_IN_BYTES);
				$data['body']  = fread($tmpf, $response_size);
				fclose($tmpf);
			}

			unlink($tmpfname);
			return new \WP_Error('http_404', trim(wp_remote_retrieve_response_message($response)), $data);
		}

		$content_md5 = wp_remote_retrieve_header($response, 'content-md5');
		if ($content_md5) {
			$md5_check = verify_file_md5($tmpfname, $content_md5);
			if (is_wp_error($md5_check)) {
				unlink($tmpfname);
				return $md5_check;
			}
		}

		return $tmpfname;
	}

	static function convert_to_webp($file, $compression_quality = 80) {
		// check if file exists
		if (!file_exists($file)) {
			return false;
		}
		$file_type = exif_imagetype($file);
		$path_parts = pathinfo($file);
		//https://www.php.net/manual/en/function.exif-imagetype.php
		//exif_imagetype($file);
		// 1    IMAGETYPE_GIF
		// 2    IMAGETYPE_JPEG
		// 3    IMAGETYPE_PNG
		// 6    IMAGETYPE_BMP
		// 15   IMAGETYPE_WBMP
		// 16   IMAGETYPE_XBM
		$output_file =  $path_parts['dirname'] . '/' . $path_parts['filename'] . '.webp';
		if (file_exists($output_file)) {
			return $output_file;
		}
		if (function_exists('imagewebp')) {
			switch ($file_type) {
				case '1': //IMAGETYPE_GIF
					$image = imagecreatefromgif($file);
					break;
				case '2': //IMAGETYPE_JPEG
					$image = imagecreatefromjpeg($file);
					break;
				case '3': //IMAGETYPE_PNG
					$image = imagecreatefrompng($file);
					imagepalettetotruecolor($image);
					imagealphablending($image, true);
					imagesavealpha($image, true);
					break;
				case '6': // IMAGETYPE_BMP
					$image = imagecreatefrombmp($file);
					break;
				case '15': //IMAGETYPE_Webp
					return false;
					break;
				case '16': //IMAGETYPE_XBM
					$image = imagecreatefromxbm($file);
					break;
				default:
					return false;
			}
			// Save the image
			$result = imagewebp($image, $output_file, $compression_quality);
			if (false === $result) {
				return false;
			}
				// Free up memory
			imagedestroy($image);
			return $output_file;
		} elseif (class_exists('Imagick')) {
			$image = new Imagick();
			$image->readImage($file);
		if ($file_type === "3") {
			$image->setImageFormat('webp');
			$image->setImageCompressionQuality($compression_quality);
			$image->setOption('webp:lossless', 'true');
		}
		$image->writeImage($output_file);
			return $output_file;
		}
		return false;
	}

	static function replace_internal_image($content, $size = 'full') {
		if(!empty($content)) {
			$home_url = home_url();

			preg_match_all( '/<img[^>]*src=([\'"])(?<src>.+?)\1[^>]*>/', $content, $matches);

			if(!empty($matches['src'])) {
				foreach($matches['src'] as $image_url) {
					if(strpos($image_url, $home_url) !== false) {
						continue;
					}

					if (strpos($image_url, 'http://') === false && strpos($image_url, 'https://') === false) {
						continue;
					}

					$old_url = $image_url;
					$image_url = str_replace('&amp;', '&', $image_url);

					$thumb_path = parse_url($image_url, PHP_URL_PATH);
					$default_thumb_name = pathinfo($thumb_path, PATHINFO_FILENAME);

					$img_id = self::upload_image_by_meta($image_url, $default_thumb_name, '', false, 'thumbnail_url', $image_url);

					if (is_int($img_id)) {
						$image_new_url = wp_get_attachment_image_src($img_id, $size);
						if (!empty($image_new_url[0])) {
							$content = str_replace($old_url, $image_new_url[0], $content);
						}
					}
				}
			}
			return $content;
		}
	}

	static function  get_post_by_old_id($origin_id, $meta_key , $post_type = 'post',$fields='*'){
		global $wpdb;

		if (is_array($post_type) ) 
		{
			$types = implode(',', array_map('self::add_quotes', $post_type));

			$sql_post_type = " AND {$wpdb->prefix}posts.post_type IN (".$types.")";
		}else
		{
			$sql_post_type = " AND {$wpdb->prefix}posts.post_type = '".$post_type."'";
		}

	    $sql = "SELECT  SQL_CACHE  {$wpdb->prefix}posts.$fields FROM {$wpdb->prefix}posts  INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE ( 
		  ( {$wpdb->prefix}postmeta.meta_key = '$meta_key' 
		  	AND {$wpdb->prefix}postmeta.meta_value = '$origin_id' )
		)"; 
		$sql .= $sql_post_type; 
		$sql .= " AND (({$wpdb->prefix}posts.post_status <> 'trash' 
			AND {$wpdb->prefix}posts.post_status <> 'auto-draft'))  LIMIT 1 ;" ;

	    $post = $wpdb->get_row($sql);

	    return $post ;
	}

	static function rw_get_term_by_meta($taxonomy, $meta_key, $meta_value)
	{
		$args = [
			'taxonomy' => $taxonomy,
			'meta_key' => $meta_key,
			'meta_value' => $meta_value,
			'hide_empty' => false
		];
		$terms = get_terms($args);

		if (!is_wp_error($terms) && !empty($terms)) {
			return $terms[0];
		}

		return false;
	}

	static function social_link_after_check($link, $type){
		if(!empty($link)){
			$fixed_link = RW_User_Social_Media_Utils::validateAndFixUrl($type, $link);
			if($fixed_link !== false) {
				$link = $fixed_link;
			}
		}
		return $link;
	}
}