<?php

/**
 * php add_meta_category_tags_to_tags.php --host=aufeminin.rw.loc.env
 */

include dirname(__FILE__).'/../../../vendor/autoload.php';
include(dirname(__FILE__) . "/../../../../init_script.php");


$_optins = getopt('', ['page::']);
$page_index = !empty($_optins['page']) ? intval($_optins['page']) : 1;
$ressource = "tag";

define('LOG_FILE', basename(__DIR__)."/add-child-tags-{$ressource}s");

use Scripts\GetDatas\MigrationAdapter;
use Scripts\Classes\HandlerCategories;

$migration = new MigrationAdapter('Scripts\GetDatas\MigrationEnfemenino');

print_flush("== Start Script\n");


$i = 1;
$j = 0;
do {

	$data = $migration->get_api_client()->fetch($ressource,['page' => $page_index]);
	list($terms, $page_count) = $migration->get_api_client()->fetch($ressource, ['page' => $page_index]);
	
	println_flush("=== Index << $page_index / $page_count >>");
	if(empty($terms["tag"])){
		continue;
	}

	foreach ($terms["tag"] as $key => $tag) {
		$added = false;
		if(!empty($tag["tagChildrenID"]) && !empty($tag["tagChildren"]) && is_array($tag["tagChildrenID"]) && $tag_id = get_tag_id_by_old_id($tag["tagID"])){
			$new_ids = [];
			$valide_ids = [];

			// find online tags
			foreach($tag["tagChildren"] as $children_object){
				if(!empty($children_object["tagID"]) && $children_object["pageOnline"] == "1"){
					$valide_ids[] = $children_object["tagID"];
				}
			}

			$tag["tagChildrenID"] = array_intersect($valide_ids,$tag["tagChildrenID"]);
			if(!empty($tag["tagChildrenID"])){
				foreach($tag["tagChildrenID"] as $children_id){
					$new_id = get_tag_id_by_old_id($children_id);
					if(!empty($new_id) && !in_array($new_id,$new_ids)){
						$new_ids[] = $new_id;
					}
				}
			}

			if(!empty($new_ids)){
				$added = true;
				$new_ids = implode(",",$new_ids);
				update_term_meta($tag_id,"category_tags",$new_ids);
			}
		}
		if($added){
			$j++;
			println_flush("$i => Tag << {$tag['tagID']} <|> $tag_id >>  was successfully updated with the 'category_tags' meta.");
		}else{
			println_flush("$i => Tag << {$tag['tagID']} >> was NOT updated. No valid children or tag not found.");
		}
		$i++;
	}

	$page_index++;
} while ($page_index <= $page_count);


println_flush("Total updated : $j");
println_flush("Total not found : ".($i-$j));

unlink(LOCK_FILE);
print_flush("== End Script\n");

function get_tag_id_by_old_id($old_id) {

	static $cache_old_ids = [];
	if(!empty($cache_old_ids[$old_id])){
		return $cache_old_ids[$old_id];
	}

	$term = HandlerCategories::get_taxonomy_by_meta(['post_tag','category'],"post_tag_old_id",$old_id);

	return $cache_old_ids[$old_id] = !is_wp_error($term) && !empty($term) ? $term->term_id : false;
}