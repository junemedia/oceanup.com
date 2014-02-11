<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

if (!class_exists('qssa__base_module')):
// base module, used by all other modules
abstract class qssa__base_module {
	// universal static settings
	protected static $required_defaults = array(
		'desc' => '',
		'active' => true,
		'auto_post_for' => array('post'),
		'notify' => '',
		'format' => 'Check this out - %TITLE% !',
		'needs_reverify' => true,
	);

	// basic module information
	protected $_type = __CLASS__; // module class
	protected $slug = ''; // module slug
	protected $display_name = ''; // module display name
	protected $instance_id = ''; // current module instance_id

	protected $messages = array(); // holder for session messages

	protected $new = false; // marks this instance as just being created. lasts only during creation
	protected $settings_slug = ''; // base settings slug for this type of module
	protected $settings = array(); // holder for current instance settings
	protected $defaults = array(); // holder for module default settings

	protected $why_blocked = array();

	// generic module constructor
	public function __construct($instance_id=false) {
		// determine if this is a request to create a new instance or not
		$initial = !$instance_id;

		// setup the basic information about this instance
		$this->slug = sanitize_title_with_dashes($this->display_name);
		// set/create the instance_id
		$this->instance_id = $instance_id ? $instance_id : md5($this->slug.'-'.time().'-'.rand(0, PHP_INT_MAX));
		// register this account's basic hooks
		$this->register();

		// improve defaults with required defaults
		$this->defaults = wp_parse_args($this->defaults, self::$required_defaults);

		// if we just created a new instance, then set it up
		if ($initial) $this->_init_new();
		// load the settings for this instance
		$this->_load_settings();
	}

	// register the hooks needed for this instance
	public function register() {
		add_action('qs-sa/settings/save', array(&$this, 'save'), 10, 2);
	}

	// unregister the hooks needed for this instance
	public function unregister() {
		remove_action('qs-sa/settings/save', array(&$this, 'save'), 10);
	}

	// functions that need defining in the specific module
	abstract public function settings_basic();
	abstract public function settings_advanced();
	abstract public function short_settings();
	abstract public function autopost($post);
	abstract public function posted_url($post);
	abstract protected function _maybe_needs_verify($settings);

	// generic function to return the instance information
	public function instance_info() { return array('type' => $this->slug, 'instance_id' => $this->instance_id); }

	// get the formal name of this instance
	public function name($context='html') {
		if ($context == 'raw') return $this->display_name.' - '.$this->settings['desc'];
		return '<span class="qssa-account-name '.$this->slug.'"><span class="icon"></span><span class="type">'.$this->display_name.'</span> - <span class="desc">'.$this->settings['desc'].'</span></span>';
	}

	// determine whether this post needs to be autoposted by this account
	public function can_autopost($post) {
		$post = get_post($post);
		return $this->_can_autopost($post, in_array($post->post_type, $this->settings['auto_post_for']) && !$this->is_autopost_blocked($post));
	}
	public function _can_autopost($post, $current) { return $current; }

	public function get_needed_actions() { return array(); }

	public function msgs() {
		if (!empty($this->messages)) {
			?>
				<div class="messages">
					<?php foreach ($this->messages as $msg): ?>
						<div class="message"><?php echo force_balance_tags($msg) ?></div>
					<?php endforeach; ?>
				</div>
			<?php
		}
	}

	// get a list of post_types that this module is setup for
	public function post_types() { return $this->settings['auto_post_for']; }

