<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //




if (file_exists(dirname(__FILE__)."/wp-config.local.php")) {

    include(dirname(__FILE__)."/wp-config.local.php");
} else {
	/** The name of the database for WordPress */
	define('DB_NAME', 'dn');

    /** MySQL database username */
    define('DB_USER', 'wp');

    /** MySQL database password */
    define('DB_PASSWORD', '12345678');

    /** MySQL hostname */
    define('DB_HOST', '10.10.21.68');
}



/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'rE|hZ1Odw!a*ss +CEIO{Ogj6p-k||cped`wbp|uwpAc`XC2.L>@dL4cbj ( *7r');
define('SECURE_AUTH_KEY',  'mMR+V,!AUMzalM*_F1MPB?I,rAW<|tQ&}Al?|J_OgdBr]/r+w8#$kYr!V_s~wo0*');
define('LOGGED_IN_KEY',    '%6hFBvo_i[H6AgxwOx/H{KLq+a-{</T$ YMr(WMDQDHF>NTuKA?XR0N8-D)[]vz0');
define('NONCE_KEY',        'u}(3=nzz//y-EtHq@_`yW*f$rdc_XugL|XYBH/w*&+A1-!<W^=Zoc-[4-]~Jlp7g');
define('AUTH_SALT',        't=W%C;xgnqO.3|G{y~I2$G0Y$oOo22BE o2 @[F(xwDuD&;zRgDD-t|~$P8&{43/');
define('SECURE_AUTH_SALT', '/1|[_G9O6*d9;2B6`TKX!jgd &lAKw;O:M)kUT]armK>}w#^=+6xf`[|}08t9!E.');
define('LOGGED_IN_SALT',   'JI~(-C39X7B5-.CoTL2x_lI![AzW=-k*kzgxm`3MjVFhm9h{=cQ[P>mvm[D5-Iae');
define('NONCE_SALT',       'R[@nvr8UN7v|[B4PEx&-oY!BDA#;b=#pMa2p+RyonERQ/:AHl||0TApI(sJc3=*?');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
    define('ABSPATH', dirname(__FILE__) . '/');

define('AUTOSAVE_INTERVAL', 300 ); // seconds
define('WP_POST_REVISIONS', false);



/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
