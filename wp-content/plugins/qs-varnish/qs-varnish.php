<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
/**
 * Plugin Name: QS Varnish Integrater
 * Plugin URI:  http://quadshot.com/
 * Description: Allows WP to purge varnish caches upone post updating.
 * Version:     0.2.0
 * Author:      Quadshot Software LLC
 * Author URI:  http://quadshot.com/
 * License: OpenTickets Software License Agreement
 * License URI: http://opentickets.com/opentickets-software-license-agreement
 * Copyright 2013 Quadshot Software, LLC. All Rights Reserved.
 */

class qs_varnish {
	protected static $version = '0.2.0';
	protected static $find = '';
	protected static $settings = array();
	protected static $defs = array(
		'ips' => array(),
		'post_cache' => true,
		'home_cache' => true,
		'debug' => false,
	);

	public static function pre_init() {
		$u = parse_url(site_url());
		self::$find = $u['host'];
    self::$settings = wp_parse_args(get_option('_qsvarn_settings', ''), self::$defs);

		add_action('save_post', array(__CLASS__, 'clear_cache_post'), PHP_INT_MAX, 3);
		add_filter('qsvarn-purge-cache', array(__CLASS__, 'purge_cache'), 10, 2);
		add_filter('qsvarn-quick-cache', array(__CLASS__, 'quick_cache'), 10, 3);
		add_filter('qsvarn-quick-purge', array(__CLASS__, 'quick_purge'), 10, 3);
		add_action('tw_home_page_settings_save', array(__CLASS__, 'settings_page_save'));

    if (is_admin()) {
      add_action('admin_menu', array(__CLASS__, 'setup_menu_pages'), 10);

			add_action('add_meta_boxes', array(__CLASS__, 'add_meta_boxes'), 10);
			
			add_action('load-post.php', array(__CLASS__, 'process_purge_request'), 10);
    }
	}

	public static function clear_cache_post($post_id, $post, $updated=false) {
		if ($post->post_status != 'publish') return;
		apply_filters('qsvarn-purge-cache', false, $post);
	}

	public static function process_purge_request($post) {
		if (!isset($_REQUEST['refresh_cache']) || $_REQUEST['refresh_cache'] != '1' || !is_admin()) return;

		$post = false;

		if (isset($_REQUEST['post'])) {
			$post = get_post($_REQUEST['post']);
		}

		apply_filters('qsvarn-purge-cache', false, $post);
	}

	public static function settings_page_save() {
		apply_filters('qsvarn-purge-cache', false, false);
	}

	public static function purge_cache($status=false, $post=false) {
		$last_purge = array();

		if (is_object($post) && self::$settings['post_cache']) {
			$url = get_permalink($post->ID);
			$resp = array();
			$resp = apply_filters('qsvarn-quick-purge', $resp, $url, 0.1);
			$resp = apply_filters('qsvarn-quick-cache', $resp, $url, 0.1);
			$last_purge = array_merge($last_purge, $resp);
		}

		if (self::$settings['home_cache']) {
			$resp = array();
			$resp = apply_filters('qsvarn-quick-purge', $resp, site_url(), 0.1);
			$resp = apply_filters('qsvarn-quick-cache', $resp, site_url(), 0.1);
			$last_purge = array_merge($last_purge, $resp);
		}

		if (self::$settings['debug'] && is_object($post)) {
			update_post_meta($post->ID, '_last_purge_data', array('date' => date('Y-m-d H:i:s'), 'info' => $last_purge));
		}

		return $last_purge;
	}

	public static function quick_cache($list, $url, $timeout=0.1) {
		$list = is_array($list) ? $list : array();
		foreach (array('gzip', 'deflate', 'none') as $enc) {
			foreach (self::$settings['ips'] as $with) {
				$r = self::_curl(self::_replace_domain($url, $with), array(), 'GET', false, array(
					'Host: '.self::$find,
					'qsssd-iua: 123',
					'Accept-Encoding: '.$enc,
				));
				unset($r['response_body']);
				$list[] = $r;
			}
		}
		return $list;
	}

