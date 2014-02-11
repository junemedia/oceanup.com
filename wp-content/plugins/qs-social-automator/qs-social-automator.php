<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
/**
 * Plugin Name: Quadshot Social Automator Lite
 * Plugin URI:  http://quadshot.com/
 * Description: Easy to use, reliable, social network autoposting tool.
 * Version:     1.1.1
 * Author:      Quadshot Software LLC
 * Author URI:  http://quadshot.com/
 * License: OpenTickets Software License Agreement
 * License URI: http://opentickets.com/opentickets-software-license-agreement
 * Copyright 2013 Quadshot Software, LLC. All Rights Reserved.
 */

if (!class_exists('qs_social_automator')):
// loader class w/ admin pages
class qs_social_automator {
	protected static $version = '1.1.1'; // plugin version
	protected static $plugin_url = ''; // holder for plugin url, for assets
	protected static $plugin_path = ''; // holder for plugin path, for includes
	protected static $def_ret_type = 'application/json'; // default ajax return type

	protected static $admin_pages = array( // slugs and hooks for admin pages
		'settings' => array('slug' => 'qs-sa-settings', 'hook' => ''), // the primary settings page
	);

	protected static $settings_slug = '_qs_social_automator_settings'; // wp_options table - settings key
	protected static $settings = array(); // holder for settings
	protected static $defaults = array( // default settings
		'accounts' => array(),
		'post_types' => array(),
	);
	protected static $default_post_overrides = array(
		'enabled' => 1,
	);

	protected static $requires_scheduling = array();
	protected static $available = array();
	protected static $accounts = array();

	// general plugin initialization. setup required files and internal data. prep for plugins_loaded
	public static function pre_init() {
		// set the plugin paths for includes and assets
		self::$plugin_url = plugin_dir_url(__FILE__);
		self::$plugin_path = plugin_dir_path(__FILE__);

		// allow read access to plugin path info
		add_filter('qs-sa/plugin/path', array(__CLASS__, 'get_plugin_path'), 10, 2);

		// allow read addess to plugin settings
		add_filter('qs-sa/plugin/settings', array(__CLASS__, 'get_plugin_settings'), 10, 2);

		// remainder of init should happen after all plugins are loaded, because that will give time for other plugins to add hooks for ours
		add_action('plugins_loaded', array(__CLASS__, 'after_plugins'), 1000);

		// last thing to run after all plugins have done their init (in theory)
		add_action('plugins_loaded', array(__CLASS__, 'after_all_plugins'), PHP_INT_MAX);
	}

	// after other plugins have had time to add their hooks for our plugin, continue the init
	public static function after_plugins() {
		// load the settings, for later use
		self::_load_settings();

		if (is_admin()) {
			// setup the admin page
			add_action('admin_menu', array(__CLASS__, 'create_admin_pages'), 11);

			// proper hook to register scripts and styles for the admin. allows use elsewhere by first registering them
			add_action('admin_init', array(__CLASS__, 'register_admin_assets'), 100);

			// proper hook to load scripts and styles for the admin
			add_action('admin_enqueue_scripts', array(__CLASS__, 'load_admin_assets'), 100);

			// hook to handle admin ajax
			add_action('wp_ajax_qs-sa/ajax/admin', array(__CLASS__, 'process_admin_ajax'), 10);

			// ajax actions
			add_filter('qs-sa/ajax/admin/sa=new-acct', array(__CLASS__, 'aj_new_acct'), 10, 3);
			add_filter('qs-sa/ajax/admin/sa=save-acct', array(__CLASS__, 'aj_save_acct'), 10, 3);
			add_filter('qs-sa/ajax/admin/sa=del-acct', array(__CLASS__, 'aj_delete_acct'), 10, 3);
			add_filter('qs-sa/ajax/admin/sa=update-close-state', array(__CLASS__, 'aj_close_state'), 10, 3);

			// actual actions to handle the autopost scheduling
			add_action('transition_post_status', array(__CLASS__, 'maybe_schedule_required'), 1000, 3);
			add_action('save_post', array(__CLASS__, 'maybe_schedule_autoposting'), PHP_INT_MAX, 2);

			// setup metaboxes
			add_action('add_meta_boxes', array(__CLASS__, 'setup_meta_boxes'), 9);
			add_action('save_post', array(__CLASS__, 'save_post_meta_bax_data'), 10, 2);
		}

		// allow access to fetch the settings page url
		add_filter('qs-sa/url/admin/page', array(__CLASS__, 'admin_page_url'), 10, 3);
		add_filter('qs-sa/url/reverify', array(__CLASS__, 'reverify_page_url'), 10, 3);

		// cache buster to spawn crons
		add_action('init', array(__CLASS__, 'cache_buster'), 1);
		add_filter('cron_request', array(__CLASS__, 'cache_buster_cron_url'), 1, 1);

		// actual autoposting actions
		add_action('qs-sa/autopost/schedule', array(__CLASS__, 'schedule_autopost'), 10, 4);
		add_action('qs-sa/autopost/run', array(__CLASS__, 'run_autopost'), 1, 1);

		// load core includes and available modules
		self::_load_includes();
		self::_load_modules();

		// notify other plugins that we are loaded
		do_action('qs-sa/plugin/loaded');
	}

	// runs at the very end of the plugins_loaded action, in an attempt to allow all other plugins the maximum amount of time to register their own hooks and such
	public static function after_all_plugins() {
		// get a list of the registered 
		self::$available = apply_filters('qs-sa/modules/available', array());
		self::$available = is_array(self::$available) ? self::$available : array();

		// load all the accounts, so they can register their hooks and what not
		self::_load_accounts();

		add_action('admin_init', array(__CLASS__, 'maybe_reverify'), 1);
		add_action('admin_init', array(__CLASS__, 'maybe_repost'), 1);

		// notify other plugins that we are loaded
		do_action('qs-sa/plugin/loaded/final');
	}

