<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; /* prevent direct access */ use QS\helpers as h;

h\olc('qssa__base_module', apply_filters('qs-sa/plugin/path', '.'.h\ds(), 0).'inc'.h\ds().'base-module.inc.php');
if (!class_exists('qssa__base_module') && class_exists('qssa__base_module__base')):
// base module, used by all other modules
abstract class qssa__base_module extends qssa__base_module__base {
	protected static $required_defaults = array(
		'access_level' => array('administrator'), // role level or <blank>
		'specific_users' => array(),
		'category_list' => array(),
		'category_filter_type' => 0,
		'post_tag_list' => array(),
		'post_tag_filter_type' => 0,
	);

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

		if (!$initial) {
			// permission based restriction
			add_action('qs-sa/settings/draw/tabs/instance_id='.$this->instance_id, array(&$this, 'draw_permissions_tab'), 100);
			add_action('qs-sa/settings/draw/tab-panels/instance_id='.$this->instance_id, array(&$this, 'draw_permissions_tab_panel'), 100);

			// taxonomy filtering
			add_action('qs-sa/settings/draw/tabs/instance_id='.$this->instance_id, array(&$this, 'draw_filter_tab'), 90);
			add_action('qs-sa/settings/draw/tab-panels/instance_id='.$this->instance_id, array(&$this, 'draw_filter_tab_panel'), 90);

			// prevent autopost due to perms
			add_filter('qs-sa-pro/autopost/can/instance_id='.$this->instance_id, array(&$this, 'can_autopost_perms'), 100, 3);

			// prevent autopost due to filters
			add_filter('qs-sa-pro/autopost/can/instance_id='.$this->instance_id, array(&$this, 'can_autopost_filters'), 100, 3);
		}

		// improve defaults with required defaults, from this and parent class
		$this->defaults = wp_parse_args($this->defaults, array_merge(parent::$required_defaults, self::$required_defaults));

