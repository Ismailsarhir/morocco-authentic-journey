<?php

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");

$_optins = getopt('', ['post_id::', 'limit::', 'force_update::', 'first_exec::']);
$post_id = !empty($_optins['post_id']) ? intval($_optins['post_id']) : 0;
$limit = !empty($_optins['limit']) ? $limit : 100;
$force_update = !empty($_optins['force_update']) ? true : false;
$first_exec = !empty($_optins['first_exec']) ? true : false;

define('LOG_FILE', basename(__DIR__)."/import-baby-names");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerPosts;

$args = ['ressource' => "babyname" ];
$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', $args);


print_flush("== Start Script\n");
$content_type = "babyname";

if(!empty($post_id)) {

	$force_update = true;

	$post = $migration->getPostOne($post_id, "babyname");

	if(!empty($post)) {
		$postdata = $migration->preparePostData($post, $content_type);

		if(!empty($postdata)) {
			$post_id = HandlerPosts::insert_post($postdata, $force_update);
			println_flush("<<{$postdata['old_id']}>> : is traited => new ID: << {$post_id} >>");
		}
	}

	file_exists(LOCK_FILE) && unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

$option_name = "last_{$content_type}_traited";
$default_last_traited_id = 9999999999;

if($first_exec) {
	$last_old_id = $default_last_traited_id;
}else {
	$last_old_id = get_option($option_name, $default_last_traited_id);
}


$first = true;
$index = 1;

$offset = $foundRows = 0;
$args['limit'] = $limit;

do {
	$args['offset'] = $offset;
	$args['content_type'] = $content_type;
	$args['last_old_id'] = $last_old_id;

	if($first){
		$args['first'] = true;
		$first = false ;
	}

	$posts = $migration->getPosts($args, "babynames");
	if($foundRows == 0){
		$foundRows = $migration->get_foundRows();
	}

	$posts_count = count($posts);
	if(!empty($posts)) {
		foreach ($posts as $post) {
			println_flush("=== Post <<{$index} / {$foundRows}>>");
			$postdata = $migration->preparePostData($post, $content_type);
			if(!empty($postdata)) {
				$old_id = $postdata['old_id'];
				$post_id = HandlerPosts::insert_post($postdata, $force_update);
				println_flush("Index <<{$index}>> | <<{$old_id}>> : is traited => new ID: <<{$post_id}>>");
			}
			unset($postdata);
			$index++;
		}
		unset($posts);
	}

	if($posts_count == $limit) {
		$offset += $limit;
	}

	if(!empty($old_id)) {
		update_option($option_name , $old_id);
		unset($old_id);
	}

} while ($posts_count == $limit);

file_exists(LOCK_FILE) && unlink(LOCK_FILE);
print_flush("== End Script\n");
