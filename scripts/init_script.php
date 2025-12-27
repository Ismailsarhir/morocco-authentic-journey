<?php

defined('DISABLE_ALL_PLUGINS') or defined('DISABLE_PLUGINS') or define('DISABLE_PLUGINS', []);

// fix error total cache
if(!defined( 'WP_ADMIN' )){
	define( 'WP_ADMIN', 0 ); 
}

define( 'INIT_SCRIPT', 1 ); 

//$sapi_type = php_sapi_name();
error_reporting(E_ALL);
set_time_limit(0);


$host = @$argv[1];


$options = getopt('', ['host::','start::','end::']);
if(isset($options['host']))
	$host = $options['host'];

check_hour_limit();

$use_console = (php_sapi_name() == 'cli');
$args_copy = $argv;
$script_name = basename( array_shift($args_copy));

if( $use_console ){
	defined('USE_CONSOLE') or define('USE_CONSOLE' , true );
	$rw_linebreak = "\n";
	$_SERVER['REQUEST_URI'] = '/' ;
}else{
	$rw_linebreak = "<br/>\n";
}

if(defined('USE_CONSOLE')){
	if( defined('DO_MONITOR') && defined('DG_CHECK')) {
		$dg_tags = array( 'script' => $script_name  , 'args' => implode(',',$args_copy) ) ;
	}
}

if ($use_console && !$host) {
    echo ("Il faut mettre le 1er parametre qui concerne le host exemle content.viepratique.fr\n");
    exit();
}
if ($use_console) {
    define('HTTP_HOST', $host);

    $_SERVER['HTTP_HOST'] = $host;
    $_SERVER['SERVER_NAME'] = $host;
}

defined('WP_ADMIN') or define('WP_ADMIN', 1); //baypass total cache

if(!defined('LOCK_FILE')){

	$lock_dir = dirname(__FILE__) . '/lock/';
	if (!file_exists($lock_dir)) {
	    @mkdir($lock_dir);
	}

	$script_name = '' ;
	foreach ($argv as  $value) {
		$script_name .= str_replace('/', '_', $value); 
	}


	define('LOCK_FILE', $lock_dir  . $script_name .'.lock');
	echo LOCK_FILE ;
}

$debug_optins = getopt('', [ 'debug::']);
$debug = !empty($debug_optins['debug'])?? false ;

if ( !$debug AND defined('LOCK_FILE') ) {
	

	if(defined('CHECK_SHELL_PARAMS') OR true ){
		$ps_ax = shell_exec('ps ax');

		// delete lock_file if exists and only this current script is executed 
		$script_cmd = implode('( +)', $argv) ;
		$script_cmd = str_replace(array('.', '/'), array('\.', '\/'), $script_cmd) ;
		//$script_cmd  = '/' . $script_cmd  .'/';
		$script_cmd  = '/[0-9] *php *' . $script_cmd  .'/';



		//echo "\n\n\n\n\n\n\n ------> $script_cmd    \n\n\n\n\n\n" ;

		if(file_exists(LOCK_FILE ) AND preg_match_all($script_cmd , $ps_ax, $matches) AND count($matches[0]) <= 1){ 
			unlink(LOCK_FILE);
		}elseif(file_exists(LOCK_FILE )){
			echo(" CHECK_SHELL_PARAMS  no unlink regix $script_cmd  number prosess : $script_cmd : " .count($matches[0]) . "\n");	
		}
	}else{

		//if(defined('CHECK_SHELL')){
			$ps_ax = shell_exec('ps ax');
			// delete lock_file if exists and only this current script is executed 
			if((substr_count($ps_ax, $script_name) == 1 || substr_count($ps_ax, $script_name) == 2) && file_exists(LOCK_FILE )){ 
				unlink(LOCK_FILE);
			}

			$regex = '#\/bin\/sh.*' . $script_name . '#' ;

			if(file_exists(LOCK_FILE ) AND preg_match_all($regex , $ps_ax, $matches) AND count($matches[0]) == 1){ 
				unlink(LOCK_FILE);
			}else{
				//echo "\n\n\n\n- " . count($matches[0]) . "- \n\n\n\n" ;

			}

		//}
	}


	if( !file_exists(LOCK_FILE ))
		touch(LOCK_FILE );
	else {
		//trigger_error( 'Lock File Present '. LOCK_FILE  , E_USER_ERROR );
		echo( 'Lock File Present '. LOCK_FILE ."\n" );
		exit();
	}
}


