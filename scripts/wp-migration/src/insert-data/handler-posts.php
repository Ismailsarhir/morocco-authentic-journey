<?php

/**
 * Insert WP POST
 *
 * @author Brahim Ibrahimi
 */
namespace Scripts\Classes;
use Scripts\Common\Utils;

class HandlerPosts {

	use \Scripts\Shared\Logger;

	const ERROR_FILE_PREFIX = '_error';
	const SUCCESS_FILE_PREFIX = '_success';

	function __construct() {

		add_filter( 'wp_insert_post_data', [$this, 'alter_post_modification_time'], 99, 2 );
		$this->disable_rocket_hooks();
	}

	/**
	 * Insert WP POST By Old Data.
	 */
	public static function insert_post($postdata, $force_update = false) {
		date_default_timezone_set('Europe/Paris');
		$old_id = !empty($postdata['old_id']) ? $postdata['old_id'] : 0;
		$old_type = !empty($postdata['old_type']) ? $postdata['old_type'] : 'post';
		$post_excerpt = !empty($postdata['post_excerpt']) ? $postdata['post_excerpt'] : '';
		$title = !empty($postdata['title']) ? $postdata['title'] : '';
		$content = !empty($postdata['content']) ? $postdata['content'] : '';
		$published = !empty($postdata['published']) ? $postdata['published'] : '';
		$published_gmt = !empty($postdata['published_gmt']) ? $postdata['published_gmt'] : get_gmt_from_date($published);
		$modified = !empty($postdata['modified']) ? $postdata['modified'] : '';
		$modified_gmt = !empty($postdata['modified_gmt']) ? $postdata['modified_gmt'] : get_gmt_from_date($modified);
		$post_status = !empty($postdata['post_status']) ? $postdata['post_status'] : 'publish';
		$post_category = !empty($postdata['post_category']) ? $postdata['post_category'] : [];
		$post_tags = !empty($postdata['post_tags']) ? $postdata['post_tags'] : [];
		$user_id = !empty($postdata['user_id']) ? $postdata['user_id'] : 0;
		$post_name = !empty($postdata['post_name']) ? $postdata['post_name'] : null;
		$post_password = !empty($postdata['post_password']) ? $postdata['post_password'] : '';
		//meta datas
		$metas_data = !empty($postdata['metas_data']) ? $postdata['metas_data'] : [];
		if(!empty($old_id)) {
			$metas_data[$old_type.'_id'] = $old_id;
		}
		//thumb data
		$thumb_data = !empty($postdata['thumb_data']) ? $postdata['thumb_data'] : [];
		// taxonomies
		$taxonomies = !empty($postdata['taxonomies']) ? $postdata['taxonomies'] : [];
		$post_type = !empty($postdata['post_type']) ? $postdata['post_type'] : 'post';
		$old_path = !empty($postdata['old_path']) ? $postdata['old_path'] : false;
		$gallery_images = !empty($postdata['gallery_images']) ? $postdata['gallery_images'] : false;

		

		$post_args = [
			'post_excerpt' 	=> $post_excerpt,
			'post_title'    => $title,
			'post_content'  => $content,
			'post_date'     => $published,
			'post_date_gmt'     => $published_gmt,
			'post_modified' => $modified,
			'post_modified_gmt' => $modified_gmt,
			'post_category' => $post_category,
			'tags_input' 	=> $post_tags,
			'post_author'   => $user_id,
			'post_status'   => $post_status,
			'post_type'     => $post_type,
		];

		if($user_id) {
			$post_args['post_author'] = $user_id ;
		}

		if(!empty($post_password)) {
			$post_args['post_password'] = $post_password;
		}

		if (!empty($post_name)) {
			$post_args['post_name'] = $post_name ;
		}
		if(!empty($postdata['force_post_id'])){
			$p = get_post( $postdata['force_post_id']);
			$post_id = !empty($p->ID) ? $p->ID :false ;
		}else{
			/*Get post if exist using old type && id*/
			$post_id = self::get_post_if_exist($old_type, $old_id, $post_type);
		}

		if(!empty($post_id) && !$force_update) {
			if(!empty($postdata['data_test'])){
				self::insert_test($post_id,$postdata['data_test']);
			}
			$log_message = "$post_id is already imported \n";
			self::registerLog($log_message, self::SUCCESS_FILE_PREFIX);
			return $post_id;
		}

		if($gallery_images){
			$gallery = self::generate_gallery($gallery_images);
			$post_args['post_content'] = $gallery . $post_args['post_content'] ;
		}


		if (!empty($post_id)) {

			$post_args['ID'] = $post_id;
			$post_id = wp_update_post( $post_args );
			$log_message = "post updated << old_id: {$old_id} , post_id: {$post_id}, post_title : {$title} >> ".date('Y-m-d H:i:s');
		}else {

			if(!empty($postdata['force_post_id'])){
				$post_args['import_id'] = $postdata['force_post_id'];
			}

			$post_id = wp_insert_post( $post_args );
			$log_message = "post imported << old_id: $old_id , post_id: $post_id, post_title : $title >> ".date('Y-m-d H:i:s');
		}

		/*use function registerLog of trait Logger to log messages*/
		if (!is_wp_error($post_id)) {
			self::registerLog($log_message, self::SUCCESS_FILE_PREFIX);
		}else{

			$error_message = "import error << old_id: {$old_id} , post_id: {$post_id}, post_title : {$title} >> error". $post_id->get_error_message();
			self::registerLog($error_message, self::ERROR_FILE_PREFIX);
		}
		/* Let's set post metax taxonomies and thumb */
		self::set_post_metas($post_id, $metas_data);
		self::set_post_taxonomies($post_id, $taxonomies);

		//Set old_path
		self::update_post_old_path($post_id, $old_path);

		$insert_thump_log = self::set_post_thumbnail($post_id, $thumb_data);


		if(!empty($postdata['data_test'])){
			self::insert_test($post_id,$postdata['data_test']);
		}

		//sur le meme sujet
		//utilisation de plugin "unify seo"
		if(!empty($metas_data['same_subject'])){
			self::insert_same_subject_unifyseo_data($metas_data['same_subject']);
		}

		return $post_id;
	}


