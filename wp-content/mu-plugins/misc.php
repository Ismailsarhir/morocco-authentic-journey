<?php

add_action('setup_theme', function() {

	defined('RW_THEME_DIR') OR define('RW_THEME_DIR', get_template_directory());
	defined('RW_THEME_DIR_URI') OR define('RW_THEME_DIR_URI', get_template_directory_uri());
	defined('STYLESHEET_DIR') OR define('STYLESHEET_DIR', get_stylesheet_directory());
	defined('STYLESHEET_DIR_URI') OR define('STYLESHEET_DIR_URI', get_stylesheet_directory_uri());

});

function get_param_global(string $param, mixed $default = ''): mixed {
	global $site_config;
	$value = $site_config[$param] ?? $default;
	$value = apply_filters('param_global_' . $param, $value);
	return $value;
}

function set_param_global(string $param, mixed $value): void {
	global $site_config;
	$site_config[$param] = $value;
}
