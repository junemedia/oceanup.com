<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

if (!class_exists('qssa_twitter')):
// twitter module. creates twitter accounts (for this plugin) and interacts with the fb graph api
class qssa_twitter extends qssa__base_module {
	// static settings
	protected static $module_name = 'Twitter'; // static version of module name

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
	protected $display_name = 'Twitter'; // same as static module name above

	protected $settings_slug = '_qssa_twitter'; // setting slug prefix. all stored account settings wp_options keys will begin with this
	protected $settings = array(); // holder for this instance settings
	protected $defaults = array( // default settings
		'active' => true,
		'desc' => '',
		'consumer_key' => '',
		'consumer_secret' => '',
		'user_access_token' => '',
		'user_access_token_secret' => '',
		'post_url' => '',
		'attach_image' => 0,
		'auto_post_for' => array('post'),
		'needs_reverify' => true,
	);
	protected $twapi = null;

	// draw basic settings panel
	public function settings_basic() {
		?>
			<div class="twitter-settings twitter-settings-basic">
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
					<label for="<?php echo $this->_id('post_url') ?>">Twitter URL</label>
					<input type="text" name="<?php echo $this->_name('post_url') ?>"
							id="<?php echo $this->_id('post_url') ?>"
							value="<?php echo esc_attr($this->settings['post_url']) ?>"
							class="widefat" />
					<span class="helper">
						The Twitter url, on which you want a new autoposting to show.
						This should be your profile url.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('consumer_key') ?>">Consumer Key</label>
					<input type="text" name="<?php echo $this->_name('consumer_key') ?>"
							id="<?php echo $this->_id('consumer_key') ?>"
							value="<?php echo esc_attr($this->settings['consumer_key']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://dev.twitter.com/apps" target="_blank" title="Twitter Developers App Dashboard">Twitter Developer - App Dashboard</a>.
						Either create a new app for this, or use one you already have setup for this url.
						On the App specific Dashboard page, copy the field labeled 'Consumer Key', and paste it here.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('consumer_secret') ?>">Consumer Secret</label>
					<input type="text" name="<?php echo $this->_name('consumer_secret') ?>"
							id="<?php echo $this->_id('consumer_secret') ?>"
							value="<?php echo esc_attr($this->settings['consumer_secret']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://dev.twitter.com/apps" target="_blank" title="Twitter Developers App Dashboard">Twitter Developer - App Dashboard</a>.
						Either create a new app for this, or use one you already have setup for this url.
						On the App specific Dashboard page, copy the field labeled 'Consumer Secret', and paste it here.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('user_access_token') ?>">Your Access Token</label>
					<input type="text" name="<?php echo $this->_name('user_access_token') ?>"
							id="<?php echo $this->_id('user_access_token') ?>"
							value="<?php echo esc_attr($this->settings['user_access_token']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://dev.twitter.com/apps" target="_blank" title="Twitter Developers App Dashboard">Twitter Developer - App Dashboard</a>.
						Either create a new app for this, or use one you already have setup for this url.
						Once the app is created, on the app 'Details' tab of the App specific Dashboard, at the bottom, there is a button to 'Create my access token'.
						For a more detailed explanation of this process, visit <a target="_blank" href="https://dev.twitter.com/docs/auth/tokens-devtwittercom">Tokens from dev.twitter.com</a>
						Click that button to generate you access token.
						After the access token is generated copy the field labeled 'Access token', and paste it here.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('user_access_token_secret') ?>">Your Access Token Secret</label>
					<input type="text" name="<?php echo $this->_name('user_access_token_secret') ?>"
							id="<?php echo $this->_id('user_access_token_secret') ?>"
							value="<?php echo esc_attr($this->settings['user_access_token_secret']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://dev.twitter.com/apps" target="_blank" title="Twitter Developers App Dashboard">Twitter Developer - App Dashboard</a>.
						Either create a new app for this, or use one you already have setup for this url.
						Once the app is created, on the app 'Details' tab of the App specific Dashboard, at the bottom, there is a button to 'Create my access token'.
						For a more detailed explanation of this process, visit <a target="_blank" href="https://dev.twitter.com/docs/auth/tokens-devtwittercom">Tokens from dev.twitter.com</a>
						Click that button to generate you access token.
						After the access token is generated copy the field labeled 'Access token secret', and paste it here.
					</span>
				</div>
			</div>
		<?php
	}

