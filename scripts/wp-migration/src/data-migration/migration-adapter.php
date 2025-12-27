<?php

/**
* Migration Doctissimo data
*
* @author Brahim Ibrahimi
*/
namespace Scripts\GetDatas;

use Scripts\GetDatas\MigrationInterface;

class MigrationAdapter implements MigrationInterface {

	private $siteInstance;

	public function __construct($name, $config_args = []) {
		$this->siteInstance = new $name($config_args);
	}
	public function __call($method, $args)
	{
		if(!empty($this->siteInstance) && !empty($method)){
			if(!method_exists($this->siteInstance , $method)){
				print_flush("method '$method' not defined !!! \n");
				return false;
			}
			return call_user_func_array(array( $this->siteInstance, $method), $args);
		}
	}

	public function getTaxonomies($taxonomy_name='category', $args = []) {
		return $this->siteInstance->getTaxonomies($taxonomy_name, $args);
	}

	public function getTaxonomie($taxonomy_name = "category", $old_id){
		return $this->siteInstance->getTaxonomie($taxonomy_name, $old_id);
	}

	public function getUsers($args = []) {
		return $this->siteInstance->getUsers($args);
	}

	public function getPosts($args, $content_type='post') {
	    return $this->siteInstance->getPosts($args, $content_type);
	}

	public function getPostOne($old_id, $content_type='post', $by_field = 'node_id') {
	    return $this->siteInstance->getPostOne($old_id, $content_type, $by_field);
	}

	public function getDataFromCustomTable($table, $limit, $offset, $args = []) {
		return $this->siteInstance->getDataFromCustomTable($table, $limit, $offset, $args);
	}

	public function preparePostData($post_data, $content_type='post') {
		return $this->siteInstance->preparePostData($post_data, $content_type);
	}

	public function preparePostBasicsData($post_data, $content_type='post') {
		return $this->siteInstance->preparePostBasicsData($post_data, $content_type);
	}

	public function prepareUser($user_data) {
		return $this->siteInstance->prepareUser($user_data);
	}

	public function getComments($args) {
		return $this->siteInstance->getComments($args);
	}

	public function prepareComment($comment_data) {
		return $this->siteInstance->prepareComment($comment_data);
	}
	public function get_foundRows() {
		return $this->siteInstance->get_foundRows();
	}
	public function get_instance() {
		return $this->siteInstance ;
	}
	
}