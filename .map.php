<?php (defined('PHP_SAPI') && PHP_SAPI == 'cli' ? null : die(header('Location: /')));

$mapfilename = '.out';
if (!is_writable($mapfilename.'.csv')) die("could not write to file".PHP_EOL.PHP_EOL);

define('WP_CACHE', false);
define('WP_MEMORY_LIMIT', '512M');
define('WP_MAX_MEMORY_LIMIT', '512M');

require_once 'wp-load.php';

function _memory_check($flush_percent_range=80) {
	global $wpdb;
	static $max = false;
	$dec = $flush_percent_range / 100;

	if ($max === false) {
		$raw = ini_get('memory_limit');
		preg_match_all('#^(\d+)(\s*)?$#', $raw, $matches, PREG_SET_ORDER);
		if (isset($matches[0])) {
			$max = $matches[0][1];
			$unit = $matches[0][2];
			switch (strtolower($unit)) {
				case 'k': $max *= 1024; break;
				case 'm': $max *= 1048576; break;
				case 'g': $max *= 1073741824; break;
			}
		} else {
			$max = 32 * 1048576;
		}
	}

	$usage = memory_get_usage();
	if ($usage > $max * $dec) {
		wp_cache_flush();
		$wpdb->queries = array();
	}
}

function _open_out_file() {
	global $f, $mapfilename;
	static $next = 0;

	$filename = $mapfilename.(empty($next) ? '' : '.'.$next).'.csv';
	$next++;

	if (is_resource($f)) fclose($f);

	$f = fopen($filename, 'w+');
}


_open_out_file();
$maxfilesize = 1024*1024;
$written = 0;
$item = 1;
$offset = 0;
$limit = 100;

$q = $wpdb->prepare('select * from wp_qsou_legacy_urls limit %d offset %d', $limit, $offset);
$res = $wpdb->get_results($q);

while (is_array($res) && count($res)):
	foreach ($res as $row):
		$oldurl = site_url('/'.$row->olduri);
		$newurl = '';

		switch ($row->urltype) {
			case 'post_tag':
				$term = get_term($row->object_id, 'post_tag');
				$newurl = get_term_link($term);
			break;

			case 'post':
				$newurl = get_permalink($row->object_id);
			break;
		}
		if (is_wp_error($newurl) || empty($newurl)) continue; 

		$package = sprintf('%s,%s', $oldurl, $newurl).PHP_EOL;
		$size = strlen($package);
		if ($written + $size > $maxfilesize) {
			$written = 0;
			_open_out_file();
		}

		fwrite($f, $package, $size);
		$written += $size;
		$item++;

		if ($item % 1000 == 0) echo ($item/1000).PHP_EOL;
		elseif ($item % 100 == 0) echo 'H';
		else echo '.';

		_memory_check();
	endforeach;

	$offset += $limit;
	$q = $wpdb->prepare('select * from wp_qsou_legacy_urls limit %d offset %d', $limit, $offset);
	$res = $wpdb->get_results($q);
endwhile;

fclose($f);
echo "done writing file [$mapfilename]".PHP_EOL.PHP_EOL;