	public static function quick_purge($list, $url, $timeout=0.1) {
		$list = is_array($list) ? $list : array();
		foreach (array('gzip', 'deflate', 'none') as $enc) {
			foreach (self::$settings['ips'] as $with) {
				$r = self::_curl(self::_replace_domain($url, $with), array(), 'PURGE', false, array(
					'Host: '.self::$find,
					'qsssd-iua: 123',
					'Accept-Encoding: '.$enc,
				));
				unset($r['response_body']);
				$list[] = $r;
			}
		}
		return $list;
	}

	protected function _headers($extra_headers=array()) {
		if (is_object($extra_headers)) $extra_headers = (array)$extra_headers;
		if (!is_array($extra_headers)) throw new Exception('Extra headers are not in the correct format.', 10000, array('extra_headers' => $extra_headers));

		$out = array();
		foreach ($extra_headers as $k => $v) {
			if (is_scalar($v)) {
				if (is_numeric($k)) $out[] = $v;
				else $out[] = $k.': '.$v;
			}
		}

		return $out;
	}

	protected function _curl($url, $fields=array(), $method='GET', $body_only=false, $extra_headers=array(), $extra_opts=array()) {
		if (is_string($extra_headers)) parse_str($extra_headers, $extra_headers);
		//if (is_string($fields)) parse_str($fields, $fields);
		$extra_opts = is_array($extra_opts) ? $extra_opts : array();
		//$method = in_array(($method = strtoupper($method)), array('GET', 'POST')) ? $method : (empty($fields) ? 'GET'  : 'POST');

		$c = curl_init($url);
		$headers = array(
			'Accept' => 'text/html, application/xhtml+xml, */*',
			'Cache-Control' => 'no-cache',
			'Connection' => 'Keep-Alive',
			'Accept-Language' => 'en-us',
		);

		$opts = array(
			CURLOPT_HTTPHEADER => self::_headers(array_merge($headers, $extra_headers)),
			CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.44 Safari/537.36',
			CURLOPT_RETURNTRANSFER => 1,
			CURLINFO_HEADER_OUT => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_TIMEOUT => 0.1,
			CURLOPT_CONNECTTIMEOUT => 0.1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_MAXREDIRS => 18,
			CURLOPT_AUTOREFERER => 1,
			CURLOPT_COOKIEFILE => '',
		);

		if ($method == 'GET') {
			$opts[CURLOPT_HTTPGET] = 1;
		} else if ($method == 'POST') {
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = is_array($fields) || is_object($fields) ? http_build_query($fields) : $fields;
		} else {
			$opts[CURLOPT_CUSTOMREQUEST] = $method;
		}

		foreach ($extra_opts as $k => $v) $opts[$k.''] = $v;

		curl_setopt_array($c, $opts);

		$resp = curl_exec($c);

		if (($errno = curl_errno($c)) && ($errmsg = curl_error($c)) && strpos($errmsg, 'SSL') !== false) {
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
			$resp = curl_exec($c);
		}

		$stats = curl_getinfo($c);
		$stats['_last_errno'] = curl_errno($c);
		$stats['_last_errmsg'] = curl_error($c);
		$stats['request_fields'] = is_array($fields) || is_object($fields) ? http_build_query($fields) : $fields;
		$stats['response_headers'] = substr($resp, 0, $stats['header_size']);
		$stats['response_body'] = substr($resp, $stats['header_size']);

		curl_close($c);
		if ($body_only) return $stats['response_body'];

		return $stats;
	}

	protected static function _replace_domain($url, $with) {
		return preg_replace('#/'.self::$find.'#i', '/'.$with, $url);
	}

	public static function add_meta_boxes() {
		if (current_user_can('delete_users')):
			$screens = array('post');
			foreach ($screens as $screen) {
				add_meta_box(
					'qs-varnish',
					'Caching',
					array(__CLASS__, 'mb_caching'),
					$screen
				);
			}
		endif;
	}

