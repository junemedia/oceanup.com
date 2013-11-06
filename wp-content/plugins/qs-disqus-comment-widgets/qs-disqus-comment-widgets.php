<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;
/**
 * Plugin Name: QS Disqus Comment Widgets
 * Plugin URI:  http://quadshot.com/
 * Description: Commonly used Disqus widget, that are tuned for high-volume websites, which do not rely upon the Disqus JS API.
 * Version:     1.0.0
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

class QS_Disqus_Comment_Widgets {
	public static $version = '1.0.0';
	public static $plugin_dir = '';
	public static $plugin_url = '';
	protected static $registered_widgets = array();

	// register actions needed for this plugin, and setup the plugin path info
	public static function pre_init() {
		self::$plugin_dir = plugin_dir_path(__FILE__);
		self::$plugin_url = plugin_dir_url(__FILE__);

		self::load_includes('core', '#^.+\.class\.php$#i');

		add_action('qsda-register-widget', array(__CLASS__, 'add_registered_widget'), 10);
		add_action('plugins_loaded', array(__CLASS__, 'load_widgets'), 1);
		add_action('init', array(__CLASS__, 'register_assets'), 11);
		add_action('wp_enqueue_scripts', array(__CLASS__, 'load_assets'), 11);
	}

	// allow widgets to register themselves so that we know they exist, when trying to determine if the default styling needs to be loaded
	public static function add_registered_widget($class) {
		self::$registered_widgets[] = strtolower($class);
		self::$registered_widgets = array_unique(self::$registered_widgets);
	}

	// load the widgets that come with the plugin
	public static function load_widgets() {
		self::load_includes('widgets');
	}

	// register the css needed to basic style the widgets
	public static function register_assets() {
		wp_register_style('qsda-widgets', self::$plugin_url.'css/disqus-widgets.css', array(), self::$version);
	}

	// determine if we need to load the cs we registered, based on whether we have one of our widgets in an active sidebar or not
	public static function load_assets() {
		$need = false;
		$sbw = get_option('sidebars_widgets', array());

		if (empty(self::$registered_widgets)) return; // if no widgets are registered, then it is impossible to have one on a sidebar. no css needed

		// regex to search each widget for, to determine if it is a registered widget for this plugin or not
		$regex = '#^('.implode('|', array_map(array(__CLASS__, 'preg_quote'), self::$registered_widgets)).')($|-)#';

		// search the sidebars for any of our widgets
		foreach ($sbw as $slug => $widgets) {
			if (is_array($widgets) && $slug != 'wp_inactive_widgets') foreach ($widgets as $widget) if (preg_match($regex, $widget)) {
				$need = true;
				break 2;
			}
		}

		// if found, we need the base css
		if ($need) {
			wp_enqueue_style('qsda-widgets');
		}
	}

	// quote each widget name with our preg syntax
	public static function preg_quote($val) {
		return preg_quote($val, '#');
	}

  public static function load_includes($group='', $regex='#^.+\.widget\.php$#i') {
    // aggregate a list of includes dirs that will contain files that we need to load
    $dirs = apply_filters('qsdcw-load-includes-dirs', array(self::$plugin_dir));
    // cycle through the top-level include folder list
    foreach ($dirs as $dir) {
      // does the subdir $group exist below this context?
      if (file_exists($dir) && ($sdir = trailingslashit($dir).$group) && file_exists($sdir)) {
        // if the subdir exists, then recursively generate a list of all *.class.php files below the given subdir
        $iter = new RegexIterator(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
              $sdir
            ),  
            RecursiveIteratorIterator::SELF_FIRST
          ),  
          $regex,
          RecursiveRegexIterator::GET_MATCH
        );  

        // require every file found
        foreach ($iter as $fullpath => $arr) {
          require_once $fullpath;
        }   
      }   
    }   
  }
}

if (defined('ABSPATH') && function_exists('add_action')) {
	QS_Disqus_Comment_Widgets::pre_init();
}
