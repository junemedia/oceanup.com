<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; /* prevent direct access */ use QS\APIs\GoogleException as ge, QS\APIs\gplus as gp, QS\helpers as h;

if (!class_exists('qssa_google')):
// google+ module. creates Google+ accounts (for this plugin) and interacts withi google+ to create posts
class qssa_gplus extends qssa__base_module {
	// static settings
	protected static $module_name = 'Google Plus'; // static version of module name
	protected static $posting_types = array( // array of valid posting_types
		'text-post' => 'Text Only',
		'image-post' => 'Text + Image',
		'text-post-with-link' => 'Text + "attached" permalink',
	);

	// when first including this file, run this
	public static function pre_init() {
		add_action('qs-sa/modules/available', array(__CLASS__, 'add_module'), 10);
	}

	// adds this module to the available module list
	public static function add_module($modules) {
		$slug = sanitize_title_with_dashes(self::$module_name);
		$modules[$slug] = array(
			'display_name' => self::$module_name,
			'class' => __CLASS__,
		);
		return $modules;
	}

	protected $_type = __CLASS__; // store this class for possible use in parent class
	protected $display_name = 'Google Plus'; // same as static module name above

	protected $settings_slug = '_qssa_gplus'; // setting slug prefix. all stored account settings wp_options keys will begin with this
	protected $settings = array(); // holder for this instance settings
	protected $defaults = array( // default settings
		'active' => true,
		'desc' => '',
		'username' => '',
		'password' => '',
		'post_to' => '',
		'posting_type' => 'text-post',
		'auto_post_for' => array('post'),
		'api_instance' => array(),
	);
	protected $gapi = null;

	// draw basic settings panel
	public function settings_basic() {
		?>
			<div class="gplus-settings gplus-settings-basic">
				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('desc') ?>">Display Name</label>
					<input type="text" name="<?php echo $this->_name('desc') ?>"
							id="<?php echo $this->_id('desc') ?>"
							value="<?php echo esc_attr($this->settings['desc']) ?>"
							class="widefat" />
					<span class="helper">
						Name that will remind you which account this is, usually descriptive.
						Ex: My Business Page
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('username') ?>">Username</label>
					<input type="text" name="<?php echo $this->_name('username') ?>"
							id="<?php echo $this->_id('username') ?>"
							value="<?php echo esc_attr($this->settings['username']) ?>"
							class="widefat" />
					<span class="helper">
						This is your username for Google Plus, which is usually your Google Email address.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('password') ?>">Password</label>
					<input type="password" name="<?php echo $this->_name('password') ?>"
							id="<?php echo $this->_id('password') ?>"
							value="<?php echo esc_attr($this->settings['password']) ?>"
							class="widefat" />
					<span class="helper">
						This is your password for Google Plus.
					</span>
				</div>

				<?php $this->choose_posting_page() ?>
			</div>
		<?php
	}

	// draw advanced settings panel
	public function settings_advanced() {
		$posting_types = self::$posting_types;
		?>
			<div class="facebook-settings facebook-settings-advanced">
				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('posting_type') ?>">Posting Type</label>
					<?php foreach ($posting_types as $slug => $label): ?>
						<span class="cb-wrap">
							<input type="radio" name="<?php echo $this->_name('posting_type') ?>"
									id="<?php echo $this->_id('posting_type') ?>"
									value="<?php echo esc_attr($slug) ?>"
									<?php checked($slug, $this->settings['posting_type']) ?> />
							<span class="cb-label"><?php echo $label ?></span>
						</span>
					<?php endforeach; ?>
					<span class="helper">
						Designates the type of post to create on your target Google Plus url.
					</span>
				</div>

				<?php $this->_draw_auto_post_for() ?>
				<?php $this->_draw_message_format() ?>
			</div>
		<?php
	}

	public function allows_image() {
		return $this->settings['posting_type'] == 'image-post';
	}

	protected function _current_endpoint($endpoints) {
		$current = $this->settings['post_to'];

		if (empty($current) && !empty($endpoints)) {
			foreach ($endpoints as $ep_id => $endpoint) if ($endpoint['type'] == 'profile') {
				$current = $ep_id;
				break;
			}
		}

		return $current;
	}