	// draw advanced settings panel
	public function settings_advanced() {
		$attach_image = $this->settings['attach_image'];

		$auto_post_for = $this->settings['auto_post_for'];
		$auto_post_for = is_array($auto_post_for) ? $auto_post_for : array();
		?>
			<div class="twitter-settings twitter-settings-advanced">
				<div class="qs-sa-field">
					<span class="cb-wrap">
						<input type="hidden" name="<?php echo $this->_name('attach_image') ?>" id="<?php echo $this->_id('attach_image') ?>" value="0" />
						<input type="checkbox" name="<?php echo $this->_name('attach_image') ?>"
								id="<?php echo $this->_id('attach_image') ?>"
								value="1" <?php checked(true, (bool)$this->settings['attach_image']) ?> />
						<span class="cb-label">Attach an Image to each Tweet</span>
					</span>
					<span class="helper">
						Do you want to 'attach an image' to your tweets?
						If so, check this, and we will use your featured image.
					</span>
				</div>

				<?php $this->_draw_auto_post_for() ?>
				<?php $this->_draw_message_format() ?>
			</div>
		<?php
	}

	// draw short settings panel
	public function short_settings() {
	}

	public function can_reverify() {
		$s = wp_parse_args($settings, $this->settings);
		return isset($s['consumer_key'], $s['consumer_secret'], $s['user_access_token'], $s['user_access_token_secret'])
			&& !empty($s['consumer_key'])
			&& !empty($s['consumer_secret'])
			&& !empty($s['user_access_token'])
			&& !empty($s['user_access_token_secret']) ;
	}

	protected function _maybe_needs_verify($settings) {
		if (!self::_load_sdk_class()) {
			$settings['notify'] = 'Could not load the Twitter SDK, which is needed to post to Twitter.';
			do_action('qs-sa/autopost/log', 'log', 0, 'Failed to load the twitter SDK. You may need to reinstall the plugin.', 'bad');
			return $settings;
		}

		$tw = new lou_twitter_oauth(array(
			'consumer_key' => $settings['consumer_key'],
			'consumer_secret' => $settings['consumer_secret'],
			'token' => $settings['user_access_token'],
			'secret' => $settings['user_access_token_secret'],
			'user_agent' => 'QS Social Automator (via tmhOAuth) (v:0.1)',
		));

		$access_token = $msg = '';

		$resp = $tw->api('account/verify_credentials');
		
		switch ($resp) {
			case '200':
				$resp = $tw->api('help/configuration');

				switch ($resp) {
					case '200':
						$config = @json_decode($tw->response['response'], true);
						if (is_array($config)) $settings['config'] = $config;
						else $msg = 'There was a problem with the config response from twitter for ['.$this->name().']. ';
					break;

					default:
						$msg = 'Could not load the twitter configuration for ['.$this->name().']. ';
					break;
				}
			break;

			case '401':
				$msg = 'The supplied credentials were not able to be validated for ['.$this->name().']. ';
			break;

			default:
				$msg = 'An error occurred while trying to use the Twitter API for ['.$this->name().']. [ '.$tw->response['errno'].' : '.$tw->response['error'].' ]';
			break;
		}

		$dashboard_url = 'https://dev.twitter.com/apps';
		$url = site_url(add_query_arg(array('rv' => $this->instance_id), remove_query_arg(array('updated', 'code', 'state'))));
		$settings['notify'] = $msg
			? $msg.' Try to re-copy <strong>all</strong> the credentials from your <a href="'.esc_attr($dashboard_url).'">App Dashboard</a>.'
				.'Then once you have verfiied they match, try to <a href="'.esc_attr($url).'">reverify</a> this plugin\'s access.'
			: '';
		if ($msg) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, $settings['notify'], 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failed to authenticate ['.$this->name().']: '."\n".$this->_debug_dump($tw), 'bad');
		} else {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Successfully verified that we can use Twitter to post to your account.', 'good');
		}
		$settings['needs_reverify'] = !!$setting['notify'];

