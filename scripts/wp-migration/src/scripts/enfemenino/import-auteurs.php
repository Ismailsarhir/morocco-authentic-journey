<?php

/**
 * Script d'import des auteurs ( users avec role auteur )
 */

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

$_optins = getopt('', ['author_id::', 'paged::', 'first_exec::', 'force_update::']);
$author_id = !empty($_optins['author_id']) ? intval($_optins['author_id']) : 0;
$paged = !empty($_optins['paged']) ? intval($_optins['paged']) : 0;
$first_exec = !empty($_optins['first_exec']) ? true : false;
$force_update = !empty($_optins['force_update']) ? true : false;

define('LOG_FILE', basename(__DIR__)."/import-authors");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerUsers;

$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', [
	'base_url' => 'https://www.aufeminin.com/reloaded/manage/api/',
	'ressource' => 'author'
]);

print_flush("== Start Script\n");

if(!empty($author_id)) {
	$force_update = true;
	$args = [
		'author_id' => $author_id,
		'ressource' => 'author'
	];
	
	$author = $migration->getUsers($args);

	if(!empty($author)) {
		$author_data = $migration->prepareUser($author);
		
		if (!empty($author_data)) {
			$stored_user = HandlerUsers::insert_user($author_data, $force_update);
			println_flush("author {$author_id} : is traited => new ID : " .$stored_user);
		}
	}

	unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

$option_name = "last_authors_page_traited";

if($first_exec) {
	$paged = 1;
} else {
	$paged = $paged ? $paged : get_option($option_name, 1);
}

do {
	$authors = $migration->getUsers([
		'page' => $paged,
		'ressource' => 'authors'
	]);
	
	$page_count = $migration->get_foundRows();
	
	println_flush("\n====== Page <<{$paged} / {$page_count}>> ======\n");
	if(!empty($authors)) {
		foreach ($authors as $author) {
			$author_data = $migration->prepareUser($author);
			if (!empty($author_data)) {
				$author_old_id = $author_data['user_metas']['old_author_id'];
				$stored_user = HandlerUsers::insert_user($author_data, $force_update);
				println_flush("author <<{$author_old_id}>> : is traited");
			}
		}
		unset($authors);
	}

	$paged++;

	update_option($option_name , $paged);

} while ($paged <= $page_count);

unlink(LOCK_FILE);
print_flush("== End Script\n");