	public function choose_posting_page() {
		if (!$this->_load_sdk()) return;

		$endpoints = array();

		try {
			$endpoints = $this->gapi->getEndpoints();
		} catch (ge $e) { }
		
		$current = $this->_current_endpoint($endpoints);
		?>
			<div class="qs-sa-field">
				<label for="<?php echo $this->_id('post_to') ?>">Where would you like to autopost to?</label>
				<?php if (!empty($endpoints)): ?>
					<select name="<?php echo $this->_name('post_to') ?>"
							id="<?php echo $this->_id('post_to') ?>"
							class="use-chosen widefat">
						<?php foreach ($endpoints as $ep_id => $endpoint): ?>
							<option value="<?php echo esc_attr($ep_id) ?>" <?php selected($ep_id, $current) ?>><?php
								echo $endpoint['name'].' ['.ucfirst($endpoint['type']).']'
							?></option>
						<?php endforeach; ?>
					</select>
					<span class="helper">
						This will determine where we autopost to.
						Select one of the pages you have access to, and we will send all autoposting to that page.
						If you do not manually select one, we will autopost to your profile page.
					</span>
				<?php else: ?>
					<p>You must first verify the account before you can select where to send your autoposts.</p>
				<?php endif; ?>
			</div>
		<?php
	}

	public function can_reverify($settings='') {
		$s = wp_parse_args($settings, $this->settings);
		return isset($s['username'], $s['password'])
			&& !empty($s['username'])
			&& !empty($s['password']);
	}

	public function get_needed_actions() {
		$actions = array();

		if ($this->settings['needs_reverify']) {
			$rurl = apply_filters('qs-sa/url/reverify', '', $this->instance_id, array('popup' => 1));
			$actions[] = array(
				'name' => 'block-acct',
				'type' => 'block',
				'msg' => '<h1>Verifying...</h1>',
			);
			$actions[] = array(
				'name' => 'verification-window',
				'type' => 'popup',
				'url' => $rurl,
			);
			$actions[] = array(
				'name' => 'reload',
				'type' => 'refresh',
			);
		}

		return $actions;
	}

	protected function _maybe_needs_verify($settings) {
		if (!self::_load_sdk_class()) {
			$settings['notify'] = 'Could not load the Google Plus QS-SDK, which is needed to post to Google Plus.';
			do_action('qs-sa/autopost/log', 'log', 0, 'Failed to load the Google Plus QS-SDK. You may need to reinstall the plugin.', 'bad');
			return $settings;
		}

		// if no reverification is needed, then just update the posting destination
		if (!$settings['needs_reverify'] && $settings['api_instance']['logged_in']) {
			$gapi = new gp(array(
				'username' => $settings['username'],
				'password' => $settings['password'],
				'post_to_page' => $settings['post_to'],
			));

			$instance = $settings['api_instance'];
			if (!empty($instance)) $gapi->setInstance($instance);
			$gapi->setSettings(array('post_to_page' => $settings['post_to']));
			$settings['api_instance'] = $gapi->getInstance();

			return $settings;
		}
		$settings['needs_reverify'] = false;

		$msg = '';

		$gapi = new gp(array(
			'username' => $settings['username'],
			'password' => $settings['password'],
			'post_to_page' => $settings['post_to'],
		));

		$instance = $settings['api_instance'];
		if (!empty($instance)) $gapi->setInstance($instance);

		try {
			$gapi->verify();
		} catch(ge $e) {
			$msg = 'Failed to verify the account ['.$this->name().']. '.$e->getMessage().' ';
			$settings['needs_reverify'] = true;
			do_action('qs-sa/autopost/log', 'log', 0, 'Failed to verify the account ['.$this->name().']. Make sure your login credentials are correct, and try again.', 'bad');
			do_action('qs-sa/autopost/log', 'details', 0, 'Could not verify account ['.$this->name().']. Here are the details: '
				.$this->_debug_dump(array('e' => $e, $settings)), 'bad');
		} catch(Exception $e) {
			$msg = 'Failed to verify the account ['.$this->name().']. An unexpected error occured. ';
			$settings['needs_reverify'] = true;
			do_action('qs-sa/autopost/log', 'log', 0, 'We encountered an unexpected problem when tyring to verify the account ['.$this->name().']. '
				.'Make sure your login credentials are correct, and try again.', 'bad');
			do_action('qs-sa/autopost/log', 'details', 0, 'Unexpected Error. Could not verify account ['.$this->name().']. Here are the details: '
				.$this->_debug_dump(array('e' => $e, $settings)), 'bad');
		}

		$settings['notify'] = empty($msg)
			? ''
			: $msg.'Make sure your login credentials are correct and that you select a valid post location, and '
					.'<a rel="reverify" href="'.esc_attr(apply_filters('qs-sa/url/reverify', '', $this->instance_id)).'">try to reverify</a>.';
		$settings['api_instance'] = $gapi->getInstance();

		return $settings;
	}