	public static function maybe_reverify() {
		if (!isset($_GET, $_GET['rv'])) return;
		$acct = self::_get_acct_by_id($_GET['rv']);
		if (!is_object($acct)) return;

		$acct->reverify();

		$url = add_query_arg(array('page' => self::$admin_pages['settings']['slug']), admin_url('options-general.php'));
		wp_safe_redirect($url);
		exit;
	}

	public static function maybe_repost() {
		if (!isset($_GET, $_GET['rp'], $_GET['pi'])) return;
		$acct = self::_get_acct_by_id($_GET['rp']);
		$post = get_post($_GET['pi']);
		if (!is_object($acct) || !is_object($post)) return;

		try {
			$fb_post_id = $acct->autopost($post);
		} catch(Exception $e) {
			do_action('qs-sa/autopost/log', 'log', $post->ID,
				'Failed autoposting post ['.$args['post_id'].':'.$post->post_title.'] to account ['.$args['name'].'].', 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID,
				'While trying to process the autoposing of post ['.$post->ID.'] on account ['.$acct->name().'], and Exception was thrown. Here are the details: '
						.$e->getMessage().' in ['.$e->getFile().'] @ '.$e->getLine(), 'bad');
		}

		$url = remove_query_arg(array('rp', 'pi'));
		wp_safe_redirect($url);
		exit;
	}

	// caching buster, two part
	// we have a client that uses 'varnish'. this hack plus a minor config changin varnish, makes sure tht crons run.
	//
	// the problem:
	//   wp-cron requires that a pageview occur. most cache solutions prevent this pageview from happening when they serve a cached page. when there is no page view, wp-cron 
	//   does not have the opportunity to spawn a cron process. even when it does, most cache utilities even cache the wp-cron.php page, so it never runs the cron anyways.
	//
	// the workaround:
	//   1) cache_buster()
	//    the work around is a two (sometimes three) part solution. first, even on cached pages, we add a 'javascript' (php script in disguise) that loads no matter what. we
	//    make sure that the javascript appears to be a 'non-cached' or 'non-cacheable' asset, by adding the uniqid() version number url parameter. this tricks most cache
	//    utilities into not caching the javascript, which in turn spawns the cron. the script is light-weight, so it will not cause too much extra server load.
	//   2) cache_buster_cron_url()
	//    some cache utilities prevent the wp-cron.php file from running, because they cache one version of it, and think it can be reused. this is similar to #1, only it 
	//    applies the same hack to the wp-cron.php request url. it tricks most cachers into thinking that it is a new url that has never been cached, thus requiring it to be
	//    're-cached', meaning it runs the cron.
	//   3) cache config changes ****
	//    if you use a cache utility like 'varnish', which lives in front of the webserver, and caches requets before they ever hit php, then you need to make some config
	//    changes, so that your cache does not keep getting 'nuked' (varnish term). for varnish, the settings change is very very simple. add the following two lines to your 
	//    varnish config file, inside the 'sub vcl_recv' range:
	//      if (req.url ~ "^/wp-cron.php") { return (pass); }
	//      if (req.url ~ "lou-spawn/spawn.php") { return (pass); }
	public static function cache_buster() {
		wp_enqueue_script('qs-sa-cache-hack', self::$plugin_url.'libs/lou-spawn/spawn.php', array(), uniqid('s'));
	}
	public static function cache_buster_cron_url($req) {
		$req['url'] = add_query_arg(uniqid('q'), uniqid('s'), $req['url']);
		return $req;
	}

	// allow externals to get the path of the plugin. mainly used for modules in this plugin
	public static function get_plugin_path($current, $as_url=true) {
		return $as_url ? self::$plugin_url : self::$plugin_path;
	}

	public static function get_plugin_settings($current) {
		return self::$settings;
	}

	// mark admin pages so that they can use our jquery ui theme
	public static function add_jquery_ui_marker($classes) {
		$classes = is_array($classes) ? $classes : preg_split('#\s+#', $classes);
		$classes[] = 'qssa';
		return implode(' ', array_unique($classes));
	}

	// run an autopost attempt, and update the post and logs with the result
	public static function run_autopost($args) {
		$args = wp_parse_args($args, array('attempt' => 1));
		$acct = self::_get_acct_by_id($args['instance_id']);
		$post = get_post($args['post_id']);
		$process = $success = $has_acct = $has_psot = true;

		do_action('qs-sa/autopost/log', 'details', 0, 'Started attempting auto post for post ['.$args['post_id'].'] on account ['.$args['name'].'].');

		if (!is_object($acct)) {
			do_action('qs-sa/autopost/log', 'log', 0,
				'Failed to autopost for account ['.$args['name'].']. Could not load account information. Is it still registered?', 'bad');
			$has_acct = $process = false;
		}

		if (!is_object($post)) {
			do_action('qs-sa/autopost/log', 'log', 0,
				'Failed to autopost for account ['.$args['name'].']. The post with id ['.$args['post_id'].'] could not be found. Was it deleted?', 'bad');
			$has_post = $process = false;
		}

		try {
			if ($process) $success = $acct->autopost($post);
			do_action('qs-sa/autopost/log', 'log', $post->ID,
				($success ? 'S' : 'Uns').'uccessfully finished autoposting post ['.$args['post_id'].':'.$post->post_title.'] to account ['.$acct->name().'].', 'good');
		} catch (Exception $e) {
			$success = false;
			do_action('qs-sa/autopost/log', 'log', $post->ID,
				'Failed autoposting post ['.$args['post_id'].':'.$post->post_title.'] to account ['.$args['name'].'].', 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID,
				'While trying to process the autoposing of post ['.$post->ID.'] on account ['.$acct->name().'], and Exception was thrown. Here are the details: '
						.$e->getMessage().' in ['.$e->getFile().'] @ '.$e->getLine(), 'bad');
		}

		do_action('qs-sa/autopost/log', 'details', 0,
			($success ? 'S' : 'Uns').'uccessfully finished attempting autopost for post ['.$args['post_id'].'] on account ['.$args['name'].'].', $success ? 'good' : 'bad');

		// on ANY failure, requeue this task to try again in 30 seconds
		if (!$success && $has_post && $has_acct && $args['attempt'] <= 5) {
			do_action('qs-sa/autopost/schedule', $acct, $post, 30, array('attempt' => (int)$args['attempt'] + 1));
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Retrying to sent post ['.$args['post_id'].':'.$post->post_title.'] to account ['.$acct->name().'] in 30 seconds.');
		}
	}