	/**
	 * Insert WP POST By Old Data.
	 */
	public static function create_post_basics($postdata) {

		$old_id = !empty($postdata['old_id']) ? $postdata['old_id'] : 0;
		$old_type = !empty($postdata['old_type']) ? $postdata['old_type'] : 'post';
		$title = !empty($postdata['title']) ? $postdata['title'] : '';
		$slug = !empty($postdata['slug']) ? $postdata['slug'] : '';
		$post_status = !empty($postdata['status']) ? $postdata['status'] : 'publish';
		$post_type = !empty($postdata['post_type']) ? $postdata['post_type'] : 'post';
		$old_path = !empty($postdata['old_path']) ? $postdata['old_path'] : false;

		$post_args = [
			'post_title'  => $title,
			'post_name'   => $slug,
			'post_status' => $post_status,
			'post_type' => $post_type,
		];

		/*Get post if exist using old type && id*/
		$post_id = self::get_post_if_exist($old_type, $old_id, $post_type);

		if (!empty($post_id)) {
			
			$post_args['ID'] = $post_id;
			$post_id = wp_update_post( $post_args );
			$log_message = "post updated << old_id: {$old_id} , post_id: {$post_id}, post_title : {$title} >> ".date('Y-m-d H:i:s');
		}else {

			//force old post id
			$post_args['import_id'] = $old_id;
			$post_id = wp_insert_post( $post_args );
			$log_message = "post imported << old_id: $old_id , post_id: $post_id, post_title : $title >> ".date('Y-m-d H:i:s');
		}

		/*use function registerLog of trait Logger to log messages*/
		if (!is_wp_error($post_id)) {
			self::registerLog($log_message, self::SUCCESS_FILE_PREFIX);
		}else{

			$error_message = "import error << old_id: {$old_id} , post_id: {$post_id}, post_title : {$title} >> error". $post_id->get_error_message();
			self::registerLog($error_message, self::ERROR_FILE_PREFIX);
		}

		/* Let's add old_id meta */
		self::update_post_old_path($post_id, $old_path);

		/* Let's add old_id meta */
		update_post_meta($post_id, $old_type.'_id', $old_id);

		return $post_id;
	}

