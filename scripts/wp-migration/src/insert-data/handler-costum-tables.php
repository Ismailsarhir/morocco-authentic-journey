<?php

namespace Scripts\Classes;

use \Scripts\Shared\Logger;

class HandlerCostumTables {

	use Logger;

	const ERROR_FILE_PREFIX = '_error';
	const SUCCESS_FILE_PREFIX = '_success';

	/**
	 * Insert By Old Data.
	 * 
	 * Paramas : 
	 *  - data => single row from old table
	 *  - table_name => nom de table wordpress
	 *  - name_old_id => nom de row_id de l'ancien table
	 */
	public static function insert_data($table_name, $name_old_id, $data) {
		global $wpdb;

		$item_id = 0;

		if(!empty($table_name) && !empty($data) && !empty($name_old_id)){
			$row = self::get_row_if_exist($table_name, $data[$name_old_id], $name_old_id);
  
			if(!$row){
				$inserted = $wpdb->insert($table_name , $data);
				$item_id = $wpdb->insert_id;

				if($inserted){
					/*use finction registerLog of trait Logger to log messages*/
					$log_message = "row imported << old_id: ".$data[$name_old_id]." , ".date('Y-m-d H:i:s');
					self::registerLog($log_message, self::SUCCESS_FILE_PREFIX);
				}else{
					$error_message = "import error << old_id: ".$data[$name_old_id]." , ".date('Y-m-d H:i:s');
					self::registerLog($error_message, self::ERROR_FILE_PREFIX);
				}
			}else{
				$item_id = $row->id;
				$updated = $wpdb->update($table_name , $data, array('id' => $item_id));

				if($updated){
					$error_message = "row updated << old_id: ".$data[$name_old_id]."  updated";
					self::registerLog($error_message, self::SUCCESS_FILE_PREFIX);
				}
			}
			return $item_id;
		}else{
			return false;
		}
	}

	private static function get_row_if_exist($table_name, $value_old_id, $name_old_id){
		global $wpdb;

		$query = "SELECT * FROM $table_name WHERE $name_old_id = \"$value_old_id\"";

		$unit = $wpdb->get_row($query);

		return !empty($unit) ? $unit : null;

	}

	
}