	protected static function _dbgbt($hops, $format='simple') {
		$dbg = debug_backtrace();
		$out = '';
		switch ($format) {
			case 'simple':
			default:
				$out = ( isset($dbg['class']) ? $dbg['class'] : ( isset($dbg['object']) ? 'OBJ:'.get_class($dbg['object']) : '' ) )
						.( isset($dbg['type']) ? $dbg['type'] : '' )
						.( isset($dbg['function']) ? $dbg['function'] : '[global]' )
						.( isset($dbg['file']) ? ' in '.$dbg['file'] : '' )
						.( isset($dbg['line']) ? ' @ '.$dbg['line'] : '' );
			break;
		}
		return $out;
	}

	// if the post meets the requirements for autoposting, then mark it as possibly needing scheduling.
	// this is an attempt to allow use to save post_meta before we try to schdule events. that will allow us to override the default settings for each account, on a post level.
	public static function maybe_schedule_required($new_status, $old_status, $post) {
		// only perform this check on 'publish'
		if ($new_status != 'publish') return;

		// maybe requires scheduling
		self::$requires_scheduling[$post->ID.''] = 1;
	}

	// possibly schedule an autoposting, based on registered account settings
	public static function maybe_schedule_autoposting($post_id, $post) {
		if (!isset(self::$requires_scheduling[$post_id.''])) return;
		if (!isset($_POST['_qssa_account_settings'], $_POST['_qssa_account_settings'][''.$post_id], $_POST['_qssa_account_settings'][''.$post_id]['update'])) return;

		// creates a gap between each account that needs an autoposting. split them every 10 seconds. meant to lighten server load by spreading out the autoposts
		$spacer = 1; $per = 13;
		// load any post overrides
		$post_acct_settings = self::_get_post_overrides($post);

		// foreach account
		foreach (self::$accounts as $acct) {
			$acct_info = $acct->instance_info();
			// if the account needs to auto-post this post, then
			if ($acct->is_enabled() && $acct->can_autopost($post) && !$acct->is_autopost_blocked($post)) {
				$post_sets = wp_parse_args(isset($post_acct_settings[$acct_info['instance_id']]) ? $post_acct_settings[$acct_info['instance_id']] : '', self::$default_post_overrides);
				if ($post_sets['enabled']) {
					do_action('qs-sa/autopost/schedule', $acct, $post, $spacer, array('rand' => rand(0, PHP_INT_MAX)));
					$spacer += $per;
				}
			}
		}
	}

	// actually schedule the task
	public static function schedule_autopost($acct, $post, $seconds=900, $extra=array()) {
		// schedule the auto post attempt
		$acct_info = $acct->instance_info();
		$acct_info['post_id'] = $post->ID;
		$acct_info['name'] = $acct->name('raw');
		$acct_info = wp_parse_args($extra, $acct_info);
		wp_schedule_single_event(microtime(true) + $seconds, 'qs-sa/autopost/run', array($acct_info));
	}

	// register all relevant admin assets
	public static function register_admin_assets() {
		// are we in script debug?
		$suffix = SCRIPT_DEBUG ? '' : '.min';

		// styles
		wp_register_style('qs-sa-admin-primary', self::$plugin_url.'assets/css/admin/primary.css', array(), self::$version);
		wp_register_style('qs-sa-admin-jquery-ui', self::$plugin_url.'assets/css/jquery-ui/qssa/jquery-ui-1.10.4.custom'.$suffix.'.css', array(), '1.10.4');
		wp_register_style('jquery-chosen', self::$plugin_url.'assets/js/lib/chosen/chosen'.$suffix.'.css', array(), '1.0');

		// javascripts
		wp_register_script('jquery-chosen', self::$plugin_url.'assets/js/lib/chosen/chosen.jquery'.$suffix.'.js', array('jquery'), '1.0');
		wp_register_script('qs-tools', self::$plugin_url.'assets/js/tools.js', array('jquery'), '0.5');
		wp_register_script('qs-sa-admin-settings', self::$plugin_url.'assets/js/admin/settings.js', array(
			'qs-tools',
			'jquery-chosen',
			'jquery-ui-dialog',
			'jquery-ui-tabs',
		), self::$version);
	}

	// on page load in the admin, enqueue the appropriate assets
	public static function load_admin_assets($hook) {
		// pretty much needed on every page
		wp_enqueue_style('qs-sa-admin-primary');

		// based on the page, load only certain assets
		switch ($hook) {
			case self::$admin_pages['settings']['hook']:
				wp_enqueue_style('jquery-chosen');
				wp_enqueue_style('qs-sa-admin-jquery-ui');
				wp_enqueue_script('qs-sa-admin-settings');
				wp_localize_script('qs-sa-admin-settings', '_qs_ap_admin_settings', array(
					'nonce' => wp_create_nonce('qs-sa/admin-ajax'),
					'templates' => self::_admin_ui_templates(),
				));
			break;
		}

		do_action('qs-sa/admin/page-loading', $hook);
		do_action('qs-sa/admin/page-loading/hook='.$hook);
	}