	/**
	 * Check if post is already imported
	 */
	private static function get_post_if_exist($old_type, $old_id, $post_type = 'post') {

		global $wpdb ;

		$sql = "SELECT SQL_CACHE {$wpdb->prefix}posts.* FROM {$wpdb->prefix}posts 
				INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) 
				WHERE ( 
		  			( 
		  				{$wpdb->prefix}postmeta.meta_key = '{$old_type}_id' 
		  				AND {$wpdb->prefix}postmeta.meta_value = '$old_id' 
		  			)
				)" ;

		if($post_type) {

			$sql .= "AND {$wpdb->prefix}posts.post_type = '$post_type'";
		}

		$sql .= "AND (({$wpdb->prefix}posts.post_status <> 'trash' 
			AND {$wpdb->prefix}posts.post_status <> 'auto-draft'))  LIMIT 1 ;" ;

		$post = $wpdb->get_row($sql);

		return !empty($post) ? $post->ID : null;
	}

	/**
	 * set posts metas
	 */
	private static function set_post_metas($post_id, $metas_data){

		if (!empty($metas_data)) {

			foreach ($metas_data as $meta_key => $meta_value) {
				
				if (!empty($meta_value)) {

					update_post_meta($post_id, $meta_key, $meta_value);
				}
			}
		}
	}

	/**
	 * set posts thumbnail
	 */
	private static function set_post_thumbnail($post_id, $thumb_data) {

		if (!empty($thumb_data)) {

			$thumbnail_url = !empty($thumb_data['thumbnail_url']) ? $thumb_data['thumbnail_url'] : '';

			$thumb_path = parse_url($thumbnail_url, PHP_URL_PATH);
			$default_thumb_name = pathinfo($thumb_path, PATHINFO_FILENAME);

			$thumbnail_meta_key = !empty($thumb_data['thumbnail_meta_key']) ? $thumb_data['thumbnail_meta_key'] : 'thumbnail_url';
			$thumbnail_title = !empty($thumb_data['thumbnail_title']) ? $thumb_data['thumbnail_title'] : $default_thumb_name;
			$thumbnail_desc = !empty($thumb_data['thumbnail_desc']) ? $thumb_data['thumbnail_desc'] : '';
			$is_thumb_featured = !empty($thumb_data['is_thumb_featured']) ? $thumb_data['is_thumb_featured'] : true;
			$copyright_text = !empty($thumb_data['copyright_text']) ? $thumb_data['copyright_text'] : "";

			if (!empty($thumbnail_url)) {

				$local_file = false ;
				if(!empty($thumb_data['local_file'])){
					$local_file = true ;
				}


				$attach_id = Utils::upload_image_by_meta($thumbnail_url, $thumbnail_title, $thumbnail_desc, false, $thumbnail_meta_key, $thumbnail_url, $local_file);
				
				if(is_int($attach_id)) {

					update_post_meta($post_id, '_thumbnail_id', $attach_id);
					update_post_meta($post_id, 'is_thumb_featured', $is_thumb_featured);
					$insert_thumb_log = "image $thumbnail_url was uploaded";
					if(!empty($copyright_text)){
						update_post_meta($attach_id, 'copy_right_for_media', $copyright_text);
					}
					self::registerLog($insert_thumb_log, self::SUCCESS_FILE_PREFIX);
				}else {
					
					$thumb_log_error = "upload failed $thumbnail_url";
					self::registerLog($thumb_log_error, self::ERROR_FILE_PREFIX);
				}
			}
		}
	}

	/**
	 * set posts taxonomies
	 */
	private static function set_post_taxonomies($post_id, $taxonomies) {

		if (!empty($taxonomies)) {

			foreach ($taxonomies as $taxonomy) {
				
				if (!empty($taxonomy['slug']) && isset($taxonomy['taxonomy_ids'])) {

					wp_set_object_terms($post_id , $taxonomy['taxonomy_ids'], $taxonomy['slug']);
				}
			}
		}
	}

	private static function update_post_old_path($post_id, $old_path) {

		if(!defined('URL_BY_OLD_PATH') || !URL_BY_OLD_PATH){
			return;
		}

		if(empty($old_path) || empty($post_id)) {
			return;
		}

		global $wpdb ;
		$sql = " UPDATE `{$wpdb->prefix}posts` SET `old_path` = '$old_path' WHERE `ID` =  {$post_id};";
		$wpdb->query($sql);
	}

	
	public static function insert_test($post_id,$data_test){
		if($post_id && !empty($data_test)){
			global $wpdb ;
			// delete all questions if already existed
			$query ="delete from  `{$wpdb->prefix}medias_qa_questions` where id_media=$post_id";
			$wpdb->get_col($query);
			// delete all answers if already existed
			$query ="delete from  `{$wpdb->prefix}medias_qa_responses` where id_media=$post_id";
			$wpdb->get_col($query);
			// delete all profiles if already existed
			$query ="delete from  `{$wpdb->prefix}medias_quizz_profiles` where id_media=$post_id";
			$wpdb->get_col($query);

			// delete all participations if already existed
			$query ="delete from  `{$wpdb->prefix}medias_qa_participations` where id_media=$post_id";
			$wpdb->get_col($query);


			if(!empty($data_test['profiles'])){
				// insert profiles
				foreach($data_test["profiles"] as $key_profile => $profile){
					if(isset($profile["profile_name"])){
						$profile_name = $profile["profile_name"];
					}else{
						$profile_value = !empty($profile["value"]) ? $profile["value"] : '';
						$profile_name = 'profile '.$profile["value"];
					}
					$profile_content = !empty($profile["content"]) ? str_replace("'", "’", $profile["content"]) : "";

					$min_points = $profile["min_points"] ?? 0;
					$max_points = $profile["max_points"] ?? 0;
					$n_responses = $profile["n_responses"] ?? 0;

					/*$query = "INSERT INTO `{$wpdb->prefix}medias_quizz_profiles` (`id_media`, `id_photo`, `name`, `description`, `n_responses`,`minimum_number_of_points`, `maximum_number_of_points`) VALUES (".$post_id." ,'0','".$profile_name."','".$profile_content."', '$n_responses', '$min_points','$max_points')";
					$wpdb->query($query);*/

					$data = [
					    'id_media'                 => $post_id,
					    'id_photo'                 => 0,
					    'name'                     => $profile_name,
					    'description'              => $profile_content,
					    'n_responses'              => $n_responses,
					    'minimum_number_of_points' => $min_points,
					    'maximum_number_of_points' => $max_points
					];

					$wpdb->insert("{$wpdb->prefix}medias_quizz_profiles", $data);
					$id_profile = $wpdb->insert_id;
					if( !empty($profile['reference_profile'])){
						$reference_profiles[$profile['reference_profile']] = $id_profile ;
					}else{
						$reference_profiles[] = $id_profile ;
					}

				}
			}

			foreach($data_test['questions'] as $question){
				//$question["response"] = str_replace('"',"'",$question["response"]);
				$question_text = $question["question_text"]?? $question["response"]?? '' ;
				$question_text = str_replace('"',"'",$question_text);
				$question["explanation"] = str_replace('"',"'",$question["explanation"]);
				$question["explanation"] = !empty($question["explanation"]) ? str_replace('"',"'",$question["explanation"]) : "";
				$question_type = ($question["type"]) ??  0;

				$question["position"] = !empty($question["position"]) ? $question["position"] : 0;
				// insert questions
				/*$query = "INSERT INTO `{$wpdb->prefix}medias_qa_questions` (`id_media`, `id_image`, `type`, `question_text`, `position`, `nb_possible_choices`, `random_order`, `free_answer`, `explanation_correct_answer`, `explanation_image`) VALUES (".$post_id.', 0, '.$question_type.', "'. $question_text .'", '.$question["position"].', 1, 0, 0, "'.$question['explanation'].'", 0)';
				$wpdb->get_col($query);*/

				$wpdb->insert(
				    "{$wpdb->prefix}medias_qa_questions",
				    [
				        'id_media'                  => $post_id,
				        'id_image'                  => 0,
				        'type'                      => $question_type,
				        'question_text'             => $question_text,
				        'position'                  => $question["position"],
				        'nb_possible_choices'       => 1,
				        'random_order'              => 0,
				        'free_answer'               => 0,
				        'explanation_correct_answer'=> $question['explanation'],
				        'explanation_image'         => 0
				    ]
				);



				$id_question = $wpdb->insert_id;
				if(!empty($question["answers"])){
					// insert answers
					foreach($question["answers"] as $answer){
						$answer["content"] = str_replace('"',"'",$answer["content"]);
						$answer["position"] = !empty($answer["position"]) ? $answer["position"] : 0;
						$answer["correct"] = !empty($answer["correct"]) ? $answer["correct"] : 0;
						$num_points = $answer["num_points"]?? 0 ;

						$medias_qa_responses = $wpdb->prefix.'medias_qa_responses';
						$inserted_answer = $wpdb->insert($medias_qa_responses, [
							'id_question' => $id_question,
							'id_media' => $post_id,
							'type' => '3',
							'position' => $answer["position"],
							'response' => $answer["content"],
							'correct' => $answer["correct"],
							'image_answer' => "0",
							'num_points' => $num_points,
						]);

						$inserted_response_id = $wpdb->insert_id;

						$query = "delete from `{$wpdb->prefix}medias_qa_participations_responses` where id_response=$inserted_response_id AND id_question=$id_question" ;
						$wpdb->get_col($query);

						if(is_array($reference_profiles) && count($reference_profiles)){

							$media_qa_participations = $wpdb->prefix.'medias_qa_participations';
							$participation_table_name = $wpdb->prefix.'medias_qa_participations_responses';

							foreach ($answer['participations'] as $participation) {
								$qa_participations = $wpdb->insert($media_qa_participations, [
									'id_media' => $participation['id_media'],
									'type' => $participation['type'],
									'date' => $participation['date'],
								]);
								$qa_participations_inserted_id = $wpdb->insert_id;
								
								$data_participation = [
									'id_participation' => $qa_participations_inserted_id,
									'id_question' => $id_question,
									'id_response' => $inserted_response_id,
								];
								$id_participation = $wpdb->insert($participation_table_name, $data_participation);
							}
						}
						if(is_array($reference_profiles) && count($reference_profiles)){
							if(!empty($answer['reference_profile']) && !empty($reference_profiles[$answer['reference_profile']])){
								$id_profile = $reference_profiles[$answer['reference_profile']];
							}else{
								$id_profile = $reference_profiles[0] ;
							}

							$inserted_answer = $wpdb->insert( $wpdb->prefix.'medias_quizz_responses', [
								'id_response' => $inserted_response_id,
								'id_profile' => $id_profile,
							]);

						}



					}
				}
			}
		}
	}



	private static function generate_gallery($gallery_images){

		$attachment_ids = [] ;
		$gallery ='';
		foreach ($gallery_images as $data) {
			


			$image = $data['url'];
			$title = $data['title'];
			$desc = $data['introduction'];
			$copyright = $data['copyright'];
			$meta_key = !empty($data['origin_id']) ? 'origin_id' : 'origin_url' ;
			$meta_value = $data['origin_id'] ??  $image ;

			if (!empty($image)) {

				$attachment_id = Utils::upload_image_by_meta($image, $title, $desc, false, $meta_key, $meta_value);
				update_post_meta($attachment_id,'copy_right_for_media', $copyright);

				if($attachment_id && is_numeric($attachment_id)){
					array_push($attachment_ids, $attachment_id);
				}
			}

		}
		if(count($attachment_ids)){
			$gallery = '[gallery ids="'.implode(',', $attachment_ids).'"] ' ;
		}

		return $gallery ;
	}

	public function insert_comment($args) {

		$comment_post_id = !empty($args['comment_post_id']) ? $args['comment_post_id'] : '';
		$user_id = !empty($args['user_id']) ? $args['user_id'] : '';
		$comment_content = !empty($args['comment_content']) ? $args['comment_content'] : '';
		$comment_author = !empty($args['comment_author']) ? $args['comment_author'] : '';
		$comment_author_ip = !empty($args['comment_author_ip']) ? $args['comment_author_ip'] : '';
		$comment_author_email = !empty($args['comment_author_email']) ? $args['comment_author_email'] : '';
		$comment_date = !empty($args['comment_date']) ? $args['comment_date'] : '';
		$comment_date_gmt = !empty($args['comment_date_gmt']) ? $args['comment_date_gmt'] : '';
		$comment_author_IP = !empty($args['comment_author_IP']) ? $args['comment_author_IP'] : '';
		$comment_meta = !empty($args['comment_meta']) ? $args['comment_meta'] : '';
		$comment_author_url = !empty($args['comment_author_url']) ? $args['comment_author_url'] : '';
		$comment_old_id = !empty($args['comment_meta']['comment_old_id']) ? $args['comment_meta']['comment_old_id'] : 0;
		$comment_parent_id = !empty($args['comment_parent']) ? $args['comment_parent'] : 0;

		$comment_data = [
			'comment_post_ID' => $comment_post_id,
			'user_id' => $user_id,
			'comment_content' => $comment_content,
			'comment_author' => $comment_author,
			'comment_author_email' => $comment_author_email,
			'comment_date' => $comment_date,
			'comment_date_gmt' => $comment_date_gmt,
			'comment_author_IP' => $comment_author_IP,
			'comment_meta' => $comment_meta,
			'comment_author_url' => $comment_author_url
		];

		if(!empty($comment_parent_id)) {
			$comment_data['comment_parent'] = $comment_parent_id;
		}

		$comment_id = 0;

		if($comment_old_id) {

			$found = get_comments([
				'meta_key' => 'comment_old_id',
				'meta_value' => $comment_old_id
			]);

			if(!$found) {

				$comment_id = wp_insert_comment($comment_data);
			} else {

				$comment_id = $comment_data['comment_ID'] = $found[0]->comment_ID;
				wp_update_comment($comment_data);
			}

		}


		return $comment_id;
	}

	public function alter_post_modification_time( $data , $postarr ) {
		if (!empty($postarr['post_modified']) && !empty($postarr['post_modified_gmt'])) {
			$data['post_modified'] = $postarr['post_modified'];
			$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
		}
		return $data;
	}

	/**
	 * Insert same subect By post url.
	 * @param $same_subject array serialize
	 */
	static function insert_same_subject_unifyseo_data($same_subject){
		global $wpdb;
		if(!empty($same_subject)){
			$same_subject = unserialize($same_subject);
			if(!empty($same_subject['seoUrl']) && !empty($same_subject['linkUrl'])){
				$same_subject = array_values($same_subject);
				$wpdb->query(
					$wpdb->prepare('REPLACE INTO `'. $wpdb->prefix.'unify_seo_internal_links`
										(`seoUrl`, `linkUrl`, `linkZoneId`, `linkOrder`)
										VALUES (%s, %s, %d, %d)', $same_subject)
				);
			}
		}
	}

	private function disable_rocket_hooks() {
		remove_action('clean_post_cache', 'rocket_clean_post');
		remove_filter('rocket_clean_files', 'rocket_clean_files_users');
	}

}

new HandlerPosts();