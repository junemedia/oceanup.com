<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

if (!class_exists('qssa_facebook')):
// facebook module. creates facebook accounts (for this plugin) and interacts with the fb graph api
class qssa_facebook extends qssa__base_module {
	// static settings
	protected static $module_name = 'Facebook'; // static version of module name
	protected static $posting_types = array( // array of valid posting_types
		'text-post' => 'Text Only',
		'image-post' => 'Text + Image',
		'text-post-with-link' => 'Text + "attached" link',
	);
	protected static $image_types = array( // array of valid image_types
		'timeline' => 'Timeline',
		'app-album' => 'App Album',
	);
	protected static $link_types = array( // array of valid link_types
		'share' => 'Share Link to Post',
		'attach' => 'Attach Post',
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
	protected $display_name = 'Facebook'; // same as static module name above

	protected $settings_slug = '_qssa_facebook'; // setting slug prefix. all stored account settings wp_options keys will begin with this
	protected $settings = array(); // holder for this instance settings
	protected $defaults = array( // default settings
		'active' => true,
		'desc' => '',
		'app_id' => '',
		'app_secret' => '',
		'user_id' => '',
		'access_token' => '',
		'post_url' => '',
		'posting_type' => 'text-post',
		'image_type' => 'timeline',
		'link_type' => 'share',
		'auto_post_for' => array('post'),
		'needs_reverify' => true,
	);
	protected $fbapi = null;

	// draw basic settings panel
	public function settings_basic() {
		?>
			<div class="facebook-settings facebook-settings-basic">
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
					<label for="<?php echo $this->_id('post_url') ?>">Post URL</label>
					<input type="text" name="<?php echo $this->_name('post_url') ?>"
							id="<?php echo $this->_id('post_url') ?>"
							value="<?php echo esc_attr($this->settings['post_url']) ?>"
							class="widefat" />
					<span class="helper">
						The facebook url, on which you want a new autoposting to show.
						This could be your profile, a page you manage, or a group you can post to.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('app_id') ?>">App ID</label>
					<input type="text" name="<?php echo $this->_name('app_id') ?>"
							id="<?php echo $this->_id('app_id') ?>"
							value="<?php echo esc_attr($this->settings['app_id']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://developers.facebook.com/" target="_blank" title="FB Developers Dashboard">Facebook Developer Dashboard</a>,
						under 'Apps'.
						Either create a new app for this, or use one you already have setup for this url.
						On the App specific Dashboard page, copy the field labeled 'App ID', and paste it here.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('app_secret') ?>">App Secret</label>
					<input type="text" name="<?php echo $this->_name('app_secret') ?>"
							id="<?php echo $this->_id('app_secret') ?>"
							value="<?php echo esc_attr($this->settings['app_secret']) ?>"
							class="widefat" />
					<span class="helper">
						You get this from your
						<a href="https://developers.facebook.com/" target="_blank" title="FB Developers Dashboard">Facebook Developer Dashboard</a>,
						under 'Apps'.
						Either create a new app for this, or use one you already have setup for this url.
						On the App specific Dashboard page, copy the field labeled 'App Secret', and paste it here.
					</span>
				</div>
			</div>
		<?php
	}

	// draw advanced settings panel
	public function settings_advanced() {
		$posting_types = self::$posting_types;
		$image_types = self::$image_types;
		$link_types = self::$link_types;

		$auto_post_for = $this->settings['auto_post_for'];
		$auto_post_for = is_array($auto_post_for) ? $auto_post_for : array();
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
						Designates the type of post to create on your target Facebook url.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('image_type') ?>">Image Type</label>
					<?php foreach ($image_types as $slug => $label): ?>
						<span class="cb-wrap">
							<input type="radio" name="<?php echo $this->_name('image_type') ?>"
									id="<?php echo $this->_id('image_type') ?>"
									value="<?php echo esc_attr($slug) ?>"
									<?php checked($slug, $this->settings['image_type']) ?> />
							<span class="cb-label"><?php echo $label ?></span>
						</span>
					<?php endforeach; ?>
					<span class="helper">
						When using the 'Text + Image' posting type, this designates where the image lives.
					</span>
				</div>

				<div class="qs-sa-field">
					<label for="<?php echo $this->_id('link_type') ?>">Link Type</label>
					<?php foreach ($link_types as $slug => $label): ?>
						<span class="cb-wrap">
							<input type="radio" name="<?php echo $this->_name('link_type') ?>"
									id="<?php echo $this->_id('link_type') ?>"
									value="<?php echo esc_attr($slug) ?>"
									<?php checked($slug, $this->settings['link_type']) ?> />
							<span class="cb-label"><?php echo $label ?></span>
						</span>
					<?php endforeach; ?>
					<span class="helper">
						When using the 'Text + "attached" link' posting type, this designates what type of link to make.
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

	public function can_reverify($settings='') {
		$s = wp_parse_args($settings, $this->settings);
		return isset($s['app_id'], $s['app_secret'], $s['post_url'])
			&& !empty($s['app_id'])
			&& !empty($s['app_secret'])
			&& !empty($s['post_url']);
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
				'name' => 'reload-fb',
				'type' => 'refresh',
			);
		}

		return $actions;
	}