	// add the admin pages to the menu, storing the respective hooks in our storage array for later use on admin_enqueue_scripts
	public static function create_admin_pages() {
		// setup the primary settings page
		self::$admin_pages['settings']['hook'] = add_options_page(
			'Quadshot Social Automator Settings',
			'<span class="qs-brand">[QS]</span> Social Automator',
			'manage_options',
			self::$admin_pages['settings']['slug'],
			array(__CLASS__, 'ap_settings')
		);

		// setup settings page save
		add_action('load-'.self::$admin_pages['settings']['hook'], array(__CLASS__, 'ap_save_settings'));

		// load settings page specific asset and hookss
		add_action('admin_head-'.self::$admin_pages['settings']['hook'], array(__CLASS__, 'ap_head_settings'));
	}

	public static function admin_page_url($current, $short=false, $url=true) {
		$sets = $short && is_scalar($short) && isset(self::$admin_pages[$short]) ? self::$admin_pages[$short] : self::$admin_pages['settings'];
		return $url ? add_query_arg(array('page' => $sets['slug']), admin_url('/options-general.php')) : $sets;
	}

	public static function reverify_page_url($current, $instance_id, $extra='') {
		$extra = wp_parse_args($extra, array());
		$extra = wp_parse_args(array('rv' => $instance_id), $extra);
		$url = apply_filters('qs-sa/url/admin/page', '', 'settings');
		return add_query_arg($extra, $url);
	}

	// draw the settings page
	public static function ap_settings() {
		// get the list of available account type modules registered
		$available = self::$available;
		?>
			<div class="wrap">
				<div id="qs-icon qs-sa-settings icon-tools" class="icon32"></div>
				<h2><?php echo get_admin_page_title() ?></h2>
				
				<div class="inside">
					<h3>Accounts</h3>

					<div class="top-actions non-form-actions" rel="top-actions">
						<div class="action">
							<select rel="acct-type" class="use-chosen">
								<?php foreach ($available as $slug => $network): /* create a list of available account type modules to select from */ ?>
									<option value="<?php echo esc_attr($slug) ?>"><?php echo force_balance_tags($network['display_name']) ?></option>
								<?php endforeach; ?>
							</select>
							<input type="button" value="+" class="button" rel="add-btn" scope=".action" from='[rel="acct-type"]' />
						</div>
					</div>


						<div class="qs-sa-accounts" rel="account-list">
							<?php self::_draw_accounts(); /* draw already configured accounts */ ?>
						</div>

				</div>
			</div>
		<?php
	}

	// things to do on page load of the admin settings page
	public static function ap_head_settings() {
		add_filter('admin_body_class', array(__CLASS__, 'add_jquery_ui_marker'), 10);
	}

	// possibly save the settings page, and redirect to prevent reload-resave
	public static function ap_save_settings() {
		self::_load_settings_box_cookie();
		$post = $_POST;
		// make sure we are attempting to save our settings
		$nonce = isset($post['qs-sa-nonce']) ? $post['qs-sa-nonce'] : false;
		if (!wp_verify_nonce($nonce, 'save-qs-sa-settings-now')) return;

		// sequester our primary plugin settings and actions
		$del_acct = isset($post['delete-acct']) && is_array($post['delete-acct']) ? $post['delete-acct'] : array();
		$basic = $post[self::$settings_slug];

		// overlay our updated settings on top of the existing ones
		self::$settings = wp_parse_args($basic, self::$settings);
		self::$settings['accounts'] = wp_parse_args($basic['accounts'], self::$settings['accounts']);

		// process any non-ajax delete requests
		foreach ($del_acct as $del_acct_id => $yes) if ($yes) self::_delete_account($del_acct_id);

		// save all settings
		self::_save_settings(true);

		// reload all accounts
		self::_load_accounts();

		// notify modules and plugins that we just saved settings, so that they can do their own save
		do_action('qs-sa/settings/save', $post, self::$settings);

		// reload all accounts again
		self::_load_accounts();

		// aggregate a list of all post types that could have autoposting
		$post_types = array();
		foreach (self::$accounts as $acct) $post_types = array_merge($post_types, $acct->post_types());
		self::$settings['post_types'] = array_unique($post_types);

		// save all settings - with the post_types settings
		self::_save_settings();

		// redirect so that the save cannot happen twice on accidental refresh
		$url = apply_filters('qs-sa/admin/setings/save/redirect', add_query_arg(array('updated' => 1), remove_query_arg(array('updated'))), self::$settings);
		wp_safe_redirect($url);
		exit;
	}

	// handle ajax requests made in the admin
	public static function process_admin_ajax() {
		// organize data
		$post = $_POST;
		$nonce = isset($post['n']) ? $post['n'] : false;
		$sa = isset($post['sa']) ? $post['sa'] : '';

		// make sure we are receiving ajax from the settings page
		if (!wp_verify_nonce($nonce, 'qs-sa/admin-ajax')) return self::_out(array('e' => 'Could not process the request.'));

		// call any handlers attached to this ajax hook
		$resp = array('e' => array(), 'm' => array());
		$resp = apply_filters('qs-sa/ajax/admin/sa='.$sa, $resp, $post, $sa);
		$resp = apply_filters('qs-sa/ajax/admin', $resp, $post, $sa);

		if (empty($resp['e'])) unset($resp['e']);
		if (empty($resp['m'])) unset($resp['m']);

		self::_out($resp);
	}

	public static function aj_close_state($resp, $post) {
		$u = wp_get_current_user();
		$current = get_user_meta($u->ID, 'qssa-close-box', true);
		$current = is_array($current) ? $current : array();
		$resp['s'] = false;
		if (isset($post['acct_id'], $post['closed'])) {
			$current[$post['acct_id']] = $post['closed'];
			update_user_meta($u->ID, 'qssa-close-box', $current);
			$resp['s'] = true;
		}
		return $resp;
	}