// load env config
if (file_exists(dirname(__FILE__) . '/config.php')) {
    require_once (dirname(__FILE__) . '/config.php');
}

//set_time_limit (  5000 ) ;
define("WP_PATH", dirname(__FILE__) . '/../');
require_once (WP_PATH . 'wp-load.php');

if ( !$use_console && !is_user_logged_in() ) {
	echo 'Vous devez vous connecter pour exécuter cette tache' ;
	exit();
}

$_SERVER['SERVER_PROTOCOL'] = null;


add_filter('posts_request', function ( $request, $query ){
    if($query->is_main_query() && !$query->is_admin()){
    	return false;
	}
	return $request ;
}, 10, 2);

$is_not_script_import = defined('IS_NOT_SCRIPT_IMPORT') && IS_NOT_SCRIPT_IMPORT ;
if(!$is_not_script_import){
	define('DO_NOT_UPDATE_TERM_COUNT', 1);
	remove_all_actions('save_post');

	$posts_types = rw_get_post_types();
	foreach ($posts_types as $post_type) {
		if('post' != $post_type){
			remove_all_actions('save_post_'.$post_type);
		}
	}
}

set_time_limit(0);
if (!$use_console)
	header("HTTP/1.1 200 OK" , true, 200);

// Force implicite flush
ob_implicit_flush(true);

function lock_required_file( $file ) {
	$fp = fopen( $file, 'c' );

	if ( flock( $fp, LOCK_EX ) ) { // acquière un verrou exclusif
		$content = require( $file );

		fflush( $fp ); // libère le contenu avant d'enlever le verrou
		flock( $fp, LOCK_UN ); // libère le verrou
		fclose( $fp );

		return $content;
	}
	else {
		print_flush( 'Unable to lock the file ! : ' . $file );
		usleep( 10000 );
		fclose( $fp );
		lock_required_file( $file );
	}
}

function date_system($format){


	exec('date +"'. $format .'"', $output) ;
	return $output[0] ;

}


function check_hour_limit(){

	$t = date_system('%H') ;
	$options = getopt('', ['start_hour::','end_hour::']);
	$start = (!empty($options['start_hour'])) ? $options['start_hour']  : null;  
	$end = (!empty($options['end_hour'])) ? $options['end_hour'] : null; 

	if (empty($start) || empty($end)) {
		return true;
	}

	if($start > $end){

		$start1 = $start;
		$end1 = 24 ;

		$start2 = 0 ;
		$end2 = $end;

		if(($t >= $start1 &&  $t < $end1 ) || ($t >= $start2 &&  $t < $end2 )){
			return true;
		}else{
			echo "exit date limit : ". date_system('%d/%m/%Y %H:%M') . "\n" ;
			exit();
		}

	}else{
		if($t >= $start && $t < $end ){
			return true;
		}else{
			echo "exit date limit : ". date_system('%d/%m/%Y %H:%M') . "\n" ;
			exit();
		}
	}
}

add_action("rw_before_wp_update_post","preserve_post_modification_time");
function preserve_post_modification_time($origin_args){
	add_filter( 'wp_insert_post_data', function (  $data, $postarr) use($origin_args) {

		if(!is_array($origin_args)){
			return $data ;
		}

		// post_modified
		if(empty($origin_args["post_modified"]) && !empty($postarr['post_modified'])){
			// override core <=> get current time 
			$data['post_modified'] = $postarr['post_modified'];
		}

		// post_modified_gmt
		if(empty($origin_args["post_modified_gmt"]) && !empty($postarr['post_modified_gmt'])){
			// override core <=> get current time 
			$data['post_modified_gmt'] = $postarr['post_modified_gmt'];
		}

		return $data;
	}, 10, 2 );
}



function rw_get_post_types() {
	$return = get_post_types( array('public' => true)) ;
	$excluded_post_types = ['attachment', 'nltemplate', 'newsletter'];
	$return = array_diff($return, $excluded_post_types);
	return $return ;
}

function print_flush($s) {
	echo $s;
	@ob_flush();
	@flush();
}

function println_flush($s) {
	print_flush($s."\n");
}

function println_error_flush($s) {
	print_flush( "\033[31m$s \033[0m\n");
}

function println_warning_flush($s) {
	print_flush("\033[33m$s \033[0m\n");
}

function println_info_flush($s) {
	print_flush("\033[36m$s \033[0m\n");
}

function println_success_flush($s) {
	print_flush("\033[32m$s \033[0m\n");
}