	protected function _maybe_needs_verify($settings) {
		if (!self::_load_sdk_class()) {
			$settings['notify'] = 'Could not load the FB SDK, which is needed to post to Facebook.';
			do_action('qs-sa/autopost/log', 'log', 0, 'Failed to load the Facebook SDK. You may need to reinstall the plugin.', 'bad');
			return $settings;
		}

		$fb = new Facebook(array(
			'appId' => $settings['app_id'],
			'secret' => $settings['app_secret'],
			'allowSignedRequest' => true,
		));
		$fb_user_id = $fb->getUser();

		$user_id = $msg = $skip_extra_msg = $access_token = '';
		$needs_reverify = 0;
		$scope = array();

		if (!$fb_user_id) {
			$scope[] = 'publish_actions';
			$msg = 'We could not authenitcate your account via the FB API.';
			$needs_reverify = 2;
		} else {
			list($msg, $user_id, $access_token, $scope, $skip_extra_msg, $needs_reverify) = $this->_user_has_access($fb, $settings);

			if ($user_id) $settings['albums'] = $this->_get_albums($fb, $user_id);
			else $settings['albums'] = array();

			$settings['album_id'] = '';
			if ($settings['albums'] && $settings['posting_type'] == 'image-post' && $settings['image_type'] == 'timeline')
				foreach ($settings['albums'] as $album) if ($album['type'] == 'wall') {
					$settings['album_id'] = $album['id'];
					break;
				}
		}

		//die(__log($settings, $fb_user_id, $msg, $user_id, $access_token, $scope, $skip_extra_msg, $needs_reverify));
		$url = apply_filters('qs-sa/url/reverify', '', $this->instance_id);
		if ($this->can_reverify($settings)) {
			$extra = ' You probably need to <a rel="reverify" href="'.esc_attr($url).'">Grant Access</a> to this plugin, before we can post. ['.var_export($fb_user_id, true).'] ['.microtime(true).']';
		} else {
			$extra = ' You need to fill out all the information, before we can even attempt to verify the account access.';
			$needs_reverify = 0;
		}

		$settings['needs_reverify'] = $needs_reverify;
		$settings['scope_needed'] = implode(',', $scope);
		$settings['access_token'] = $access_token;
		$settings['user_id'] = $user_id;
		$settings['notify'] = $msg
			? ( $skip_extra_msg ? $msg : $msg.$extra )
			: '';

		return $settings;
	}

	protected function _get_albums($fb, $user_id) {
		$all_albums = array();
		try {
			$albums = $fb->api('/'.$user_id.'/albums');
			if (isset($albums['data']))
				foreach ($albums['data'] as $album)
					$all_albums[$album['id']] = array(
						'id' => $album['id'],
						'name' => $album['name'],
						'type' => $album['type'],
					);
		} catch (Exception $e) {
			do_action('qs-sa/autopost/log', 'details', 0, 'Failed to Re-Verify: '.$this->_debug_exception($e), 'bad');
		}

		return $all_albums;
	}

	protected function _access_token($settings=false) {
		$settings = $settings ? $settings : $this->settings;
		return $settings['app_id'].'|'.$settings['app_secret'];
	}

	protected function _has_perms($need, $perms) {
		$has = true;
		foreach ($need as $perm) {
			$found = false;
			foreach ($perms['data'] as $granted) if ($granted['permission'] == $perm && $granted['status'] == 'granted') {
				$found = true;
				break;
			}
			if (!$found) {
				$has = false;
				break;
			}
		}
		return $has;
	}

