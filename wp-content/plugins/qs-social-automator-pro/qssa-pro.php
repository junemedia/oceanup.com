<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; /* prevent direct access */
/**
 * Plugin Name: Quadshot Social Automator Pro
 * Plugin URI:  http://quadshot.com/
 * Description: Upgrade to Quadshot Social Automator Lite, which includes more social networks
 * Version:     0.6.0
 * Author:      Quadshot Software LLC
 * Author URI:  http://quadshot.com/
 * License: OpenTickets Software License Agreement
 * License URI: http://opentickets.com/opentickets-software-license-agreement
 * Copyright 2013 Quadshot Software, LLC. All Rights Reserved.
 */

class qs_social_automator_pro {
	protected static $version = '0.6.0';
	protected static $plugin_path = '';
	protected static $plugin_url = '';

	public static function pre_init() {
		self::$plugin_path = plugin_dir_path(__FILE__);
		self::$plugin_url = plugin_dir_url(__FILE__);
		$passed = version_compare(phpversion(), '5.3.0') >= 0;

		// allow read access to plugin path info
		add_filter('qs-sa-pro/plugin/path', array(__CLASS__, 'get_plugin_path'), 10, 2);

		if ($passed) {
			require_once 'inc'.DIRECTORY_SEPARATOR.'helpers.php';
			if (self::_check_deps()) {
				add_filter('qs-sa/modules/paths/extra', array(__CLASS__, 'plugin_modules_dir'), 10, 1);
				add_filter('qs-sa/includes/paths/extra', array(__CLASS__, 'plugin_includes_dir'), 10, 1);
			} else {
				add_action('admin_notices', array(__CLASS__, 'missing_dependency'), 1, 0);
			}
		} else {
			add_action('admin_notices', array(__CLASS__, 'php_version_error'), 0, 0);
		}

		add_action('admin_init', array(__CLASS__, 'register_admin_assets'), 101, 0);
		add_action('admin_enqueue_scripts', array(__CLASS__, 'load_admin_assets'), 101, 1);
	}

	public static function register_admin_assets() {
		wp_register_style('qssa-pro-admin-primary', self::$plugin_url.'assets/css/admin/primary.css', array(), self::$version);
		wp_register_script('qssa-pro-admin-settings', self::$plugin_url.'assets/js/admin/settings.js', array('qs-sa-admin-settings'), self::$version);
	}

	public static function load_admin_assets($hook) {
		wp_enqueue_style('qssa-pro-admin-primary');

		$settings_page = apply_filters('qs-sa/url/admin/page', '', false, false);
		if (is_array($settings)) switch ($hook) {
			case $settings_page['hook']:
				wp_enqueue_script('qssa-pro-admin-settings');
				wp_localize_script('qssa-pro-admin-settings', '_qssa_pro_admin_settings', array(
					'nonce' => wp_create_nonce('qs-sa-pro/admin-ajax'),
				));
			break;
		}
	}

	// allow externals to get the path of the plugin. mainly used for modules in this plugin
	public static function get_plugin_path($current, $as_url=true) {
		return $as_url ? self::$plugin_url : self::$plugin_path;
	}

	public static function plugin_modules_dir($dirs) {
		$dirs[] = trailingslashit(self::$plugin_path.'modules');
		return array_unique($dirs);
	}

	public static function plugin_includes_dir($dirs) {
		array_unshift($dirs, trailingslashit(self::$plugin_path.'inc'));
		return array_unique($dirs);
	}

	public static function php_version_error() {
		?>
			<div class="error">
				<p>
					Some <span class="qssa-product-name">Quadshot Social Automator Pro</span> features require PHP 5.3.0 or higher.
					Currently you have PHP <?php echo phpversion() ?>.
					You must upgrade your version of PHP before you can use the features of the Pro plugin.
				</p>
			</div>
		<?php
	}

	public static function missing_dependency() {
		?>
			<div class="error">
				<p>
					The <span class="qssa-product-name">Quadshot Social Automator Pro</span> plugin requires that you have
						<span class="qssa-product-name">Quadshot Social Automator Lite</span> installed and active first.
					None of the features from this Pro plugin will be active until you first activate Lite.
				</p>
			</div>
		<?php
	}

	protected static function _check_deps() {
		$active = get_option('active_plugins');
		$pos = array_search('qs-social-automator'.DIRECTORY_SEPARATOR.'qs-social-automator.php', $active);
		return $pos !== false;
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qs_social_automator_pro::pre_init();
}