	// ajax handler to create a new account
	public static function aj_new_acct($resp, $post) {
		// check that teh type was submitted and that it is a valid type
		$type = isset($post['type']) ? $post['type'] : false;
		if (!self::_available($post['type'])) {
			$resp['e'][] = 'Could not find type ['.$type.'] in the list of available acct-types.';
			return $resp;
		}

		// create the new blank account
		$cls = self::$available[$type]['class'];
		$acct = new $cls();

		// add the account to the account list, and save the account list
		$acct_info = $acct->instance_info();
		self::$settings['accounts'][$acct_info['instance_id']] = $acct_info['type'];
		self::_save_settings();

		// add the account base data to the response
		$resp['acct'] = $acct;

		// get the settings box for the account
		ob_start();
		self::_draw_account_settings($acct_info);
		$resp['html'] = ob_get_contents();
		ob_end_clean();

		// pass on the response
		return $resp;
	}

	// ajax handler to create a save account
	public static function aj_save_acct($resp, $post) {
		// only execute further if we have an account id (instance_id)
		$acct_id = isset($post['acct_id']) ? $post['acct_id'] : false;
		if (!$acct_id || !isset(self::$settings['accounts'][$acct_id])) {
			$resp['e'][] = 'Could not find the account to save it\'s settings. ['.$acct_id.']';
			return $resp;
		}

		// only execute further if we have an account (based on instance_id)
		$acct = isset(self::$accounts[$acct_id]) ? self::$accounts[$acct_id] : false;
		if (!$acct || !is_object($acct)) {
			$resp['e'][] = 'The was a problem loading the account. ['.$acct_id.']';
			return $resp;
		}

		// save the account's settings
		$post = $_POST;
		$acct->save($post, self::$settings);
		$acct_info = $acct->instance_info();

		// add the account base data to the response
		$resp['acct'] = $acct;

		// get the settings box for the account
		ob_start();
		self::_draw_account_settings($acct_info);
		$resp['html'] = ob_get_contents();
		ob_end_clean();

		// prevent infinite auth loop on multiple failures
		if ($acct->is_enabled() && (!isset($post['already-attempted']) || !$post['already-attempted'])) {
			$actions = $acct->get_needed_actions();
			if (!empty($actions)) $resp['actions'] = $actions;
		}

		// pass on the response
		return $resp;
	}

	// handle admin ajax request to delete an account
	public static function aj_delete_acct($resp, $post) {
		// check that we have an account id (instance_id)
		$acct_id = isset($post['acct_id']) ? $post['acct_id'] : false;
		if (empty($acct_id)) {
			$resp['e'][] = 'You must sumbit at least one acct_id to be deleted.';
			return $resp;
		}

		// verify that the account id belongs to a registered account (instance_id)
		$acct = self::_get_acct_by_id($acct_id);
		if (!is_object($acct)) {
			$resp['e'][] = 'Could not find that account.';
			return $resp;
		}
		// attempt the account deletion
		$res = self::_delete_account($acct_id);

		// if it was deleted, save settings
		if ($res) self::_save_settings();

		// mark with appropriate response
		$resp['s'] = $res;

		return $resp;
	}

	// generic ajax response function, based on requested response type and response data
	protected static function _out($data, $die=true) {
		// find the expected response type
		$resp_type = self::_determine_response_type();
		// indicate that the response is of that type
		header('Content-Type: '.$resp_type);

		// depending on the response type, do something different with the response data
		switch ($resp_type) {
			// text and html responses are mostly the same
			case 'text/plain':
			case 'text/html':
				if (!is_scalar($data)) {
					if (isset($data['html'])) echo force_balance_tags($data['html']);
					else if (isset($data['text'])) echo $data['text'];
					else if (isset($data['out'])) echo $data['out'];
					else echo @json_encode($data);
				} else {
					echo $data;
				}
			break;
			
			// javascript/json responses are favored by me, so this is the default, and the easiest to print out
			case 'text/javascript':
			case 'application/json':
			case 'text/json':
			case 'application/javascript':
			default:
				echo @json_encode($data);
			break;
		}

		// die if needed
		if ($die) exit;
	}

	// break down the request header 'accept' value, and determine the first accepted response type, or select our default
	protected static function _determine_response_type() {
		// get the header value
		$accepts_raw = isset($_SERVER['HTTP_ACCEPT']) ? $_SERVER['HTTP_ACCEPT'] : self::$def_ret_type;

		// break it up into sections, of which we are only concerned with the first
		$parts = preg_split('#\s*;\s*#', $accepts_raw);

		// split the first grouping up by the standard delimiters
		$types = preg_split('#\s*,\s*#', (string)array_shift($parts));

		// get the first of the accepted types
		$accept = trim((string)array_shift($types));

		// return the resulting type, default to our primary default type
		return $accept ? $accept : self::$def_ret_type;
	}

	protected static function _delete_account($acct_id) {
		// load the account
		$acct = self::_get_acct_by_id($acct_id);
		if (!$acct) return false;

		// process the delete request
		$res = $acct->delete($acct_id);

		// if the request was a success, then remove the account from the account list
		if ($res) unset(self::$settings['accounts'][$acct_id]);

		return $res;
	}