	protected function _user_has_access($fb, $settings) {
		$target_username = $this->_get_username_from_url($settings['post_url']);

		$msg = $user_id = $access_token = $skip_msg = $reverify = '';
		$scope = array();

		try {
			$resp = $fb->api('/me');
			$perms = $fb->api('/me/permissions');

			if ($resp['id'] != $target_username && $resp['username'] != $target_username) {
				$scope[] = 'publish_actions';
				$scope[] = 'manage_pages';
				if ($this->_has_perms($scope, $perms)) {
					$accts = $fb->api('/me/accounts');
					$match = false;
					foreach ($accts['data'] as $ind => $acct) {
						$match = $target_username == $acct['id'];
						if (!$match) {
							$page_info = $fb->api('/'.$acct['id']);
							$match = isset($page_info, $page_info['username']) && $page_info['username'] == $target_username;
							if ($match) $acct = array_merge($acct, $page_info);
							$accts['data'][$ind] = $acct;
						}
						if ($match) {
							$match = $acct;
							if (!isset($match['username'])) $match['username'] = $match['id'];
							break;
						}
					}
					if ($match) {
						$can_create = array_search('CREATE_CONTENT', $match['perms']);
						if ($can_create) {
							$user_id = $match['username'];
							$access_token = $match['access_token'];
							do_action('qs-sa/autopost/log', 'details', 0, 'Successfully, fully, accurately verified that we have access to manage the Facebook Page the user is requesting.', 'good');
							$scope = array();
						} else {
							$msg = 'Your facebook App appears to be linked to the Facebook Page you are trying to update, but the App does not have permission to manage the Facebook Page.'
									.' Check your <a href="'.$acct['link'].'/settings?tab=admin_roles'.'">Page Admin Roles</a>, or have the page admin give the App access.';
							$skip_msg = false;
							do_action('qs-sa/autopost/log', 'details', 0, 'The authorized Facebook account has access to the page, but they do not have the ability to "CREATE_CONTENT" on that page. '
									.'This can only be given by the page administrator. '.$this->_debug_dump(array('me' => $resp, 'perms' => $match['perms'], 'need' => 'CREATE_CONTENT'), true), 'bad');
						}
					} else {
						$msg = 'It does not appear that the App has access to the page you are trying to post to.'
								.' Check your <a href="'.$settings['post_url'].'/settings?tab=admin_roles'.'">Page Admin Roles</a>, or have the page admin give the App access.';
						$skip_msg = true;
						do_action('qs-sa/autopost/log', 'details', 0, 'We have access to manage_pages and publish_stream, but apparently the authorize Facebook Account does not have management rights '
								.'for the page they are trying to post to. Ergo, the page is not on the account\'s list of pages it can manage. '
								.$this->_debug_dump(array('me' => $resp, 'accts' => $accts, 'need' => $target_username), true), 'bad');
					}
				} else {
					$reverify = true;
					$msg = 'We did gain access to manage your Facebook Profile. However the Post URL you specified appears to be a Facebook Page, which we do not have access to manage yet.';
					do_action('qs-sa/autopost/log', 'details', 0, 'The authorized Facebook Account has granted us access to manage the Profile. However, the url specified appears to be a Facebook Page. '
							.'We do not yet have access to manage that. '.$this->_debug_dump(array('me' => $resp, 'perms' => $perms, 'need' => $scope), true), 'bad');
				}
			} else {
				$scope[] = 'publish_actions';
				if ($this->_has_perms($scope, $perms)) {
					$user_id = $resp['usernmae'];
					$access_token = $fb->getAccessToken();
					do_action('qs-sa/autopost/log', 'details', 0, 'Successfully, fully, accurately verified we can post to the Profile the user requested.', 'good');
				} else {
					$reverify = true;
					$msg = 'The plugin does not have permission to your Facebook account.';
					do_action('qs-sa/autopost/log', 'details', 0, 'The Facebook account, for the Facebook AppId, has not authorized this plugin to post to it\'s pages. '
							.$this->_debug_dump(array('me' => $resp, 'perms' => $perms, 'need' => $scope), true), 'bad');
				}
			}
		} catch (Exception $e) {
			do_action('qs-sa/autopost/log', 'details', 0, 'Failed to Re-Verify: '.$this->_debug_exception($e), 'bad');
			$reverify = 5;
			$msg = 'We encountered a problem when trying to verify the Facebook account settings. ';
		}

		return array($msg, $user_id, $access_token, $scope, $skip_msg, $reverify);
	}

