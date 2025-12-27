<?php

/**
 * Script d'import des admins
 */

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");

//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

$_optins = getopt('', ['admin_id::', 'limit::', 'first_exec::', 'force_update::']);
$admin_id = !empty($_optins['admin_id']) ? intval($_optins['admin_id']) : 0;
$limit = !empty($_optins['limit']) ? intval($_optins['limit']) : 100;
$first_exec = !empty($_optins['first_exec']) ? true : false;
$force_update = !empty($_optins['force_update']) ? true : false;

define('LOG_FILE', basename(__DIR__)."/import-admins");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerUsers;

$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', [
	'ressource' => 'admin'
]);

print_flush("== Start Script\n");

if(!empty($admin_id)) {
	$force_update = true;
	$args = [
		'admin_id' => $admin_id,
		'ressource' => 'admin'
	];
	
	$admin = $migration->getUsers($args);

	if(!empty($admin)) {
		$admin_data = $migration->prepareAdmin($admin);		
		if (!empty($admin_data)) {
			$stored_user = HandlerUsers::insert_user($admin_data, $force_update);
			println_flush("admin {$admin_id} : is traited => new ID : " .$stored_user);
		}
	}

	unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

$option_name = "last_admins_traited";
$default_last_traited_id = 9999999999;

if($first_exec) {
	$last_old_id = $default_last_traited_id;
}else {
	$last_old_id = get_option($option_name, $default_last_traited_id);
}

$first = true;
$index = 1;

$offset = $foundRows = $total = 0;

$args = [
	'ressource' => 'admins',
	'limit' => $limit,
	'last_old_id' => $last_old_id
];

do {
	$admin_old_id = null;
	$args['offset'] = $offset;

	if($first){
		$args['first'] = true;
		$first = false ;
	}

	$all_admins = $migration->getUsers($args);

	if($foundRows == 0){
		$foundRows = $migration->get_foundRows();
		$total = ceil($foundRows / $limit);
	}
	
	if(!empty($all_admins)) {
		$admins_count = count($all_admins);
		println_flush("\n====== Page <<{$index} / {$total}>> ======\n");

		foreach ($all_admins as $admin) {
			$admin_data = $migration->prepareAdmin($admin);
			if (!empty($admin_data)) {
				$admin_old_id = $admin_data['user_metas']['old_admin_id'];
				$stored_user = HandlerUsers::insert_user($admin_data, $force_update);
				println_flush("admin <<{$admin_old_id}>> : is traited => new ID : " .$stored_user);
			}
		}
		unset($all_admins);

		$index++;
	}

	if($admins_count == $limit) {
		$offset += $limit;
	}

	if(!empty($admin_old_id)) {
		update_option($option_name , $admin_old_id);
		unset($admin_old_id);
	}

} while ($admins_count == $limit);

unlink(LOCK_FILE);
print_flush("== End Script\n");