	// determind if this post has previously been posted by this module
	public function is_autopost_blocked($post) {
		if (!isset($this->why_blocked[''.$post->ID]) && ($id = get_post_meta($post->ID, $this->_settings_slug(), true)) !== '')
			$this->why_blocked[''.$post->ID] = apply_filters(
				'qs-sa/autopost/blocked',
				array('msg' => sprintf('You previously submitted the post to this account, viewable at <a href="%s" target="_blank">this url</a>.', $this->posted_url($post)), 'code' => 1),
				$post,
				$this
			);
		if (!isset($this->why_blocked[''.$post->ID]) && !empty($this->settings['notify']))
			$this->why_blocked[''.$post->ID] = apply_filters(
				'qs-sa/autopost/blocked',
				array('msg' => '<span class="bad">You currently have errors on the verification of this account that need to be resolved.</span>', 'code' => 2),
				$post,
				$this
			);
		return isset($this->why_blocked[''.$post->ID]) && !!$this->why_blocked[''.$post->ID]['code'];
	}
	public function why_is_autopost_blocked($post, $type='msg') {
		$type = is_string($type) ? $type : 'msg';
		return isset($this->why_blocked[''.$post->ID])
			? ( isset($this->why_blocked[''.$post->ID][$type]) ? $this->why_blocked[''.$post->ID][$type] : $this->why_blocked[''.$post->ID]['msg'] )
			: '';
	}

	// generic function to save te instance information to the db, based on the post data
	public function save($post, $all) {
		if (!isset($post[$this->slug], $post[$this->slug][$this->instance_id])) return;
		$save = $post[$this->slug][$this->instance_id];
		$save['auto_post_for'] = is_array($save['auto_post_for']) ? $save['auto_post_for'] : array();
		$res = $this->_save($save);
		if ($res) $this->messages[] = 'Your settings for account ['.$this->name().'] have been saved.';
		else $this->messages[] = '<span class="error-message">Could not save the settings for account ['.$this->name().'].';
		return $this;
	}

	// alias function to quicly disable this account
	public function disable() {
		$this->settings['active'] = false;
		$res = $this->_save();
		if ($res) $this->messages[] = 'The account ['.$this->name().'] has been disabled.';
		else $this->messages[] = '<span class="error-message">Could not disable the account ['.$this->name().'].';
		return $this;
	}

	// alias function to quicly enable this account
	public function enable() {
		$this->settings['active'] = true;
		$res = $this->_save();
		if ($res) $this->messages[] = 'The account ['.$this->name().'] has been enabled.';
		else $this->messages[] = '<span class="error-message">Could not enable the account ['.$this->name().'].';
		return $this;
	}

	// public access function to determin active state of this module
	public function is_enabled() { return (bool)$this->settings['active']; }

	// remove this account's settings completely from the db
	public function delete($confirm) {
		if ($confirm != $this->instance_id) return false;
		delete_option($this->_settings_slug());
		return true;
	}

	// public access methods to allow external sources to get the properly formated field names and ids (like the settings box wrapper)
	public function field_name($base=array(), $post=false) { return $this->_name($base, $post); }
	public function field_id($base=array(), $post=false) { return $this->_id($base, $post); }

	// get any norifications that this module has thrown
	public function get_notify() { return $this->settings['notify']; }

	// reverification function. blank by default. used by modules to reverify the credentials supplied, usually after a redirection from the social network
	public function reverify() {}
	public function can_reverify($settings='') { return true; }

	// generic icon function. will only return an icon if the module defines it's own _icon() method with a return value
	public function icon() {
		$icon = trim($this->_icon());
		if (empty($icon)) return'';
		return '<div class="account-icon">'.$icon.'</div>';
	}

	// draws the auto_post_for setting. this should be generic enough for every module
	protected function _draw_auto_post_for($helper=false) {
		$helper = $helper !== false ? $helper : 'Select all the post types that should get auto posted to this account, when they are published the first time.';
		$helper = $helper ? '<span class="helper">'.$helper.'</span>' : '';

		$auto_post_for = $this->settings['auto_post_for'];
		$auto_post_for = is_array($auto_post_for) ? $auto_post_for : array();
		?>
			<div class="qs-sa-field">
				<label for="<?php echo $this->_id('auto_post_for') ?>">Autopost for Post Types</label>
				<select name="<?php echo $this->_name('auto_post_for') ?>"
						id="<?php echo $this->_id('auto_post_for') ?>"
						multiple="multiple" size="5" class="use-chosen widefat">
					<?php foreach (get_post_types(array('public' => true, 'show_ui' => true), 'objects') as $post_type): ?>
						<option value="<?php echo esc_attr($post_type->name) ?>" <?php selected(true, in_array($post_type->name, $auto_post_for)) ?>><?php echo $post_type->labels->name ?></option>
					<?php endforeach; ?>
				</select>
				<?php echo $helper ?>
			</div>
		<?php
	}

