<?php
/**
 * Functions and definitions
 * 
 * @package TransfertMarrakech
 * @since 1.0.0
 */

// Empêche l'accès direct
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Définit les constantes du thème
define( 'TM_VERSION', '1.0.0' );
define( 'TM_THEME_DIR', get_template_directory() );
define( 'TM_THEME_URI', get_template_directory_uri() );
define( 'TM_INC_DIR', TM_THEME_DIR . '/inc' );

// Charge l'autoloader
require_once TM_INC_DIR . '/Autoloader.php';

// Enregistre l'autoloader
$autoloader = new TM\Autoloader( TM_INC_DIR, 'TM' );
$autoloader->register();

// Initialise le thème
TM\Core\Theme::init();

/**
 * Flush les rewrite rules lors de l'activation du thème
 * Nécessaire pour que les nouvelles URLs des CPT fonctionnent
 */
\add_action( 'after_switch_theme', function() {
	\flush_rewrite_rules();
} );