		// if we just created a new instance, then set it up
		if ($initial) $this->_init_new();
		// load the settings for this instance
		$this->_load_settings();
	}

	public function draw_permissions_tab() {
		?><li><a href="#<?php echo $this->field_id() ?>-permissions">Permissions</a></li><?php
	}

	public function draw_permissions_tab_panel() {
		$roles = get_editable_roles();
		$cur_role = (array)$this->settings['access_level'];
		?>
			<div class="tab-panel" id="<?php echo $this->field_id() ?>-permissions">
				<div class="<?php echo esc_attr($this->slug) ?>-settings <?php echo esc_attr($this->slug) ?>-settings-permissions">
					<div class="qs-sa-field">
						<label for="<?php echo $this->_id('access_level') ?>">Roles</label>
						<select name="<?php echo $this->_name('access_level') ?>"
								id="<?php echo $this->_id('access_level') ?>"
								multiple="multiple" size="5" class="use-chosen widefat">
							<?php foreach ($roles as $role => $rdata): ?>
								<option value="<?php echo esc_attr($role) ?>" <?php selected(true, in_array($role, $cur_role)) ?>><?php echo translate_user_role($rdata['name']) ?></option>
							<?php endforeach; ?>
						</select>
						<span class="helper">
							Select all roles should be able to autopost to this account.
						</span>
					</div>

					<div class="qs-sa-field">
						<label for="<?php echo $this->_id('specific_users') ?>">Specfic Users</label>
						<select name="<?php echo $this->_name('specific_users') ?>"
								id="<?php echo $this->_id('specific_users') ?>"
								multiple="multiple" size="5" class="use-chosen widefat chosen-ajax" ajax-type="users">
							<?php foreach ($this->settings['specific_users'] as $user_id): $user = get_user_by('id', $user_id); if (!$user) continue; ?>
								<option selected="seleected" value="<?php echo esc_attr($user->ID) ?>"><?php echo sprintf(
									'%s [%s : %s] (#%d)',
									$user->display_name,
									$user->user_login,
									$user->user_email,
									$user->ID
								) ?></option>
							<?php endforeach; ?>
						</select>
						<span class="helper">
							Choose specific users that additionally have access to autopost to this account.
							These users will be granted access, even if they do not have one of the roles specified above.
						</span>
					</div>
				</div>
			</div>
		<?php
	}

	public function draw_filter_tab() {
		?><li><a href="#<?php echo $this->field_id() ?>-filtering">Filtering</a></li><?php
	}

	public function draw_filter_tab_panel() {
		?>
			<div class="tab-panel" id="<?php echo $this->field_id() ?>-filtering">
				<div class="<?php echo esc_attr($this->slug) ?>-settings <?php echo esc_attr($this->slug) ?>-settings-permissions">
					<?php foreach (array('category', 'post_tag') as $tax_slug): $tax = get_taxonomy($tax_slug); if (!$tax) continue; ?>
						<div class="qs-sa-field">
							<label for="<?php echo $this->_id($tax_slug.'_filter_type') ?>"><?php echo $tax->labels->singular_name ?> Filtering</label>
							<div class="filter-type qs-sa-pre-field">
								<span class="cb-wrap">
									<input type="radio" name="<?php echo $this->_name($tax_slug.'_filter_type') ?>"
											<?php echo checked(0, $this->settings[$tax_slug.'_filter_type']) ?>
											id="<?php echo $this->_id($tax_slug.'_filter_type') ?>" value="0" />
									<span class="cb-label">Exclude posts that have...</span>
								</span>
								<span class="cb-wrap">
									<input type="radio" name="<?php echo $this->_name($tax_slug.'_filter_type') ?>"
											<?php echo checked(1, $this->settings[$tax_slug.'_filter_type']) ?>
											id="<?php echo $this->_id($tax_slug.'_filter_type') ?>" value="1" />
									<span class="cb-label">Include <strong>only</strong> posts that have...</span>
								</span>
							</div>
							<select name="<?php echo $this->_name($tax_slug.'_list') ?>"
									id="<?php echo $this->_id($tax_slug.'_list') ?>"
									multiple="multiple" size="5" class="use-chosen widefat chosen-ajax" ajax-type="tax:<?php echo $tax_slug ?>">
								<?php foreach ($this->settings[$tax_slug.'_list'] as $term_id): $term = get_term_by('id', $term_id, $tax_slug); if (!$term) continue; ?>
									<option selected="seleected" value="<?php echo esc_attr($term->term_id) ?>"><?php echo sprintf(
										'%s [%s] (#%d)',
										$term->name,
										$term->slug,
										$term->term_id
									)?></option>
								<?php endforeach; ?>
							</select>
							<span class="helper">
								Either exclude or include posts from being autoposted, based on the <?php echo $tax->labels->name ?> they have.
							</span>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php
	}

	final public function can_autopost($post) {
		$can = parent::can_autopost($post);

		$can = apply_filters('qs-sa-pro/autopost/can', $can, $post, $this);
		$can = apply_filters('qs-sa-pro/autopost/can/type='.$this->slug, $can, $post, $this);
		$can = apply_filters('qs-sa-pro/autopost/can/instance_id='.$this->instance_id, $can, $post, $this);

		return $can;
	}

	final public function can_autopost_perms($can, $post) {
		$ocan = $can;

		$cu = wp_get_current_user();
		$perms = !!$this->settings['access_level']
			? !!(array_intersect($cu->roles, (array)$this->settings['access_level']))
			: false;
		$perms = !!$this->settings['specific_users']
			? ( $perms || in_array($cu->ID, (array)$this->settings['specific_users']) )
			: $perms;

		$can = $can && $perms;
		
		if ($ocan != $can)
			$this->why_blocked[''.$post->ID] = apply_filters(
				'qs-sa/autopost/blocked',
				array('msg' => 'You do not have permission to autopost to this account. Contact the site administrator for access.', 'code' => 10),
				$post,
				$this
			);

		return $can;
	}

	final public function can_autopost_filters($can, $post) {
		$post = get_post($post);
		$ocan = $can;
		$msg = array();

		$cats = wp_get_object_terms(array($post->ID), array('category'), array('fields' => 'ids'));
		$tags = wp_get_object_terms(array($post->ID), array('post_tag'), array('fields' => 'ids'));

		$in_cats = array_intersect($cats, $this->settings['category_list']);
		$ccats = count($in_cats); $clcats = count($this->settings['category_list']);
		$in_tags = array_intersect($tags, $this->settings['post_tag_list']);
		$ctags = count($in_tags); $cltags = count($this->settings['post_tag_list']);

		if (!$this->settings['category_filter_type'] && $ccats) {
			$names = get_terms(array('category'), array('hide_empty' => false, 'fields' => 'names', 'include' => $in_cats));
			$msg[] = 'post has excluded categor'.($ccats != 1 ? 'ies' : 'y').' ['.implode(', ', $names).']';
		} else if ($this->settings['category_filter_type'] && !$ccats) {
			$names = get_terms(array('category'), array('hide_empty' => false, 'fields' => 'names', 'include' => $this->settings['category_list']));
			$msg[] = 'post does <strong>not have</strong> '.($clcats != 1 ? 'any of ' : '').'the categor'.($clcats != 1 ? 'ies' : 'y').' ['.implode(', ', $names).']';
		}

		if (!$this->settings['post_tag_filter_type'] && $ctags) {
			$names = get_terms(array('post_tag'), array('hide_empty' => false, 'fields' => 'names', 'include' => $in_tags));
			$msg[] = 'post has excluded tag'.($ctags != 1 ? 's' : '').' ['.implode(', ', $names).']';
		} else if ($this->settings['post_tag_filter_type'] && !$ctags) {
			$names = get_terms(array('post_tag'), array('hide_empty' => false, 'fields' => 'names', 'include' => $this->settings['post_tag_list']));
			$msg[] = 'post does <strong>not have</strong> '.($cltags != 1 ? 'any of ' : '').'the tag'.($cltags != 1 ? 's' : '').' ['.implode(', ', $names).']';
		}

		$can = $can && !$msg;
		
		if ($ocan != $can)
			$this->why_blocked[''.$post->ID] = apply_filters(
				'qs-sa/autopost/blocked',
				array('msg' => 'Cannot autopost because: '.implode(', ', $msg), 'code' => 10),
				$post,
				$this
			);

		return $can;
	}

	final public function is_autopost_blocked($post) {
		$blocked = parent::is_autopost_blocked($post);

		$blocked = !apply_filters('qs-sa-pro/autopost/can', !$blocked, $post, $this);
		$blocked = !apply_filters('qs-sa-pro/autopost/can/type='.$this->slug, !$blocked, $post, $this);
		$blocked = !apply_filters('qs-sa-pro/autopost/can/instance_id='.$this->instance_id, !$blocked, $post, $this);

		return $blocked;
	}

	// generic function to save te instance information to the db, based on the post data
	public function save($post, $all) {
		if (!isset($post[$this->slug], $post[$this->slug][$this->instance_id])) return;
		$save = $post[$this->slug][$this->instance_id];

		$save['auto_post_for'] = is_array($save['auto_post_for']) ? $save['auto_post_for'] : array();
		$save['access_level'] = is_array($save['access_level']) ? $save['access_level'] : array();
		$save['specific_users'] = is_array($save['specific_users']) ? $save['specific_users'] : array();

		$res = $this->_save($save);
		if ($res) $this->messages[] = 'Your settings for account ['.$this->name().'] have been saved.';
		else $this->messages[] = '<span class="error-message">Could not save the settings for account ['.$this->name().'].';
		return $this;
	}
}
endif;
