<?php

/**
 * section/subsection: sont les catégorie pour WP
 * tag : les etiquettes pour WP
 */

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


//media files
include_once (ABSPATH . "wp-admin/includes/media.php");
include_once (ABSPATH . "wp-admin/includes/file.php");
include_once (ABSPATH . 'wp-admin/includes/image.php');

$_optins = getopt('', ['taxonomy_id::', 'page::', 'ressource::']);
$taxonomy_id = !empty($_optins['taxonomy_id']) ? intval($_optins['taxonomy_id']) : 0;
$page = !empty($_optins['page']) ? intval($_optins['page']) : 1;
$ressource = !empty($_optins['ressource']) ? $_optins['ressource'] : 'section';

define('LOG_FILE', basename(__DIR__)."/import-{$ressource}s");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerCategories;

$args = ['ressource' => $ressource];
$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino', $args);

$taxonomy_type = $migration->get_term_type();

print_flush("== Start Script\n");

if(!empty($taxonomy_id)) {
	$term = $migration->getTaxonomie('term', $taxonomy_id);
	if(!empty($term)) {
		HandlerCategories::insert_taxonomy($term, $taxonomy_type);
	}

	unlink(LOCK_FILE);
	print_flush("== End Script\n");
	exit();
}

do {
	$args['page'] = $page;
	$terms = $migration->getTaxonomies('terms', $args);

	$page_count = $migration->get_foundRows();

	println_flush("=== Index << $page / $page_count >>");

	foreach ($terms as $term) {
		HandlerCategories::insert_taxonomy($term, $taxonomy_type);
	}

	$page++;
} while ($page <= $page_count);

unlink(LOCK_FILE);
print_flush("== End Script\n");