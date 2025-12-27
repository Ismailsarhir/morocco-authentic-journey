<?php

/**
 * Insert WP User
 *
 * @author Brahim Ibrahimi
 */

namespace Scripts\Classes;
use Scripts\Common\Utils;

class HandlerUsers {
	
	use \Scripts\Shared\Logger;

	const ERROR_FILE_PREFIX = '_error';
	const SUCCESS_FILE_PREFIX = '_success';
	/**
	 * Insert WP User By Old Data.
	 */

	public static function insert_user($userdata, $force_update = false) {

		$user = $user_id = false;
		//user metas
		$user_metas = !empty($userdata['user_metas']) ? $userdata['user_metas'] : [];
		/**
		 * Find the user by some alternative fields
		 *
		 * Fields checked: user_login, user_email
		*/

		if ( isset( $userdata['user_login'] ) ){
			$user = get_user_by( 'login', $userdata['user_login'] );
		}

		if ( ! $user && isset( $userdata['user_email'] ) ){
			$user = get_user_by( 'email', $userdata['user_email'] );
		}

		if(!empty($user) && !$force_update) {
			$log_message = "$user->ID is already imported \n";
			self::registerLog($log_message, self::SUCCESS_FILE_PREFIX);
			return $user->ID;
		}
		
		$update = false;
		if ( $user ) {
			$userdata['ID'] = $user->ID;
			$update = true;
		}

		if ( empty( $userdata['user_pass'] ) && !$user){
			/* No password set for this user, let's generate one automatically */
			$userdata['user_pass'] = wp_generate_password( 12, false );
		}

		if ( ! empty( $userdata['role'] ) ) {
			$userdata['role'] = strtolower( $userdata['role'] );

			$user_roles = explode( ',', $userdata['role'] );
			$user_roles = array_map( 'trim', $user_roles );

			if( count( $user_roles ) > 1 ) {
				$userdata['role'] = reset( $user_roles );
			}
		}

		if ( $update ){
			$user_id = wp_update_user( $userdata );
			if ( !is_wp_error( $user_id ) ) {
				$user_log = "user updated << {$user_id} >> ".date('Y-m-d H:i:s');
			}
		} else {
			$user_id = wp_insert_user( $userdata );
			if ( !is_wp_error( $user_id ) ) {
				$user_log = "user imported << {$user_id} >> ".date('Y-m-d H:i:s');
			}
		}

		$args = !empty($userdata["args"]) ? $userdata["args"] : false;

		/* Is there an error o_O? */
		if ( empty($user_id) || is_wp_error( $user_id ) ) {

			$user_log = "user creation error {$userdata['user_login']}";
			self::registerLog($user_log, self::ERROR_FILE_PREFIX);
			return ;
		} else {
			/* If no error, let's update the user meta too! */
			self::set_user_metas($user_id, $user_metas , $args);
			/* Let's update the user roles! */
			self::update_user_roles($user_id, $user_roles);

			self::registerLog($user_log, self::SUCCESS_FILE_PREFIX);
		}

		return $user_id;
	}

	/**
	 * set user metas
	 */
	private static function set_user_metas($user_id, $user_metas,$args = false){
		
		if ( $user_metas ) {

			$args_image = false;
			if(!empty($args) && !empty($args["args_image"])){
				$args_image = $args["args_image"];
			}

			foreach ( $user_metas as $metakey => $metavalue ) {
				$metavalue = maybe_unserialize( $metavalue );

				/* Let's set user avatar! */
				if ($metakey == 'user_image') {
					$url_image = $user_metas['user_image']['url_image'];
					$title_image = !empty($user_metas['user_image']['title_image']) ? $user_metas['user_image']['title_image'] : '';
					$desc= !empty($user_metas['user_image']['description']) ? $user_metas['user_image']['description'] : '';
					$is_local_img = !empty($user_metas['user_image']['local_img']) ? true : false;
					if(!empty($url_image)){
						$avatar_id = Utils::upload_image_by_meta($url_image, $title_image, $desc,false, 'user_avatar', $url_image, $is_local_img,false,false,$args_image);
						$copyright_text = !empty( $user_metas['user_image']['copyright']) ?  $user_metas['user_image']['copyright'] : "";
						if(!empty($copyright_text)){
							update_post_meta($avatar_id, 'copy_right_for_media', $copyright_text);
						}
					}
					if ( ! is_wp_error($avatar_id) ) {
						update_user_meta($user_id, 'user_avatar', $avatar_id);
						update_user_meta($user_id, 'avatar_user_upload_meta', $avatar_id);
						$user_thumbnail = wp_get_attachment_url($avatar_id);
						update_user_meta($user_id, 'user_thumbnail', $user_thumbnail);
					}
				}else {
					/*set other metas*/
					update_user_meta( $user_id, $metakey, $metavalue );
				}
			}
		}
	}

	/**
	 * update user roles
	 */
	private static function update_user_roles($user_id, $user_roles){

		if ( $user_roles ) {

			foreach( $user_roles as $user_role ){
				$user = new \WP_User( $user_id );
				$user->add_role( $user_role );
			}
		}
	}
}