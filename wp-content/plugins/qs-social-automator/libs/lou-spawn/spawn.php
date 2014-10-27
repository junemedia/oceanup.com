<?php

if ( !empty($_POST) || defined('DOING_AJAX') || defined('DOING_CRON') )
	die();

define('DS', DIRECTORY_SEPARATOR);
define('LOUSPAWNV', '0.1.1');
define('PATH_TO_ABSPATH', '..'.DS.'..'.DS.'..'.DS.'..'.DS.'..'.DS);

function _spawn_out($data) {
	global $___stime;
	if (!empty($___stime)) $data['tt'] = microtime(true) - $___stime;
	$var = uniqid('r');
	header('Content-Type: text/javascript');
	echo $var.'='.@json_encode($data).';';
	exit;
}

$___stime = isset($_GET, $_GET['time']) ? microtime(true) : false;

define('WP_CACHE', false);
define('SHORTINIT', true);

require_once PATH_TO_ABSPATH.'wp-load.php';
require_once PATH_TO_ABSPATH.'wp-includes/link-template.php';
require_once PATH_TO_ABSPATH.'wp-includes/general-template.php';
require_once PATH_TO_ABSPATH.'wp-includes/http.php';
require_once PATH_TO_ABSPATH.'wp-includes/class-http.php';

if (!function_exists('_lou_db_check')) { function _lou_db_check() {
	require_once ABSPATH.WPINC.'/formatting.php';
	$dbv = get_option('_lou_dbv', 0);
	if (version_compare($dbv, LOUSPAWNV) >= 0) return;

	global $wpdb;
	$wpdb->query('drop table if exists '.$wpdb->prefix.'qssa_spawn_cron');
	$wpdb->query('create table '.$wpdb->prefix.'qssa_spawn_cron ('
			.'`id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,'
			.'`ip` bigint(20) unsigned DEFAULT NULL,'
			.'`ts` bigint(20) unsigned DEFAULT NULL,'
			.'PRIMARY KEY (`id`),'
			.'UNIQUE KEY `ip` (`ip`)'
		.') ENGINE=MyISAM AUTO_INCREMENT=265502 DEFAULT CHARSET=utf8');
	update_option('_lou_dbv', LOUSPAWNV);
}}
_lou_db_check();

$out = array();

$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';

$row = $wpdb->get_row($wpdb->prepare('select id, ts from '.$wpdb->prefix.'qssa_spawn_cron where ip = inet_aton(%s)', $ip));

$ts = is_object($row) && isset($row->ts) ? $row->ts : 0;
$id = is_object($row) && isset($row->id) ? $row->id : 0;

$check = $ts + 120;
$time = time();

if (true || $check < $time) {
	if ($id) $wpdb->query($wpdb->prepare('update '.$wpdb->prefix.'qssa_spawn_cron set ts = %d where id = %d', $time, $id));
	else $wpdb->query($wpdb->prepare('insert into '.$wpdb->prefix.'qssa_spawn_cron (ip, ts) values (inet_aton(%s), %d)', $ip, $time));
	
	require_once ABSPATH.WPINC.DS.'cron.php';
	spawn_cron();

	$out['m'] = 'Spawned.';
} else {
	$out['e'] = 'Too many requests.';
	$out['code'] = $time - $check;
}

_spawn_out($out);