	public function autopost($post, $use_settings='', $force=false) {
		$post = get_post($post);
		if ($this->already_autoposting($post, $force)) return;

		if (!$this->_load_sdk()) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Did not autopost, because we could not find the Google Plus QS-SDK.', 'bad');
			return false;
		}

		$args = array();

		switch ($this->settings['posting_type']) {
			default: break;

			case 'image-post':
				$img_id = $this->_get_thumbnail_id($post, $use_settings);
				if ($img_id) $args['_img_path'] = $this->_get_image_filename($img_id);
			break;

			case 'text-post-with-link':
				$args['url'] = get_permalink($post->ID);
			break;
		}

		$msg = $this->_get_message($post);

		try {
			$old_id = '';
			try {
				// delete any previously posted version of this post, that we have on record
				$old_id = get_post_meta($post->ID, $this->_settings_slug(), true);
				if ($old_id) $this->gapi->delete($old_id);
			} catch(ge $e) {
				do_action('qs-sa/autopost/log', 'details', $post->ID, 'Could not remove old post. Here are the details of this issue: '.$this->_debug_exception($e), 'bad');
			} catch(Exception $e) {
				do_action('qs-sa/autopost/log', 'details', $post->ID, 'Could not remove old post. An unexpected error occurred. This is what Google Plus said: '.$this->_debug_exception($e), 'bad');
			}

			$unique_id = $this->gapi->post($msg, $args);
			update_post_meta($post->ID, $this->_settings_slug(), $unique_id);
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Successfully '.($old_id ? 're' : '').'posted ['.apply_filters('the_title', $post->post_title).'] to account ['.$this->name().'] '
					.'published at '.sprintf('<a href="%s" target="_blank">this url</a>.', $this->posted_url($post)).' Any old urls will no longer work.', 'good');
			return true;
		} catch(ge $e) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Failed to autopost to Google Plus for account['.$this->name().']. '.$e->getMessage(), 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failed to autopost to Google Plus for account['.$this->name().']. '.$this->_debug_exception($e), 'bad');
		} catch(Exception $e) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Failed to autopost to Google Plus for account['.$this->name().']. An unexpected error occured. '.$e->getMessage(), 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failed to autopost to Google Plus for account['.$this->name().']. An unexpected error occured. '.$this->_debug_exception($e), 'bad');
		}
		do_action('qs-sa/autopost/log', 'log', $post->ID, 'no idea what is going on here');

		return false;
	}

	public function posted_url($post) {
		if (!$this->_load_sdk()) return '';
		$post = get_post($post);
		$unique_id = get_post_meta($post->ID, $this->_settings_slug(), true);
		return $this->gapi->urlFromId($unique_id);
	}

	public function reverify() {
		$this->_load_sdk();
		
		$popup = isset($_GET['popup']) ? $_GET['popup'] : 0;
		$this->_save('', true);

		if ($popup) {
			echo '<html><head><script type="text/javascript">if(window.opener&&window.opener._qssa_msg)window.opener._qssa_msg("'.esc_attr($msg).'");'
					.'window.close();</script></head><body><p>Processing...</p></body></html>';
			exit;
		}

		return !!$this->settings['notify'];
	}

	protected function _load_sdk() {
		if (self::_load_sdk_class()) {
			if (!isset($this->gapi)) {
				$this->gapi = new gp(array(
					'username' => $this->settings['username'],
					'password' => $this->settings['password'],
					'post_to_page' => $this->settings['post_to'],
				));

				$instance = $this->settings['api_instance'];
				if (!empty($instance)) $this->gapi->setInstance($instance);
			}
			return true;
		}

		return false;
	}

	// load and confirm the existence of the facebook sdk
	protected static function _load_sdk_class() {
		// if it is already loaded, just confirm it
		if (class_exists('QS\APIs\gplus') && class_exists('QS\APIs\google')) return true;

		// try to include the sdk
		$plugin_path = apply_filters('qs-sa-pro/plugin/path', '', false);
		$sdk_path = $plugin_path.'libs'.h\ds().'qs-apis'.h\ds();
		if (file_exists($sdk_path.'gplus.php')) require_once $sdk_path.'gplus.php';

		// test required class existence again
		return class_exists('QS\APIs\gplus') && class_exists('QS\APIs\google');
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qssa_gplus::pre_init();
}
endif;
