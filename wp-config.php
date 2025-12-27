<?php
define( 'WP_CACHE', false ); // Added by WP Rocket

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */
if(strpos( $_SERVER['HTTP_HOST']  , 'local.transfertmarrakech.fr') !== false) :
// ** Database settings - You can get this info from your web host ** //
define( 'DB_NAME', 'transfertmarrakechdb' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', 'root' );
define( 'DB_HOST', 'db' );
define( 'DB_CHARSET', 'utf8' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 */
define('AUTH_KEY',         'raW8=1g(vjHj34kz}*Bb{B|ugF^cT8{4(&`zxk.P~mt/XC-eV+9wWXa(?5$wN{[N');
define('SECURE_AUTH_KEY',  'A/|{>K,-09j9%@%D|/MWu=A8`&|4Fqt{1o#5CCPjB}:eQ%cu{bJ5&DP(|@fbH6##');
define('LOGGED_IN_KEY',    '>skrkLO-+G%&:x-:{C-La&,RkTTv4.*QLtHqMeiAGSc2na!fWA$(bw[V+gI+CUDR');
define('NONCE_KEY',        '9R>&U]vsJ_aG)A~/o<cR_!C-6$$0k*qtmek|%D]j#5cLXQi5|Ub|_Uv2w1M$zZ(0');
define('AUTH_SALT',        'RB&QnnjH*(0+GR}~WaH0Buv%>>#G~Nz4H$cZ?hXf`-uu2Fv-9;~;aTJEoR(]gu(l');
define('SECURE_AUTH_SALT', 'GtW$R]OhX3KL5mA<<aq+$BSgio4v3;p-nSA)TOp6D9$ -(1e#?DFsAlz%RanXi1^');
define('LOGGED_IN_SALT',   'ze3v!]9rcT7DdJYZWD{Y/-]ImMwI2C^^-|J zK%ReA9D?F&]@&MVYddA*J6}1]m(');
define('NONCE_SALT',       '1u8/5^t$&R>G?)N;H+p| 66H$l[mKA*1:n{fzd|g2 e@@{w5zD|X%-K/|EQh8)xo');

$table_prefix = 'wp_';

define('WPLANG', 'fr_FR');

define('DOMAIN_CURRENT_SITE', 'local.transfertmarrakech.fr');

define( 'WP_CONTENT_DIR', dirname(__FILE__) . '/wp-content' );


define('COOKIE_DOMAIN', '');
define( 'UPLOADS','wp-content/uploads/transfertmarrakech' );
define( 'WP_HOME', 'http://local.transfertmarrakech.fr' );
define( 'WP_SITEURL', 'http://local.transfertmarrakech.fr' );

endif;

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define('WP_DEBUG', false );
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
