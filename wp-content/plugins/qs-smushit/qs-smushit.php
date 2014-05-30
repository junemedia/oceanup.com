<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
/**
 * Plugin Name: QS Smushit
 * Plugin URI:  http://quadshot.com/
 * Description: Uses the Yahoo! Smushit utility to losslessly compress images, in high-performance, multi-server environments.
 * Version:     0.1.0
 * Author:      Quadshot Software LLC
 * Author URI:  http://quadshot.com/
 * License: OpenTickets Software License Agreement
 * License URI: http://opentickets.com/opentickets-software-license-agreement
 * Copyright 2013 Quadshot Software, LLC. All Rights Reserved.
 */

if (!defined('QS_SMUSHIT_IP')) define('QS_SMUSHIT_IP', '');
if (!defined('QS_SMUSHIT_DOMAIN')) define('QS_SMUSHIT_DOMAIN', '');

// post to: http://www.smushit.com/ysmush.it/ws.php
// post params: img=<list-of-images>
// smushit sample response
// {"src":"http:\/\/cdn01.thewrap.com\/images\/2014\/04\/Tommy_Lee_Jones_Ryan_Gosling-618x400.png","src_size":578294,"dest":"http:\/\/ysmushit.zenfs.com\/results\/4be5e60b%2Fsmush%2Fimages%2F2014%2F04%2FTommy_Lee_Jones_Ryan_Gosling-618x400.png","dest_size":369910,"percent":"36.03","id":""}
class qs_smushit {
	protected static $version = '0.1.0';
	protected static $plugin_path = '';
	protected static $plugin_url = '';

	protected static $uPath = false;
	protected static $uUrl = false;

	protected static $def_smushers = array();
	protected static $smusher = false;

	public static function pre_init() {
		self::_get_uploads_paths();
		self::_get_plugin_paths();

		self::$def_smushers = array(
			'QS_CLI_Smusher' => self::$plugin_path.'smushers'.DIRECTORY_SEPARATOR.'cli.smusher.php',
			'QS_Imagick_Smusher' => self::$plugin_path.'smushers'.DIRECTORY_SEPARATOR.'imagick.smusher.php',
			'QS_Smushit_Smusher' => self::$plugin_path.'smushers'.DIRECTORY_SEPARATOR.'smushit.smusher.php',
		);

		add_action('plugins_loaded', array(__CLASS__, 'plugins_loaded'), PHP_INT_MAX);
	}

	public static function plugins_loaded() {
		add_filter('wp_image_editors', array(__CLASS__, 'presmushers'), PHP_INT_MAX);

		$smushers = apply_filters('qs-smushers-list', self::$def_smushers);

		foreach ($smushers as $class => $file) {
			require_once $file;
			if (!class_exists($class)) continue;
			$available = call_user_func(array($class, 'available'));
			if ($available) {
				self::$smusher = new $class(array(
					'overwrite' => true,
					'segment' => '',
				));
				break;
			}
		}

		if (is_object(self::$smusher)) {
			add_filter('qs-smush-image-files', array(__CLASS__, 'smush'), 10, 3);
			add_filter('qs-smush-get-errors', array(__CLASS__, 'get_smusher_errors'), 10, 1);
			// handled by pre-smusher now
			//add_action('wp_generate_attachment_metadata', array(__CLASS__, 'attempt_smushing'), 10, 2);
		}
	}

	public static function smush($result, $srcs, $args='') {
		self::$smusher->reset();
		return self::$smusher->smush($srcs, $args);
	}

	public static function get_smusher_errors($e) {
		return self::$smusher->get_errors();
	}

	public static function attempt_smushing($meta, $attachment_id) {
		$attachment = get_post($attachment_id);
		$file = self::$plugin_path.$meta['file'];

		if (preg_match('!^image/!', get_post_mime_type($attachment)) && file_is_displayable_image($file) && isset($meta['sizes']) && is_array($meta['sizes']) && count($meta['sizes'])) {
			$base_path = dirname($file).DIRECTORY_SEPARATOR;
			$list = $lookup = array();

			foreach ($meta['sizes'] as $slug => $size) {
				$lookup[$base_path.$size['file']] = $slug;
				$list[] = $base_path.$size['file'];
			}

			$res = apply_filters('qs-smush-image-files', array(), $list);

			if (is_array($res) && count($res)) foreach ($res as $res_item) {
				$slug = isset($lookup[$res_item['src']]) ? $lookup[$res_item['src']] : false;
				if (empty($slug)) continue;
				$meta['sizes'][$slug]['qssm'] = sprintf(
					'v3:%d:%d',
					$res_item['src_size'],
					$res_item['dest_size']
				);
			}
		}

		return $meta;
	}

	public static function presmushers($list) {
		return apply_filters('qs-smush-presmushers', $list);
	}

	protected static function _get_uploads_paths() {
		$u = wp_upload_dir();
		self::$uPath = $u['basedir'];
	}

	protected static function _get_plugin_paths() {
		self::$plugin_path = plugin_dir_path(__FILE__);
		self::$plugin_url = plugin_dir_url(__FILE__);
	}

	public static function require_at_least_one_smusher() {
		$smushers = apply_filters('qs-smushers-list', self::$def_smushers);
		?>
			<div class="error">
				<p>
					<strong class="qs-smushit">QS Smushit</strong> requires that you meet at least one set of requirements below:<br/>
					<?php foreach ($smushers as $class => $file): if (!class_exists($class)) continue; ?>
						<?php
							$requires = trim(call_user_func(array($class, 'requires')));
							$method = trim(call_user_func(array($class, 'name')));
						?>
						<?php if ($requires): ?>
							The <?php echo $name ?> method requires that <?php $requires ?>.<br/>
						<?php endif; ?>
					<?php endforeach; ?>
				</p>
			</div>
		<?php
	}

	public static function require_at_least_one_smusher_cli() {
		echo "QS Smushit requires that cURL be installed. This library must be installed before you can use this tool.\n";
		foreach ($smushers as $class => $file):
			$requires = trim(call_user_func(array($class, 'requires')));
			$method = trim(call_user_func(array($class, 'name')));
			if ($requires):
				echo "The $name method requires that $requires.\n";
			endif;
		endforeach;
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qs_smushit::pre_init();
}