	// cycle through the registered accounts, and load any matching account based on the acct_id (instance_id) of the account
	protected static function _get_acct_by_id($acct_id) {
		if (isset(self::$accounts[$acct_id])) return self::$accounts[$acct_id];
		// set default values
		$acct = $acct_info = false;

		// if the acct_id is not empty
		$acct_id = trim((string)$acct_id);
		if ($acct_id) {
			// if the account is a registered one, construct a basic information array
			if (isset(self::$settings['accounts'][$acct_id]))
				$acct_info = array('type' => self::$settings['accounts'][$acct_id], 'instance_id' => $acct_id);

			// if we found one, and it is an account type that is a valid account type, load up the account object
			if ($acct_info && self::_available($acct_info['type'])) {
				// load the account object, of the appropriate type
				$cls = self::$available[$acct_info['type']]['class'];
				self::$accounts[$acct_id] = $acct = new $cls($acct_info['instance_id']);
			}
		}

		// return any account we found, false on failure
		return $acct;
	}

	// generic function to validate an account type (module)
	protected static function _available($type) {
		// if no type was submitted, auto-fail
		if (!$type) return false;

		// if the module does not exist, fail
		if (!isset(self::$available[$type]) || !is_array(self::$available[$type])) return false;

		// if the class for the module does not exist, fail
		if (!isset(self::$available[$type]['class']) || !class_exists(self::$available[$type]['class'])) return false;

		// success
		return true;
	}

	// generic function to determine a code for why the account type (module) could not load
	protected static function _msg_available($type) {
		// if no type was submitted, auto-fail
		if (!$type) return 'The account type is an empty value.';

		// if the module does not exist, fail
		if (!isset(self::$available[$type]) || !is_array(self::$available[$type]))
			return 'The type of account is not one that is currently available. Perhaps a Pro plugin got diabled?';

		// if the class for the module does not exist, fail
		if (!isset(self::$available[$type]['class']) || !class_exists(self::$available[$type]['class']))
			return 'The account type is registered, but we cannot find the code to handle it. Maybe we need to reinstall a plugin?';

		// success
		return 'Could not determine why this account was not loaded.';
	}

	// draw the list of currently configured accounts
	protected static function _draw_accounts() {
		$accts = self::$settings['accounts'];
		?>
			<?php if (empty($accts)): ?>
				<p class="no-accts empty">You have not yet configured any accounts.</p>
			<?php else: ?>
				<?php foreach ($accts as $acct_id => $acct_type): $acct = array('type' => $acct_type, 'instance_id' => $acct_id); ?>
					<?php self::_draw_account_settings($acct); ?>
				<?php endforeach; ?>
			<?php endif; ?>
		<?php
	}

	protected static function _maybe_recover_acct_name($acct) {
		global $wpdb;
		$name = $acct['type'].':'.$acct['instance_id'];

		$maybe = $wpdb->get_var($wpdb->prepare('select option_value from '.$wpdb->options.' where option_name like %s limit 1', '%'.$acct['instance_id'].'%'));
		if ($maybe && ($maybe_arr = @maybe_unserialize($maybe)) && is_array($maybe_arr) && isset($maybe_arr['desc'])) {
			$name = '<strong>'.$acct['type'].' - '.$maybe_arr['desc'].'</strong> (<em>'.$acct['instance_id'].'</em>)';
		}

		return $name;
	}

	// draws the primary settings box for a given registered account
	protected static function _draw_account_settings($acct) {
		$available = self::$available;

		// load the account
		$acct_obj = is_array($acct) && isset($acct['instance_id']) ? self::_get_acct_by_id($acct['instance_id']) : false;

		// if the account is not valid, print an error
		if (!self::_available($acct['type'])) {
			$name = self::_maybe_recover_acct_name($acct);
			?>
				<div class="bad-account account-settings" sort="000-no-name" rel="acct">
					<p>The account [<?php echo $name ?>] could not be loaded. <?php echo self::_msg_available($acct['type']) ?></p>
				</div>
			<?php
			return;
		}

		// get any notifications from the module
		$notify = $acct_obj->get_notify();

		// draw the wrapper box for the primary account settings. this is something akin to a metabox elsewhere in WP
		$title = $acct_obj->name();
		?>
			<div class="account-settings" sort="<?php echo esc_attr($acct_obj->name('raw')) ?>" acct-id="<?php echo esc_attr($acct['instance_id']) ?>" rel="acct">
				<input type="hidden" name="acct_id" value="<?php echo esc_attr($acct['instance_id']) ?>" />
				<input type="hidden" name="<?php echo self::$settings_slug.'[accounts]['.$acct['instance_id'].']' ?>" value="<?php echo $acct['type'] ?>" />

				<div class="header ui-widget-header <?php echo $notify ? 'ui-state-error' : '' ?>">
					<?php echo $acct_obj->icon() ?>
					<div class="notification ui-icon ui-icon-alert"></div>
					<div class="account-control-box" rel="control-acct"></div>
					<?php self::_account_controls($acct_obj); ?>
					<h4 class="qs-sa-title">
						<input type="hidden" name="<?php echo $acct_obj->field_name('active') ?>" id="<?php echo $acct_obj->field_id('active') ?>" value="0" />
						<input type="checkbox"
								name="<?php echo $acct_obj->field_name('active') ?>"
								id="<?php echo $acct_obj->field_id('active') ?>"
								value="1" <?php checked(true, $acct_obj->is_enabled()) ?> />
						<span class="full-title"><?php echo $title ?></span>
					</h4>
				</div>

				<div class="inner">
					<?php self::_get_acct_messages($acct_obj) ?>

					<div class="use-tabs">
						<ul>
							<li><a href="#<?php echo $acct_obj->field_id() ?>-basic">Basic</a></li>
							<li><a href="#<?php echo $acct_obj->field_id() ?>-advanced">Advanced</a></li>
							<?php do_action('qs-sa/settings/draw/tabs', $acct['type'], $acct_obj) ?>
							<?php do_action('qs-sa/settings/draw/tabs/type='.$acct['type'], $acct_obj) ?>
							<?php do_action('qs-sa/settings/draw/tabs/instance_id='.$acct['instance_id'], $acct_obj) ?>
						</ul>
						
						<div class="tab-panel" id="<?php echo $acct_obj->field_id() ?>-basic">
							<?php if ($notify): ?>
								<div class="notification-msg"><?php echo $notify; ?></div>
							<?php endif; ?>
							<?php $acct_obj->settings_basic(); ?>
						</div>
						<div class="tab-panel" id="<?php echo $acct_obj->field_id() ?>-advanced"><?php $acct_obj->settings_advanced(); ?></div>
						<?php do_action('qs-sa/settings/draw/tab-panels', $acct['type'], $acct_obj) ?>
						<?php do_action('qs-sa/settings/draw/tab-panels/type='.$acct['type'], $acct_obj) ?>
						<?php do_action('qs-sa/settings/draw/tab-panels/instance_id='.$acct['instance_id'], $acct_obj) ?>
					</div>

					<div class="bottom-actions">
						<input type="button" class="button primary-button" rel="save-acct" value="Save Account" scope='[rel="acct"]' />
					</div>
				</div>
			</div>
		<?php
	}