	protected function _draw_message_format($helper=false, $html=false) {
		$helper = $helper !== false ? $helper : 'The message format for new auto posts to '.$this->display_name.'. This CAN be blank'
			.($html ? ', but it CANNOT contain HTML, because '.$this->display_name.' will not allow it.' : ' and it CAN contain HTML.');
		$helper = $helper ? '<span class="helper">'.$helper.'</span' : '';
		?>
			<div class="qs-sa-field">
				<label for="<?php echo $this->_id('format') ?>">Message Format</label>
				<textarea name="<?php echo $this->_name('format') ?>"
						id="<?php echo $this->_id('format') ?>"
						class="widefat"><?php echo force_balance_tags($this->settings['format']) ?></textarea>
				<?php echo $helper ?>
				<span class="helper">
					%TITLE% - the title of the post<br/>
					%URL% - the permalink of the post<br/>
					%EXCERPT% - the excerpt of the post<br/>
					%CONTENT% - the content of the post, up to the more tag<br/>
					%FULLCONENT% - the FULL content of the post<br/>
					%IMGURL% - the url of the Featured Image of the post
					<?php if ($html): ?>
						<br/>%IMG% - image tag of the Featured Image<br/>
						%POSTLINK% - a link pointing to the post, with the title of the post as the link text
					<?php endif; ?>
				</span>
			</div>
		<?php
	}

	protected function _diff_multi($a1, $a2){
		$r = array();
		$a1 = (array)$a1;
		$a2 = (array)$a2;
		foreach ($a1 as $k => $v) {
			if (isset($a2[$k])) {
				if (is_array($a1[$k]) || is_object($a1[$k])) {
					if (is_array($a2[$k]) || is_object($a2[$k])) {
						$res = $this->_diff_multi($a1[$k], $a2[$k]);
						if (!empty($res)) $r[$k] = $res;
					} else $r[$k] = $a1[$k];
				} else {
					if (!isset($a2[$k]) || $a1[$k] != $a2[$k]) $r[$k] = $v;
				}
			} else {
				$r[$k] = $v;
			}
		}
		return $r;
	}

	// actual save function. takes any passed settings, and overlays them on the current settings, which are then saved to the db
	protected function _save($new_settings='', $force=false) {
		$old = $this->settings;

		$new_settings = wp_parse_args($new_settings, $this->settings);
		$to_save = wp_parse_args($new_settings, $this->defaults);
		$to_save['needs_reverify'] = $to_save['needs_reverify'] || $force;

		if ( !$this->new && $to_save['active'] )
			$to_save = $this->_maybe_needs_verify($to_save);
		else
			$to_save['notify'] = '';

		update_option($this->_settings_slug(), $to_save);
		$this->settings = $to_save;
		return true;
	}

	// setup a new instance, and save it
	protected function _init_new() {
		$this->settings['desc'] = 'My '.$this->display_name.' Account';
		$this->new = true;
		$this->_save();
	}

	// basic _icon() method that returns nothing
	protected function _icon() {}

	protected function _get_excerpt($post) {
		$GLOBALS['post'] = $post;
		$GLOBALS['more'] = false;
		return html_entity_decode(get_the_excerpt());
	}

	protected function _get_content($post) {
		query_posts(array('p' => $post->ID)); the_post();
		$res = get_the_content();
		wp_reset_query();
		return html_entity_decode($res);
	}

	protected function _get_thumbnail_id($post) {
		$attachment_id = 0;
		if (!has_post_thumbnail($post->ID)) {
			$attachment = array_shift(get_children(array(
				'numberposts' => 1, 'order' => 'ASC', 'post_mime_type' => 'image', 'post_parent' => $post->ID, 'post_status' => null, 'post_type' => 'attachment'
			)));
			if (is_object($attachment))
				$attachment_id = $attachment->ID;
		} else {
			$attachment_id = get_post_thumbnail_id($post->ID);
		}
		return $attachment_id;
	}

