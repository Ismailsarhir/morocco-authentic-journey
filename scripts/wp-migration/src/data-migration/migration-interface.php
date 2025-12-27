<?php

/**
 * Migration interface
 *
 * @author Brahim Ibrahimi
 */
namespace Scripts\GetDatas;

interface MigrationInterface {

	public function getTaxonomies($taxonomy_name, $args);
	public function getTaxonomie($taxonomy_name, $old_id);
	public function getUsers($args);
	public function getPosts($args, $content_type);
	public function getPostOne($old_id, $content_type);
	public function preparePostData($post_data, $content_type);
	public function preparePostBasicsData($post_data, $content_type);
	public function prepareUser($user_data);
	public function getComments($args);
	public function prepareComment($comment_data);

}