	protected static function _get_acct_messages($acct_obj) {
		ob_start();
		$acct_obj->msgs();
		$out = ob_get_contents();
		ob_end_clean();

		if (!empty($out)):
			?><div class="messages-wrapper icon-16"><?php echo force_balance_tags($out) ?></div><?php
		endif;
	}

	// draw the extra account controls, which are part of the primary account settings box
	protected static function _account_controls($acct) {
		// basic needed data
		$id = uniqid();
		$acct_info = $acct->instance_info();

		// generate a list of available actions, based on the account
		$actions = array(
			sprintf(
				'<a href="#%s" id="%s" rel="%s" class="show-if-js action-link">%s</a><span class="hide-if-js"><input type="checkbox" name="%s[%s]" value="1" />%s</span>',
				'delete-'.$id,
				'delete-'.$id,
				'delete-btn',
				'delete',
				'delete-acct',
				$acct_info['instance_id'],
				'delete account?'
			),
		);
		if ($acct->can_reverify()) {
			$actions[] = sprintf(
				'<a rel="reverify" href="%s">%s</a>',
				apply_filters('qs-sa/url/reverify', '#', $acct_info['instance_id']),
				'reverify'
			);
		}
		$actions = apply_filters('qs-sa/modules/settings/controls', $actions, $acct);

		// draw the actions list
		?>
			<div class="account-controls" rel="controls"><?php echo implode(' | ', $actions) ?></div>
		<?php
	}

	// get a list of templates used in the admin settings interface
	protected static function _admin_ui_templates() {
		$templates = array();

		$templates['ays-delete'] = '<div class="are-you-sure" title="Are you sure?">'
				.'<p>Are you sure you want to delete the account <span class="account-name"></span>?</p>'
			.'</div>';

		$templates['deleting'] = '<div class="deleting" title="Deleting...">'
				.'<p>We are deleting the account permanently. Please wait...</p>'
			.'</div>';

		$templates['deleted'] = '<div class="deleted" title="Deleted.">'
				.'<p>The account <span class="account-name"></span> has been deleted successfully.</p>'
			.'</div>';

		return apply_filters('qs-sa/admin-ui/templates', $templates);
	}

	public static function setup_meta_boxes() {
		foreach (self::$settings['post_types'] as $screen) {
			$accounts = self::_accounts_for_post_type($screen);
			if (empty($accounts)) continue;

			add_meta_box(
				'qs-sa-accounts',
				'Quadshot Social Automator - Accounts',
				array(__CLASS__, 'mb_accounts'),
				$screen,
				'normal',
				'high'
			);
		}
	}

	public static function mb_accounts($post, $mb) {
		$post_acct_settings = self::_get_post_overrides($post);
		$defs = self::$default_post_overrides;
		$drawn = 0;
		$accounts = self::_accounts_for_post_type($post->post_type);
		?>
			<input type="hidden" name="_qssa_account_settings[<?php echo $post->ID ?>][update]" value="1" />
			<ul class="accounts icon-16">
				<?php foreach ($accounts as $acct): ?>
					<?php if ($acct->is_enabled()): ?>
						<?php $acct_info = $acct->instance_info(); ?>
						<?php $post_acct_settings[$acct_info['instance_id']] = wp_parse_args($post_acct_settings[$acct_info['instance_id']], $defs); ?>
						<li class="account">
							<div class="header">
								<h4 class="account-name"><?php echo $acct->name() ?></h4>
								<?php self::_get_mb_actions($acct, $post) ?>
							</div>

							<div class="inner">
								<?php if (!$acct->is_autopost_blocked($post)): ?>
									<div class="field-wrap">
										<?php $field = self::_field_name($acct, $post, 'enabled'); ?>
										<input type="hidden" name="<?php echo $field ?>" value="0" />
										<input type="checkbox" name="<?php echo $field ?>" value="1" <?php
											checked(true, (bool)$post_acct_settings[$acct_info['instance_id']]['enabled'] && $post->post_status != 'publish')
										?> />
										<span>Submit to this account?</span>
									</div>
								<?php else: ?>
									<div class="autopost-blocked-reason"><?php echo force_balance_tags($acct->why_is_autopost_blocked($post)) ?></div>
								<?php endif ?>
							</div>
						</li>
						<?php $drawn++; ?>
					<?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<?php if (!$drawn): ?>
				<p>There are currently no accounts for you to autopost to.</p>
			<?php endif; ?>
		<?php
	}

	protected static function _accounts_for_post_type($post_type) {
		static $cache = array();
		if (isset($cache[$post_type])) return $cache[$post_type];

		$out = array();
		foreach (self::$accounts as $acct_id => $acct) if (in_array($post_type, $acct->post_types())) $out[$acct_id] = $acct;
		return $cache[$post_type] = $out;
	}

