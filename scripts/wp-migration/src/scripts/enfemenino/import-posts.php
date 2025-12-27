<?php

/**
 * edito-feed: ce sont les articles normaux 
 * album : ce sont les articles diaporamas
 */

define('WP_ADMIN', 1); //baypass total cache
define('DO_NOT_PURGE_URL', true);
define('DO_NOT_GENERATE_ATTACHMENT', 1);

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

remove_action('pre_get_posts', 'add_ep_integrate_to_queries');

$_optins = getopt('', ['post_id::', 'paged::', 'ressource::', 'first_exec::', 'force_update::', 'edito_album::', 'page_start::', 'page_end::', 'updated_since::']);
$post_id = !empty($_optins['post_id']) ? intval($_optins['post_id']) : 0;
$paged = !empty($_optins['paged']) ? intval($_optins['paged']) : 0;
$ressource = !empty($_optins['ressource']) ? $_optins['ressource'] : 'edito-feed';
$first_exec = !empty($_optins['first_exec']) ? true : false;
$force_update = !empty($_optins['force_update']) ? true : false;
$edito_album = !empty($_optins['edito_album']) ? true : false;
$page_start = !empty($_optins['page_start']) ? intval($_optins['page_start']) : 0;
$page_end = !empty($_optins['page_end']) ? intval($_optins['page_end']) : 0;
$updated_since = !empty($_optins['updated_since']) ? $_optins['updated_since'] : '';

define('LOG_FILE', basename(__DIR__)."/import-{$ressource}s");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerPosts;
use Scripts\Common\Utils;
use \Scripts\Shared\CommonFunctions;

$args = ['ressource' => $ressource];
if($ressource == 'album') {
	$args['extra_args']['full'] = 1;
	if($edito_album) {
		$args['extra_args']['edito'] = 1;
	}
} elseif($ressource == 'edito-feed') {
	$args['extra_args']['ft'] = 'aufem-migration';
	if(!empty($updated_since) && CommonFunctions::validateDate($updated_since)) {
		$args['extra_args']['updated_content_since'] = $updated_since;
	}
}
$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', $args);


$content_type = str_replace('-', '_', $ressource);

print_flush("== Start Script\n");

if(!empty($post_id)) {

	$force_update = true;

	$post_obj = $migration->getPostOne($post_id, 'post');

	if(!empty($post_obj)) {
		$postdata = $migration->preparePostData($post_obj, $content_type);

		if(!empty($postdata)) {
			$post_id = HandlerPosts::insert_post($postdata, $force_update);
			println_flush("<<{$postdata['old_id']}>> : is traited => new ID: <<{$post_id}>>");
		}
	}

	file_exists(LOCK_FILE) && unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

$option_name = "last_{$content_type}_traited";

if($first_exec) {
	$page_index = 1;
} else {
	$page_index = $paged ? $paged : get_option($option_name, 1);
}

if($page_start) {
	$page_index = $page_start;
}

$index = 1;

do {
	$args['page'] = $page_index;
	$args['content_type'] = $content_type;

	$first_time_fetch = time();
	$posts = $migration->getPosts($args, 'posts');
	println_flush("==== Fetched time is : ".Utils::calculate_time_spent($first_time_fetch));

	$page_count = $migration->get_foundRows();

	println_flush("=== Page <<{$page_index} / {$page_count}>>");

	if(!empty($posts)) {
		foreach ($posts as $p) {
			$first_time_prep = time();
			$postdata = $migration->preparePostData($p, $content_type);
			println_flush("==== Prepared time is : ".Utils::calculate_time_spent($first_time_prep));

			if(!empty($postdata)) {
				$first_time_insert = time();
				$post_id = HandlerPosts::insert_post($postdata, $force_update);
				println_flush("==== Inserted time is : ".Utils::calculate_time_spent($first_time_insert));

				println_flush("Index <<{$index}>> | <<{$postdata['old_id']}>> : is traited => new ID: <<{$post_id}>>");
				$index++;
			}
		}
	}

	$page_index++;

	update_option($option_name , $page_index);

    $page_count_condition = !empty($page_count) ? $page_index <= $page_count : true;
    $page_end_condition = !empty($page_end) ? $page_index <= $page_end : true;

} while ($page_count_condition && $page_end_condition);

file_exists(LOCK_FILE) && unlink(LOCK_FILE);
print_flush("== End Script\n");