	protected function _get_image_filename($image_id) {
		$meta = wp_get_attachment_metadata($image_id);
		$file = $meta && isset($meta['file']) ? $meta['file'] : '';

		if ($file) {
			$u = wp_upload_dir();
			$file = is_array($u) && $file && isset($u['basedir']) ? trailingslashit($u['basedir']).$file : '';
		}

		return $file;
	}

	protected function _get_thumbnail_url($post) {
		$attachment_id = $this->_get_thumbnail_id($post);
		list($url) = wp_get_attachment_image_src($attachment_id, 'full');
		return (string)$url;
	}

	protected function _get_message($post) {
		$format = $this->settings['format'];

		$replace = array(
			'%URL%' => ($url = get_permalink($post->ID)),
			'%TITLE%' => ($title = html_entity_decode(apply_filters('the_title', $post->post_title))),
			'%EXCERPT%' => $this->_get_excerpt($post),
			'%CONTENT%' => $this->_get_content($post),
			'%FULLCONTENT%' => $post->post_content,
			'%IMGURL%' => $this->_get_thumbnail_url($post),
			'%IMG%' => get_the_post_thumbnail($post->ID, 'full'),
			'%POSTLINK%' => sprintf('<a href="%s" title="%s">%s</a>', esc_attr($url), esc_attr($title), force_balance_tags($title)),
		);

		return trim(str_replace(array_keys($replace), array_values($replace), $format));
	}

	protected function _debug_exception($e) {
		return $e->getMessage().' in ['.$e->getFile().'] @ '.$e->getLine().(is_callable(array($e, 'getData')) ? ' with '.$this->_debug_dump($e->getData()) : '');
	}

	protected function _debug_dump() {
		ob_start();
		$a = func_get_args();
		echo defined('PHP_SAPI') && PHP_SAPI == 'cli'
			? "-----\n"
			: '<a href="#" class="toggle-details">toggle</a><pre class="details" style="text-align:left !important; max-width:100%; width:100%;">';

		ob_start();
		if (count($a)) foreach ($a as $b) { if (is_object($b) || is_array($b)) print_r($b); else var_dump($b); }
		$inner = ob_get_contents();
		ob_end_clean();

		echo htmlspecialchars($inner);
		echo defined('PHP_SAPI') && PHP_SAPI == 'cli' ? '' : '</pre>';
		$out = ob_get_contents();
		ob_end_clean();
		return $out;
	}

	// used to quickly create the 'name' property of the settings page fields
	protected function _name($base=array(), $post=false) {
		$post_part = $post && is_object($post) ? '['.$post->ID.']' : '';
		$base = is_array($base) ? $base : (array)$base;
		return esc_attr($this->slug.$post_part.'['.$this->instance_id.']['.implode('][', $base).']');
	}

	// used to quickly create the 'id' property of the settings page fields
	protected function _id($base=array(),$post=false) {
		$post_part = $post && is_object($post) ? '-'.$post->ID : '';
		$base = is_array($base) ? $base : (array)$base;
		$base = array_filter($base);
		return esc_attr($this->slug.$post_part.'-'.$this->instance_id.($base ? '-'.implode('-', $base) : ''));
	}

	// generate the instance specific wp_options settings key
	protected function _settings_slug() {
		return $this->settings_slug.'-'.$this->instance_id;
	}

	// load the settings from the db. overlay the loaded values on top of the default values, to obtain a full set of information about the account
	protected function _load_settings() {
		$this->defaults = apply_filters('qs-sa/'.$this->slug.'/settings/default', $this->defaults, $this->instance_id);
		$this->settings = apply_filters('qs-sa/'.$this->slug.'/settings/loaded', wp_parse_args(get_option($this->_settings_slug()), $this->defaults), $this->instance_id);
	}
}
endif;
