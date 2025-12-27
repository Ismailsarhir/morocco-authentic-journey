<?php

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

$_optins = getopt('', ['post_id::', 'force_update::','limit::']);
$post_id = !empty($_optins['post_id']) ? intval($_optins['post_id']) : 0;
$force_update = !empty($_optins['force_update']) ? true : false;
$limit = !empty($_optins['limit']) ? $limit : 10;

define('LOG_FILE', basename(__DIR__)."/import-pages");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerPosts;

$args = ['ressource' => "page" ];
$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', $args);

$ops_pages = [712,639,1037,339,783,1260,569,2035,1996,253,1999,1988,391,166,532,2004];

print_flush("== Start Script\n");
$content_type = "page";

if(!empty($post_id)) {
	if(in_array($post_id, $ops_pages)) {
		print_flush("== id d'ops page \n");
		end_script();
	}
	$force_update = true;

	$post = $migration->getPostOne($post_id, "page");

	if(!empty($post)) {
		$postdata = $migration->preparePostData($post, $content_type);

		if(!empty($postdata)) {
			$post_id = HandlerPosts::insert_post($postdata, $force_update);
			println_flush("<<{$postdata['old_id']}>> : is traited => new ID: << {$post_id} >>");
		}
	}

	end_script();
}

$option_name = "last_{$content_type}_traited";


$first = true;
$index = 1;

$offset = $foundRows = 0;
$args['limit'] = $limit;

do {
	$args['offset'] = $offset;
	$args['content_type'] = $content_type;
	if($first){
		$args['first'] = true;
		$first = false ;
	}
	$posts = $migration->getPosts($args, "pages");
	if($foundRows == 0){
		$foundRows = $migration->get_foundRows();
	}

	$posts_count = count($posts);
	if(!empty($posts)) {
		foreach ($posts as $post) {
			if(!empty($post['StaticPageId']) && in_array($post['StaticPageId'], $ops_pages)) {
				print_flush("== id d'ops page \n");
				continue;
			}
			println_flush("=== Page <<{$index} / {$foundRows}>>");
			$postdata = $migration->preparePostData($post, $content_type);
			if(!empty($postdata)) {
				$post_id = HandlerPosts::insert_post($postdata, $force_update);
				println_flush("Index <<{$index}>> | <<{$postdata['old_id']}>> : is traited => new ID: <<{$post_id}>>");
			}
			$index++;
		}
	}

	if($posts_count == $limit)
	{
		$offset+= $limit;
	}


} while ($posts_count == $limit);

end_script();

function end_script() {
	file_exists(LOCK_FILE) && unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}