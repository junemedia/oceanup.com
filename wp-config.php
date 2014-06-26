<?php
/*
if (!function_exists('getallheaders')) {
	function getallheaders() { $headers = ''; foreach ($_SERVER as $name => $value) if (substr($name, 0, 5) == 'HTTP_') $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value; return $headers; }
}

function qsssd_maybe_ua() { $h = getallheaders(); if (isset($h['qsssd-iua']) && $h['qsssd-iua'] == '123') ignore_user_abort(1); }
qsssd_maybe_ua();
*/

/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'oceanup_dev');
// wpadmin user ouadmin, asdftyghbn
/** MySQL database username */
define('DB_USER', 'oceanup_dev');

/** MySQL database password */
define('DB_PASSWORD', '4VCOFhDK4S');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         'put your unique phrase here');
define('SECURE_AUTH_KEY',  'put your unique phrase here');
define('LOGGED_IN_KEY',    'put your unique phrase here');
define('NONCE_KEY',        'put your unique phrase here');
define('AUTH_SALT',        'put your unique phrase here');
define('SECURE_AUTH_SALT', 'put your unique phrase here');
define('LOGGED_IN_SALT',   'put your unique phrase here');
define('NONCE_SALT',       'put your unique phrase here');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

function __log() {
  $a = func_get_args();
  echo defined('PHP_SAPI') && PHP_SAPI == 'cli' ? "-----\n" : '<pre style="text-align:left !important; max-width:100%; width:100%;">';
  $d = debug_backtrace();
  $l = false;
  while (count($d) && empty($l)) {
    $l = array_shift($d);
    if (is_string($l['function']) && in_array($l['function'], array('call_user_func_array', 'call_user_func'))) $l = false;
  }
  if ($l['function'] == '__log') $l['function'] = $d[0]['function'];
  echo sprintf("FROM: %s @ line [%s] -> %s()\n", $l['file'], $l['line'], $l['function']);
	ob_start();
  if (count($a)) foreach ($a as $b) { if (is_object($b) || is_array($b)) print_r($b); else var_dump($b); }
	$output = ob_get_contents();
	ob_end_clean();
	echo htmlspecialchars($output);
  echo defined('PHP_SAPI') && PHP_SAPI == 'cli' ? '' : '</pre>';
}
/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');