	protected static function _field_name($acct, $post, $field, $base_name='_qssa_account_settings') {
		$acct_info = $acct->instance_info();
		return sprintf('%s[%d][%s][%s]', $base_name, $post->ID, $acct_info['instance_id'], $field);
	}

	public static function save_post_meta_bax_data($post_id, $post) {
		if (!isset($_POST['_qssa_account_settings'], $_POST['_qssa_account_settings'][$post_id.''], $_POST['_qssa_account_settings'][$post_id.'']['update'])) return;
		$data = $_POST['_qssa_account_settings'][$post_id.''];
		unset($data['update']);
		self::_set_post_overrides($post_id, $data);
	}

	protected static function _get_post_overrides($post_id) {
		if (is_object($post_id)) $post_id = $post_id->ID;
		$res = get_post_meta($post_id, '_qssa_post_account_settings', true);
		return $res ? $res : array();
	}

	protected static function _set_post_overrides($post_id, $data) {
		$current = self::_get_post_overrides($post_id);
		update_post_meta($post_id, '_qssa_post_account_settings', wp_parse_args($data, $current));
	}

	protected static function _get_mb_actions($acct, $post) {
		$acct_info = $acct->instance_info();
		$actions = array();

		$repost = $acct->is_autopost_blocked($post);
		$why = $acct->why_is_autopost_blocked($post, 'code');
		if ($repost || $post->post_status == 'publish') {
			switch ($why) {
				case 1:
					$actions[] = sprintf(
						'<a href="%s">%s</a>',
						add_query_arg(array('rp' => $acct_info['instance_id'], 'pi' => $post->ID)),
						$repost ? 'repost' : 'intial-post'
					);
				break;
			}
		}

		$actions = apply_filters('qs-sa/post/account/actions', $actions);

		?>
			<?php if (count($actions)): ?>
				<div class="actions"><?php echo implode('', $actions) ?></div>
			<?php endif; ?>
		<?php
	}

	protected static function _load_settings_box_cookie() {
		$u = wp_get_current_user();

		// load the settings from the db
		$vals = get_user_meta($u->ID, 'qssa-close-box', true);
		if (is_array($vals)) foreach ($vals as $k => $v) {
			setcookie('qssa-close-box-'.$k, "$v", time() + 63072000, '/wp-admin');
		}
	}

	// load all includes that are present and not hidden
	protected static function _load_includes() {
		// give plugins the opportunity to add their own modules
		$paths = apply_filters('qs-sa/includes/paths/extra', array(trailingslashit(self::$plugin_path.'inc')));

		// include any files found
		self::_load_files($paths, '#^[^\.].*\.inc\.php$#i');
	}

	// load all modules that are present, and not hidden
	protected static function _load_modules() {
		// give plugins the opportunity to add their own modules
		$paths = apply_filters('qs-sa/modules/paths/extra', array(trailingslashit(self::$plugin_path.'modules')));

		// include any files found
		self::_load_files($paths, '#^[^\.].*\.module\.php$#i');
	}

	// generic include files function. includes files based on regex pattern
  protected static function _load_files($paths, $regex='#^.+\.php$#i', $require=true, $once=true) {
    // cycle through the top-level include folder list
    foreach ($paths as $dir) {
			$dir = trailingslashit($dir);
      if (file_exists($dir) && is_dir($dir)) {
        // if the subdir exists, then recursively generate a list of all *.php (aka: regex matched files) files below the given subdir
        $iter = new RegexIterator(
          new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),  
            RecursiveIteratorIterator::SELF_FIRST
          ),  
          $regex,
          RecursiveRegexIterator::GET_MATCH
        );  

				if ($require) {
					// require every file found
					if ($once) foreach ($iter as $fullpath => $arr) require_once $fullpath;
					else foreach ($iter as $fullpath => $arr) require $fullpath;
				} else {
					// include every file found
					if ($once) foreach ($iter as $fullpath => $arr) include_once $fullpath;
					else foreach ($iter as $fullpath => $arr) include $fullpath;
				}
      }   
    }   
  }

	// save all settings
	protected static function _save_settings($suppress_filter=false) {
		// assume we have a valid settings array already
		update_option(self::$settings_slug, self::$settings);

		// notify other plugins we saved the settings
		if (!$suppress_filter)
			do_action('qs-sa/settings/saved', self::$settings);
	}

	// load the settings
	protected static function _load_settings() {
		// allow other plugins to modify defaults. they can add their own options, or modify core behavior if desired
		self::$defaults = apply_filters('qs-sa/settings/default', self::$defaults);

		// load the settings and overlay them on the defaults. allow other plugins again to modify this, so they can have their own settings
		self::$settings = apply_filters('qs-sa/settings/loaded', wp_parse_args(get_option(self::$settings_slug), self::$defaults));

		// sanitize, just in case
		self::$settings['accounts'] = is_array(self::$settings['accounts']) ? self::$settings['accounts'] : self::$defaults['accounts'];
	}

	// load all the accounts that are currently registered
	protected static function _load_accounts() {
		self::_reset_accounts();
		// load all registered accounts, registering their hooks
		foreach (self::$settings['accounts'] as $acct_id => $type) self::_get_acct_by_id($acct_id);
	}

	// reset the currently loaded accounts
	protected static function _reset_accounts() {
		// if there are none to empty then do nothing
		if (empty(self::$accounts)) return;

		// unregister the account and all it's hooks
		foreach (self::$accounts as $acct_id => $acct) $acct->unregister();

		// empty the accounts array
		self::$accounts = array();
	}
}

// cheap security
if (defined('ABSPATH') && function_exists('add_action')) {
	qs_social_automator::pre_init();
}
endif;
