<?php

namespace Scripts\Shared;

trait Logger {

	// Possible values of $status_prefix are: _error OR _success 
	public static function registerLog($message, $status_prefix) {

		$status_prefix = !empty($status_prefix) && $status_prefix == '_success' ? '_success' : '_error';

		if (!defined('LOG_FILE')) {
			echo $message . PHP_EOL;
			return;
		}

		$filename = LOG_FILE.'_'.$status_prefix . '.log';

		$folder = dirname(__FILE__) . '/logs/' . dirname(LOG_FILE);

		// Create the folder if it does not exist
		if (!is_dir($folder)) {
			mkdir($folder, 0755, true);
		}

		$file_path = dirname(__FILE__) . '/logs/' . $filename;

		$log_line = sprintf("[%s] %s", date('Y-m-d H:i:s'), $message) . PHP_EOL;

		file_put_contents($file_path, $log_line, FILE_APPEND | LOCK_EX);
	}
}