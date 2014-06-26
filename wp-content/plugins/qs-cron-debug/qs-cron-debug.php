<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;
/**
 * Plugin Name: QS Cron Debugger Tool
 * Plugin URI:  http://quadshot.com/
 * Description: A simple plugin that allows special processing or wp cron tasks
 * Version:     1.0
 * Author:      Quadshot
 * Author URI:  http://quadshot.com/
 * License:     Apache License, v2
 *
Copyright 2013 Quadshot Software, LLC. Authored by Chris Webb (Loushou).

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
 */

class QS_Cron_Debug {
	public static function pre_init() {
		if (is_admin()) {
			add_action('admin_init', array(__CLASS__, 'wp_cron_debug'), 0);
		}
	}

	public static function wp_cron_debug() {
		if (!isset($_GET['wp_cron_debug']) || $_GET['wp_cron_debug'] != 9999) return;
		header('Content-Type: text/html');

		?><html><head><title>WP Cron Debug</title><?php self::_debug_style() ?></head><body><?php

		if (isset($_GET['remove'])) self::_debug_remove();
		if (isset($_GET['run'])) self::_debug_run();

		$cron = get_option('cron');

		?><h3>Found [<?php echo count($cron) ?>] Cron Items</h3><div class="list-wrap"><ul><li><?php echo implode(',', array_keys($cron)) ?></li><?php
			foreach ($cron as $ts => $hooks):
				if (!is_array($hooks) || !is_numeric($ts)) continue;
				foreach ($hooks as $hook => $settings):
					foreach ($settings as $key => $funcargs):
						$a = array();
						foreach ($funcargs['args'] as $k => $v) $a[] = '$'.$k.' = "'.$v.'"';
						$a = implode(', ', $a);
						$sch = implode('', (array)$funcargs['schedule']);
						$int = isset($funcargs['interval']) ? implode(',', (array)$funcargs['interval']) : '';
						?><li>
							<strong><?php echo date('Y-m-d H:i:s', $ts) ?> (<?php echo $ts ?>)</strong>
							-
							<code><?php echo $key ?></code>
							-
							<em>do_action('<?php echo $hook ?>"<?php echo !empty($a) ? ', '.$a : '' ?>);
								<?php if (!empty($sch)): ?> @ [<?php echo $sch ?>] <?php endif; ?>
								<?php if (!empty($int)): ?> EVERY [<?php echo $int ?>] <?php endif; ?>
							</em>
						</li><?php
					endforeach;
				endforeach;
			endforeach;
		?></ul></div><?php

		?></body></html><?php
		exit;
	}

	protected static function _debug_run() {
		$times = array_map('absint', array_filter(explode(',', $_GET['run'])));
		if (empty($times)) return;

		$time = max($times);
		echo '<div class="msg">Attempting to run timestamp ['.$time.']</div>';

		self::_debug_run_as($time);
	}

	protected static function _debug_run_as($gmt_time) {
		remove_action( 'scheduled_subscription_payment', 'WC_Subscriptions_Manager::safeguard_scheduled_payments', 0 );

		$crons = get_option('cron');

		foreach ( $crons as $timestamp => $cronhooks ) {
			if ( $timestamp != $gmt_time )
				continue;
			echo '<div class="msg">Running ['.$timestamp.']</div>';

			foreach ( $cronhooks as $hook => $keys ) {

				foreach ( $keys as $k => $v ) {

					$schedule = $v['schedule'];

					if ( $schedule != false ) {
						$new_args = array($timestamp, $schedule, $hook, $v['args']);
						call_user_func_array('wp_reschedule_event', $new_args);
					}

					__log($GLOBALS['wp_filter'][$hook]);

					wp_unschedule_event( $timestamp, $hook, $v['args'] );

					do_action_ref_array( $hook, $v['args'] );
				}
			}
		}
	}

	protected static function _debug_remove() {
		$times = array_filter(explode(',', $_GET['remove']));
		if (empty($times)) return;

		$crons = get_option('cron');
		foreach ($times as $time) unset($crons[$time]);
		update_option('cron', $crons);
	}

	protected static function _debug_style() {
		?><style>
			body { margin:0; font-family:Helvetica, Arial, sans-serif; font-size:11px; }
			h3 { margin:0 0 5px; padding:0; border:0; }
			code { background-color:#cccccc; }
			.list-wrap ul,
			.list-wrap ul li { padding:0; margin:0; border:0; list-style:none outside none; }
			.list-wrap ul li { padding:3px 0; border-top:1px dotted #dddddd; }
			.list-wrap ul li:first-child { border-top:0; }
		</style><?php
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	QS_Cron_Debug::pre_init();
}