	public static function mb_caching($post) {
		$url = add_query_arg(array('refresh_cache' => 1));
		?>
			<p>
				<a href="<?php echo esc_attr($url) ?>" class="button">Manually Refresh Cache for this Post</a>
			</p>

			<?php if (current_user_can('manage_options')): ?>
				<style>
					#qs-varnish .details { display:none; font-size:10px; max-width:100%; overflow:scroll; width:100%; text-align:left; }
				</style>

				<div class="section">
					<?php
						$details = get_post_meta($post->ID, '_last_purge_data', true);
					?>
					<div class="toggle-details">Toggle Details</div>
					<pre class="details"><?php echo $details ? self::__log($details) : '(nothing to show)'; ?></pre>
				</div>

				<script language="javascript">
					if (typeof jQuery == 'object' || typeof jQuery == 'function') (function($) {
						$(document).on('click', '.toggle-details', function() {
							var cont = $(this).next('.details:eq(0)');
							if (cont.css('display') == 'none') cont.slideDown(200);
							else cont.slideUp(200);
						});
					})(jQuery);
				</script>
			<?php endif; ?>
		<?php
	}

	protected static function __log() {
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
		echo force_balance_tags(htmlspecialchars($output));
		echo defined('PHP_SAPI') && PHP_SAPI == 'cli' ? '' : '</pre>';
	}

  public static function setup_menu_pages() {
    $hook = add_options_page(
      'QS Varnish Integrater Settings',
      'QS Varnish Settings',
      'manage_options',
      'qs-varnish',
      array(__CLASS__, 'ap_settings_page')
    );
    add_action('load-'.$hook, array(__CLASS__, 'head_ap_settings_page_purge'), 9);
    add_action('load-'.$hook, array(__CLASS__, 'head_ap_settings_page'), 10);
  }

	public static function sane_url($url) {
		$url = trim($url);
		$p = @parse_url($url);

		return (isset($p['scheme']) ? $p['scheme'] : 'http').'://'
			.(isset($p['user']) ? $p['user'].(isset($p['pass']) ? ':'.$p['pass'] : '').'@' : '') 
			.(isset($p['host']) ? $p['host'] : self::$find).(isset($p['port']) ? ':'.$p['port'] : '') 
			.(isset($p['path']) ? $p['path'] : '/')
			.(isset($p['query']) ? '?'.$p['query'] : '');
	}

	public static function head_ap_settings_page_purge() {
    if (!is_admin() || !isset($_POST, $_POST['qsvarn-purge-submit'])) return;
    if (!wp_verify_nonce($_POST['qsvarn-purge-submit'], 'qsvarn-purge-now')) return;

		$urls = trim($_POST['qsvarn-purge-urls']);
		if (strlen($urls)) {
			$urls = preg_split('#(\n|\r)+#', $urls);
			$urls = array_filter(array_map(array(__CLASS__, 'sane_url'), $urls));
		} else {
			$urls = array();
		}

		$update = array();
		foreach ($urls as $url) {
			$resp = array();
			$resp = apply_filters('qsvarn-quick-purge', $resp, $url, 0.1);
			$resp = apply_filters('qsvarn-quick-cache', $resp, $url, 0.1);
			$update[$url] = $resp;
		}

		update_option('_qsvarn_last_purge_info', $update);
		update_option('_qsvarn_last_purge', 'Successfully purged: '.implode(', ', array_keys($update)));

    $u = add_query_arg(array('purged' => 1), remove_query_arg(array('updated', 'purged')));
    wp_safe_redirect($u);
    exit;
	}

  public static function head_ap_settings_page() {
    if (!is_admin() || !isset($_POST, $_POST['qsvarn-settings-save'])) return;
    if (!wp_verify_nonce($_POST['qsvarn-settings-save'], 'qsvarn-save-now')) return;

    $cur = self::$settings;

    $r = trim($_POST['qsvarn-ips']);
    if (strlen($r)) {
      $r = preg_split('#(\n|\r)+#', $r);
      $r = array_filter(array_map('trim', $r));
    } else {
      $r = array();
    }

    $options = wp_parse_args(array(
			'ips' => $r,
			'post_cache' => $_POST['qsvarn-post'],
			'home_cache' => $_POST['qsvarn-home'],
			'debug' => $_POST['qsvarn-debug'],
		), $cur);

    update_option('_qsvarn_settings', $options);
		do_action('qsvarn-save-settings', $options, $cur);

    $url = add_query_arg(array('updated' => 1), remove_query_arg(array('updated', 'purged')));
    wp_safe_redirect($url);
    exit;
	}

