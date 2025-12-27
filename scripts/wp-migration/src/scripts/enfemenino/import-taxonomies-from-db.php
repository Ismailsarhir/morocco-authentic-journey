<?php

/**
 * Script pour importer les taxonomies depuis la BD au lieu de l'API
 */

ini_set('memory_limit', '1000M');
define('WP_MAX_MEMORY_LIMIT' , '1000M');

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

$_optins = getopt('', ['taxonomy_id::', 'limit::', 'taxonomy_name::']);
$taxonomy_id = !empty($_optins['taxonomy_id']) ? intval($_optins['taxonomy_id']) : 0;
$limit = !empty($_optins['limit']) ? intval($_optins['limit']) : 100;
$taxonomy_name = !empty($_optins['taxonomy_name']) ? $_optins['taxonomy_name'] : '';

if(empty($taxonomy_name)) {
	unlink(LOCK_FILE);
	print_flush("== Il faut preciser le taxonomy_name\n");
	exit();
}

define('LOG_FILE', basename(__DIR__)."/import-{$taxonomy_name}s");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerCategories;
use Scripts\Classes\HandlerCostumTables;

$args = ['ressource' => $taxonomy_name];
$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', $args);

$taxonomy_type = $migration->get_term_type();

$babyname_list_table_name = 'wp_baby_names_list_content';
$babyname_list_old_id_name = 'old_babyname_list_content';

print_flush("== Start Script\n");

if(!empty($taxonomy_id)) {
	$term = $migration->getTaxonomie($taxonomy_name, $taxonomy_id);
	if(!empty($term)) {
		$term_id = HandlerCategories::insert_taxonomy($term, $taxonomy_type);
		if(!empty($term_id)) {
			$attached_babynames = $migration->get_baby_names_list_content($taxonomy_id, $term_id);
			if(!empty($attached_babynames)) {
				foreach($attached_babynames as $babyname) {
					$insert = HandlerCostumTables::insert_data($babyname_list_table_name, $babyname_list_old_id_name, $babyname);
				}
			}
		}
	}

	unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

$first = true;
$index = 1;

$offset = $total_items = 0;
$args['limit'] = $limit;

do {
	$args['offset'] = $offset;
	if($first){
		$args['first'] = true;
		$first = false ;
	}
	$terms = $migration->getTaxonomies("{$taxonomy_name}s", $args);

	if($total_items == 0){
		$total_items = $migration->get_foundRows();
	}

	$count_terms = count($terms);
	if(!empty($terms)) {
		foreach ($terms as $term) {
			println_flush("=== Item <<{$index} / {$total_items}>>");
			$term_id = HandlerCategories::insert_taxonomy($term, $taxonomy_type);
			$old_id = $term->get_old_id();
			if(!empty($term_id) && !empty($old_id)) {
				$attached_babynames = $migration->get_baby_names_list_content($old_id, $term_id);
				if(!empty($attached_babynames)) {
					foreach($attached_babynames as $babyname) {
						$insert = HandlerCostumTables::insert_data($babyname_list_table_name, $babyname_list_old_id_name, $babyname);
					}
				}
			}
			$index++;
		}
	}

	if($count_terms == $limit) {
		$offset += $limit;
	}
} while ($count_terms == $limit);

unlink(LOCK_FILE);
print_flush("== End Script\n");