		return $settings;
	}

	protected function _can_upload_image($file, $post, $failure_msg) {
		$max_uploads = $this->settings['config']['max_media_per_upload'];
		if (!$max_uploads) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'The Twitter account is not setup to allow uploads at this time. '.$failure_msg, 'bad');
			return false;
		}

		if (!$file || !is_readable($file)) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Cannot find an image to upload for this post. ['.$file.'] '.$failure_msg, 'bad');
			return false;
		}

		$size = filesize($file);
		$max_size = $this->settings['config']['photo_size_limit'];
		if ($size > $max_size) {
			$max_disp = number_format($max_size/1024, 2).'KB';
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'The supplied image (usually the featured image) was too big to upload to Twitter (max:'.$max_disp.'). '.$failure_msg, 'bad');
			return false;
		}

		return true;
	}

	protected function _get_image_filename($image_id) {
		$file = '';
		$u = wp_upload_dir();
		$meta = wp_get_attachment_metadata($image_id);
		$full = $meta && isset($meta['file']) ? $meta['file'] : '';

		if ($full && is_array($u) && isset($u['basedir'])) {
			$lives_in = trailingslashit(dirname($full));
			$basedir = trailingslashit($u['basedir']).$lives_in;
			$full = basename($full);

			$k = isset($meta['width'], $meta['height']) ? $meta['width'] * $meta['height'] : '0-error';
			$potential_filenames = array($k.'' => $full);
			if (isset($meta['sizes']) && !empty($meta['sizes'])) foreach ($meta['sizes'] as $size_slug => $data) {
				$k = $data['width'] * $data['height'];
				$potential_filenames[$k.''] = $data['file'];
			}

			krsort($potential_filenames, SORT_NUMERIC);

			foreach ($potential_filenames as $filename) {
				$fullname = $basedir.$filename;
				$file_size = @filesize($fullname);
				if ($file_size && $file_size <= $this->settings['config']['photo_size_limit']) {
					$file = $fullname;
					break;
				}
			}
		}

		return $file;
	}

	public function autopost($post) {
		$post = get_post($post);

		if (!$this->_load_sdk()) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Did not autopost, because we could not find the Twitter SDK.', 'bad');
			return false;
		}

		$type = $this->settings['attach_image'] ? 'statuses/update_with_media' : 'statuses/update';
		$msg = $this->_get_message($post);
		$attach = $this->settings['attach_image'];

		if ($attach) {
			$image_id = $this->_get_thumbnail_id($post);
			$file = $this->_get_image_filename($image_id);
			if (!$this->_can_upload_image($file, $post, 'Changing to status update only (with no attachment).')) $attach = false;
		}

		$data = array('status' => $msg); //.rand(0, PHP_INT_MAX));
		$headers = array();
		if (!$attach) {
			$ep = 'statuses/update';
		} else {
			$data['media'][] = '@'.$file;
			$ep = 'statuses/update_with_media';
		}
		
		$old_id = get_post_meta($post->ID, $this->_settings_slug(), true);
		$this->twapi->api('statuses/destroy/:id:', array('id' => $old_id));

		$resp = $this->twapi->api($ep, $data, $headers);

		switch ($resp) {
			case '200':
				$json = @json_decode($this->twapi->response['response']);
				if (is_object($json)) {
					update_post_meta($post->ID, $this->_settings_slug(), $json->id);
					do_action('qs-sa/autopost/log', 'log', $post->ID, 'Successfully posted the update to your Twitter account ['.$this->name().'] '
							.'at '.sprintf('<a href="%s" target="_blank">this url</a>.', $this->posted_url($post)).' Any old urls will no longer work.', 'good');
					return true;
				} else {
					do_action('qs-sa/autopost/log', 'log', $post->ID, 'When we tried to post to your Twitter account ['.$this->name().'], we got no response back, which is weird.', 'bad');
					do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failed to post to Twitter: '."\n".$this->_debug_dump($this->twapi), 'bad');
				}
			break;
			
			default:
				do_action('qs-sa/autopost/log', 'log', $post->ID, 'We could not post the update to your Twitter account ['.$this->name().'].', 'bad');
				do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failed to post to Twitter: '."\n".$this->_debug_dump($this->twapi), 'bad');
			break;
		}

		return false;
	}

	public function posted_url($post) {
		$post = get_post($post);
		$id = get_post_meta($post->ID, $this->_settings_slug(), true);
		return trailingslashit($this->settings['post_url']).'statuses/'.$id;
	}

	public function reverify() {
		$this->_load_sdk();

		$popup = isset($_GET['popup']) ? $_GET['popup'] : 0;
		$this->_save('', true);

		$msg = $this->settings['notify'] ? 'failed-auth' : 'auth-success';

		if ($popup) {
			echo '<html><head><script type="text/javascript">if(window.opener&&window.opener._qssa_msg)window.opener._qssa_msg("'.esc_attr($msg).'");'
					.'window.close();</script></head><body><p>Processing...</p></body></html>';
			exit;
		}

		return (bool)$this->settings['notify'];
	}

	protected function _load_sdk() {
		if (self::_load_sdk_class()) {
			if (!isset($this->twapi)) {
				$this->twapi = new lou_twitter_oauth(array(
					'consumer_key' => $this->settings['consumer_key'],
					'consumer_secret' => $this->settings['consumer_secret'],
					'token' => $this->settings['user_access_token'],
					'secret' => $this->settings['user_access_token_secret'],
					'user_agent' => 'QS Social Automator (via tmhOAuth) (v:0.1)',
				));
			}
			return true;
		}

		return false;
	}

	// load and confirm the existence of the facebook sdk
	protected static function _load_sdk_class() {
		// if it is already loaded, just confirm it
		if (class_exists('tmhOAuth') && class_exists('lou_twitter_oauth')) return true;

		// try to include the sdk
		$plugin_path = apply_filters('qs-sa/plugin/path', '', false);
		$sdk_path = $plugin_path.'libs/lou-twitter/';
		if (file_exists($sdk_path.'twitter.php')) require_once $sdk_path.'twitter.php';

		// test required class existence again
		return class_exists('tmhOAuth') && class_exists('lou_twitter_oauth');
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qssa_twitter::pre_init();
}
endif;