  public static function ap_settings_page() {
    $settings = self::$settings;
    $ips = is_array($settings['ips']) ? implode("\n", $settings['ips']) : (array)$settings['ips'];
		$do_post = (bool)$settings['post_cache'];
		$do_home = (bool)$settings['home_cache'];
		$debug = (bool)$settings['debug'];

		$pmsg = get_option('_qsvarn_last_purge', '');
		update_option('_qsvarn_last_purge', '');
		?>
      <style>
        .wrap .helper { font-size:10px; color:#888888; }
        .wrap .helper code { font-size:10px; }
        .wrap h4 { margin-bottom:4px; }
        .wrap .field { margin-bottom:1.5em; }
      </style>

			<div class="wrap">
				<h2>QS Varnish Integrater Settings</h2>

				<form action="" method="POST">
          <div class="section">
            <h4>Varnish Server IPs</h4>

            <div class="field">
              <label>IP list</label>
              <textarea name="qsvarn-ips" class="widefat" rows="10"><?php echo $ips; ?></textarea>
              <div class="helper">
                One replacement ip per line. This should be the list of IPs that represent the varnish servers that need purging.
              </div>
            </div>
          </div>

          <div class="section">
            <h4>Additional Settings</h4>

            <div class="field">
              <input type="hidden" name="qsvarn-post" value="0" />
              <input type="checkbox" name="qsvarn-post" value="1" <?php checked(true, (bool)$do_post) ?> />
              <label> Blow out post cache?</label>
              <div class="helper">
								Checking this means that, upon saving a post (updating, new, etc...) the cache that may have been built for that post, gets blown out.
              </div>
            </div>

            <div class="field">
              <input type="hidden" name="qsvarn-home" value="0" />
              <input type="checkbox" name="qsvarn-home" value="1" <?php checked(true, (bool)$do_home) ?> />
              <label> Blow out homepage cache?</label>
              <div class="helper">
								Checking this means that, upon saving a post (updating, new, etc...) the cache that may have been built for THE HOMEPAGE, gets blown out.
              </div>
            </div>

            <div class="field">
              <input type="hidden" name="qsvarn-debug" value="0" />
              <input type="checkbox" name="qsvarn-debug" value="1" <?php checked(true, (bool)$debug) ?> />
              <label> Enable Debug?</label>
              <div class="helper">
								Enabling this will allow for the plugin to track the results of the purge request, and store them so that an admin can see them.
              </div>
            </div>
          </div>

          <div class="field">
            <?php wp_nonce_field('qsvarn-save-now', 'qsvarn-settings-save') ?>
            <input type="submit" class="button-primary" value="Save Settings"/>
          </div>					
				</form>

				<h2>Purge</h2>

				<?php if ($pmsg): ?>
					<style>.fake-p { margin:0.5em 2px; } .toggle-next { cursor:pointer; }</style>
					<div class="updated pmsg">
						<p>
							<strong>Purge Success</strong><br/>
							<?php echo $pmsg ?>
						</p>
						<div class="fake-p">
							<div class="toggle-next"> toggle</div>
							<div class="hide-if-js">
								<?php echo self::__log(get_option('_qsvarn_last_purge_info', array())) ?>
							</div>
						</div>
						<script language="javascript">
							(function($) {
								$('.pmsg .toggle-next').on('click', function(e) {
									e.preventDefault();
									var subject = $(this).next();
									subject[subject.css('display') == 'none' ? 'slideDown' : 'slideUp'](250);
								});
							})(jQuery);
						</script>
					</div>
				<?php endif; ?>

				<form action="" method="POST">
          <div class="section">
            <h4>URLs to Purge</h4>

            <div class="field">
              <label>URLs</label>
              <textarea name="qsvarn-purge-urls" class="widefat" rows="10"></textarea>
              <div class="helper">
                One replacement url per line. Provide either the full url, or just the path portion of the url.
              </div>
            </div>
          </div>

          <div class="field">
            <?php wp_nonce_field('qsvarn-purge-now', 'qsvarn-purge-submit') ?>
            <input type="submit" class="button-primary" value="Purge URLs"/>
          </div>					
				</form>
			</div>
		<?php
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qs_varnish::pre_init();
}