	protected function _get_username_from_url($url=false) {
		$url = $url ? $url : $this->settings['post_url'];
		$parsed = parse_url($url);
		if ($parsed === false) return '';

		$path_parts = explode('/', trim(isset($parsed['path']) ? $parsed['path'] : '', '/'));
		return array_shift($path_parts);
	}

	protected function _get_post_og($post) {
		$og = get_post_meta($post->ID, '_fb_og_resp', true);
		if (empty($og)) {
			$req = array('id' => get_permalink($post->ID), 'scrape' => 'true');
			$resp = wp_remote_post('http://graph.facebook.com', array('body' => $req));
			if (!empty($resp) && !is_wp_error($resp) && ($resp = @json_decode($resp, true))) {
				$og = $resp;
				update_post_meta($post->ID, '_fb_og_resp', $og);
			}
		}
		return $og && is_array($og) ? $og : array();
	}

	public function autopost($post, $use_settings='', $force=false) {
		$post = get_post($post);
		if ($this->already_autoposting($post, $force)) return;

		if (!$this->_load_sdk()) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Did not autopost, because we could not find the Facebook SDK.', 'bad');
			return false;
		}

		$success = false;
		$process = true;
		$username = $this->settings['user_id'];
		if (empty($username)) {
			do_action('qs-sa/autopost/log', 'log', $post->ID, 'Failed to figure out the feed to post to. Perhaps the "Post URL" setting is malformed, or the wrong url?', 'bad');
			do_action('qs-sa/autopost/log', 'details', $post->ID, 'Failure Debug: '.$this->_debug_dump(array('settings' => $this->settings, 'post' => $post), true), 'bad');
			$process = false;
		}

	// An unexpected error has occurred. Please retry your request later.
		if ($process) {
			// determine the endpoint url to send this post to. either 'feed' for status updates or 'photos' for images
			$type = $this->settings['posting_type'] == 'image-post' ? 'photos' : 'feed';

			// facebook api path. used by the sdk to send the request to the proper endpoint of the rest api
			$path = '/'.$username.'/'.$type;
			// method to use. writing data is usually a POST action
			$method = 'POST';
			// the information that describes the action to take, to the facebook api
			switch ($type) {
				default:
				case 'feed':
					$og = $this->_get_post_og($post);
					// describe the post to attach
					$args = array(
						'name' => apply_filters('the_title', $post->post_title),
						'link' => isset($og['id']) ? $og['id'] : get_permalink($post->ID),
						'caption' => isset($og['site_name']) ? $og['site_name'] : get_bloginfo('name'),
						'description' => isset($og['description']) ? $og['description'] : $this->_get_content($post),
					);

					// you can only have one action .... apparently
					if ($this->settings['link_type'] == 'attach')
						$args['actions'] = array( array('name' => $args['caption'], 'link' => site_url()) );
				break;

				case 'photos':
					// describe the image
					$args = array(
						'url' => $this->_get_thumbnail_url($post, $use_settings),
					);

					// if we are posting photos, maybe post to the selected album
					$username = isset($this->settings['album_id']) ? $this->settings['album_id'] : $username;

					// fail on empty image url, since it is required
					if (empty($args['url'])) {
						$process = false;
						do_action('qs-sa/autopost/log', 'details', $post->ID, 'Could not post to account ['.$this->name().'] because we require at least one post image, in order to do a image post.', 'bad');
					}
				break;
			}

			if ($process) {
				// only add a message if the message has a value 
				$msg = $this->_get_message($post);
				if ($msg) $args['message'] = $msg;

				// try to send the post to facebook
				try{

					try {
						// delete any previously posted version of this post, that we have on record
						$old_id = get_post_meta($post->ID, $this->_settings_slug(), true);
						if ($old_id) $this->fbapi->api('/'.$old_id, 'delete');
					} catch(Exception $e) {
						do_action('qs-sa/autopost/log', 'details', $post->ID, 'Could not remove old post. This is what Facebook said: '.$this->_debug_exception($e), 'bad');
					}

					// create a new published post on the target url
					$resp = $this->fbapi->api($path, $method, $args);
					update_post_meta($post->ID, $this->_settings_slug(), $resp['id']);

					// log our success
					do_action('qs-sa/autopost/log', 'log', $post->ID, 'Successfully '.($old_id ? 're' : '').'posted ['.apply_filters('the_title', $post->post_title).'] to account ['.$this->name().'] '
							.'published at '.sprintf('<a href="%s" target="_blank">this url</a>.', $this->posted_url($post)).' Any old urls will no longer work.', 'good');

					return true;
				} catch(Exception $e) {
					do_action('qs-sa/autopost/log', 'details', $post->ID, 'debug: '
							.$this->_debug_dump(array('path' => $path, 'method' => $method, 'args' => $args, 'json' => @json_encode($args), 'settings' => $this->settings), true), 'bad');
					throw $e;
				}
			}
		}

		return false;
	}

	public function posted_url($post) {
		$post = get_post($post);
		$id = get_post_meta($post->ID, $this->_settings_slug(), true);
		return 'https://www.facebook.com/'.$id;
	}

	public function reverify() {
		$this->_load_sdk();

		$code = isset($_GET['code']) ? $_GET['code'] : false;
		$state = isset($_GET['state']) ? $_GET['state'] : false;
		$popup = isset($_GET['popup']) ? $_GET['popup'] : 0;
		$requested = isset($_GET['requested']) ? $_GET['requested'] : '';
		$msg = 'auth-success';

		if (empty($code) && empty($state)) {
			$requesting = $this->settings['scope_needed'];
			$rurl = apply_filters('qs-sa/url/reverify', '', $this->instance_id, array('popup' => $popup, 'requested' => $requesting));
			if (!empty($rurl)) {
				$args = array('scope' => $this->settings['scope_needed'], 'redirect_uri' => $rurl);
				if ($popup) $args['display'] = 'popup';
				$url = $this->fbapi->getLoginUrl($args);
				wp_redirect($url);
				exit;
			}
		} else {
			$settings = $this->_maybe_needs_verify($this->settings);
			$requesting = $settings['scope_needed'];
			if ($requesting && $requesting == $requested) {
				$settings['notify'] = 'You must click "Okay" on the Facebook popup, if you want to use this plugin to autopost. Please '
					.'<a rel="reverify" href="'.esc_attr(apply_filters('qs-sa/url/reverify', '', $this->instance_id)).'">try to reverify</a> again, and click "Okay" to allow this plugin access.'
					.'['.$requesting.' - '.$requested.']';
				$this->_save($settings, false, true);
				$msg = 'failed-auth';
			} else if (!isset($_GET['already']) && !empty($settings['notify'])) {
				$rurl = apply_filters('qs-sa/url/reverify', '', $this->instance_id, array('already' => 1, 'popup' => $popup));
				if (!empty($rurl)) {
					$args = array('scope' => $settings['scope_needed'], 'redirect_uri' => $rurl);
					if ($popup) $args['display'] = 'popup';
					$url = $this->fbapi->getLoginUrl($args);
					wp_redirect($url);
					exit;
				}
			} else {
				$this->_save('', true);
			}
		}

		if ($popup) {
			echo '<html><head><script type="text/javascript">if(window.opener&&window.opener._qssa_msg)window.opener._qssa_msg("'.esc_attr($msg).'");'
					.'window.close();</script></head><body><p>Processing...</p></body></html>';
			exit;
		}

		return (bool)$this->settings['notify'];
	}

	protected function _load_sdk() {
		if (self::_load_sdk_class()) {
			if (!isset($this->fbapi)) {
				$this->fbapi = new Facebook(array(
					'appId' => $this->settings['app_id'],
					'secret' => $this->settings['app_secret'],
					'allowSignedRequest' => true,
				));
				$this->fbapi->setAccessToken($this->settings['access_token']);
			}
			return true;
		}

		return false;
	}

	// load and confirm the existence of the facebook sdk
	protected static function _load_sdk_class() {
		// if it is already loaded, just confirm it
		if (class_exists('Facebook') && class_exists('BaseFacebook')) return true;

		// try to include the sdk
		$plugin_path = apply_filters('qs-sa/plugin/path', '', false);
		$sdk_path = $plugin_path.'libs/fb/';
		if (file_exists($sdk_path.'facebook.php')) require_once $sdk_path.'facebook.php';

		// test required class existence again
		return class_exists('Facebook') && class_exists('BaseFacebook');
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qssa_facebook::pre_init();
}
endif;
