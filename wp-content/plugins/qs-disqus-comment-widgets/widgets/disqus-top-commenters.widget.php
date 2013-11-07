<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;

if (!class_exists('QSDCW_Top_Commenters')):

class QSDCW_Top_Commenters extends QSDCW_Widget {
	protected $proper_name = 'QS - Disqus Top Commenters';
	protected $short_name = 'disqus-top-commenters';
	protected $defaults = array(
		'title' => 'Top Commenters',
		'limit' => 5,
		'post_limit' => 1,
	);
	protected $timer = 0;

	public static function pre_init() {
		add_action('widgets_init', array(__CLASS__, 'widgets_init'), 11);
	}

	public static function widgets_init() {
		register_widget(__CLASS__);
		do_action('qsda-register-widget', __CLASS__);
	}

	public function QSDCW_Top_Commenters() {
		parent::WP_Widget(false, $this->proper_name);
		$this->_setup_widget(__CLASS__, __FILE__);
	}

	protected function _form($inst) {
		?>
			<div class="settings-wrapper">
				<div class="setting">
					<label>Title</label>
					<input type="text" class="widefat"
							id="<?php echo $this->get_field_id('title') ?>"
							name="<?php echo $this->get_field_name('title') ?>"
							value="<?php echo esc_attr($inst['title']) ?>" />
				</div>
				<div class="setting">
					<label>Commenters to Display</label>
					<input type="text" class="widefat"
							id="<?php echo $this->get_field_id('limit') ?>"
							name="<?php echo $this->get_field_name('limit') ?>"
							value="<?php echo esc_attr($inst['limit']) ?>" />
				</div>
				<div class="setting">
					<label>Posts per Commenter</label>
					<input type="text" class="widefat"
							id="<?php echo $this->get_field_id('post_limit') ?>"
							name="<?php echo $this->get_field_name('post_limit') ?>"
							value="<?php echo esc_attr($inst['post_limit']) ?>" />
				</div>
			</div>
		<?php
	}

	protected function _get_uniqid($salt) {
		$key = @json_encode(getallheaders());
		if (isset($_SERVER, $_SERVER['REMOTE_ADDR']))
			$key .= '|'.$_SERVER['REMOTE_ADDR'];
		$key .= md5(strrev($salt));
		return sha1($key);
	}

	protected function _get_lock_key() {
		return '_qsda_lock_'.$this->id;
	}

	protected function _get_timer_key() {
		return '_qsda_timer_'.$this->id;
	}

	protected function _is_force() {
		$force = $this->_clear_cache(array('clear_cache', 'clear_widget_cache'));

		if (!$force && !$this->_cache_file_exists($this->_cache_file_name())) $force = true;

		return $force;
	}

	protected function _cache_file_name() {
		return $this->id.'.cache';
	}

	protected function _get_option($key) {
		global $wpdb;

		$q = $wpdb->prepare('select option_value from '.$wpdb->options.' where option_name = %s', $key);
		$val = maybe_unserialize($wpdb->get_var($q));

		return $val;
	}

	protected function _widget($args, $instance) {
		$lock_key = $this->_get_lock_key();
		$timer_key = $this->_get_timer_key();
		$uniq = $this->_get_uniqid(microtime(true));

		$redraw = false;
		$lock = get_option($lock_key, false);
		$timer = get_option($timer_key, 300); // length of time before next recache, calculated each recache
		$force = $this->_is_force();

		// set a intermediate time, which will serve as the 'lock' for this client to redraw the cache. if this client has not completed the redraw by the end of this timer
		// then another client will be able to complete it, because the lock will expire. at the end of a successful redraw, the time is extended the full length
		$lock_timer = ( ($timer + rand(1, $timer * .2)) * .1 );
		$lock_timer = $lock_timer < 10 ? 10 : $lock_timer;

		$package = array(
			'by' => $uniq,
			'until' => time() + $lock_timer,
		);

		if ($force || empty($lock) || !is_array($lock) || !isset($lock['until'])) {
			$redraw = true;
			update_option($lock_key, $package);
		} elseif (is_array($lock) && $lock['until'] < time()) {
			$redraw = true;
			update_option($lock_key, $package);
		}

		if ($redraw) {
			usleep(5000); // double check this is the client redrawing cache
			$val = $this->_get_option($lock_key);
			if ($val != $package) $redraw = false;
		}

		$out = '';
		if (!$redraw) {
			$out = $this->_get_cache_file_contents($this->_cache_file_name());
			$out .= '<!-- FROM CACHE: '.$lock_key.'|'.implode(':', array_values($lock)).':'.$timer.' -->';
		} else {
			$commenters = $this->_get_commenter_data($args, $instance);
			if (is_wp_error($commenters)) return $this->_do_error($commenters);
			list($r, $of, $time_left) = apply_filters('qsda-ratelimit', array(999, 1000, 3600));
			$args['commenters'] = $commenters;

			ob_start();
			$this->_display_widget($args, $instance);
			$out = ob_get_contents();
			ob_end_clean();
			$out = $this->_clean_output($out);

			$this->_put_cache_file_contents($this->_cache_file_name(), $out);

			$tlratio = $time_left / 3600; // ratio of time left to max lockout length
			$rratio = $r / $of; // ratio of remaining requests to max requests
			$timer_length = 300;
			$buffer_adjust = .95; // calculate timer totals based on this percentage of the actual numbers, to allow for a percentage of margin of error. .95 would leave a 5% margin of error
			$burnrate = 1 + (($instance['post_limit'] + 2) * $instance['limit']); // one request to pull the list of comments, then one request per comment to find the post that it links to

			// adjust the cache lock timer so that we do not burn through our requests too quickly, based on our current burnrate.
			$timer = $r / $burnrate; // current gap of time needed to space out requests, such that we do not hit the limit
			$timer = ceil($timer/$buffer_adjust); // allow for margin of error
			$timer = $timer < 30 ? 30 : $timer;
			$timer = 300; // force 5 minutes until we fix cache
			update_option($timer_key, $timer);

			$package = array(
				'by' => $uniq,
				'until' => time() + $timer,
			);

			update_option($lock_key, $package);

			$out .= '<!-- FRESH CACHE: '.$lock_key.'|'.implode(':', array_values($package)).':'.$timer.' -->';
		}

		echo $out;
	}

	protected function _clean_output($str) {
		$str = preg_replace('#>\s+(\S)#', '>\1', $str);
		$str = preg_replace('#(\S)\s+<#', '\1<', $str);
		return $str;
	}

	protected function _get_commenter_data($args, $instance) {
		$forum = apply_filters('qsda-option', '', 'disqus_forum_url');
		if (empty($forum)) return new WP_Error('Forum URL setting is missing.', 'misconfiguration');

		$data = apply_filters('qsda-query', array(), array(
			'resource' => 'forums/listMostActiveUsers',
			'data' => array(
				'forum' => $forum,
				'limit' => $instance['limit'],
				'order' => 'desc',
			),
		));
		if (is_wp_error($data)) return $data;
		if (!is_object($data) && !isset($data->response)) return new WP_Error('Invalid response.', 'invalid_response');

		$commenters = $data->response;

		foreach ($commenters as $ind => $commenter) {
			if ($instance['post_limit'] > 0 && isset($commenter->id)) {
				$comments = apply_filters('qsda-query', array(), array(
					'resource' => 'users/listPosts',
					'data' => array(
						'limit' => $instance['post_limit'],
						'user' => $commenter->id,
						'include' => 'approved',
						'order' => 'desc',
					),
				));
				if (!is_wp_error($comments) && isset($comments->response) && is_array($comments->response)) {
					$commenters[$ind]->recent_posts = array();
					foreach ($comments->response as $comment) {
						$thread_data = apply_filters('qsda-query', array(), array(
							'resource' => 'threads/details',
							'data' => array(
								'forum' => $forum,
								'thread' => $comment->thread,
							),
						));
						if (!is_wp_error($thread_data) && isset($thread_data->response)) {
							$commenters[$ind]->recent_posts[] = $thread_data->response;
						}
					}
					if (empty($commenters[$ind]->recent_posts)) $commenters[$ind]->recent_posts = false;
				} else {
					$commenters[$ind]->recent_posts = false;
				}
			} else {
				$commenters[$ind]->recent_posts = false;
			}
		}

		return $commenters;
	}

	public function update($new_inst, $old_inst) {
		$new_inst = parent::update($new_inst, $old_inst);
		$new_inst['limit'] = $new_inst['limit'] > 100 ? 100 : (int)$new_inst['limit'];
		$new_inst['limit'] = $new_inst['limit'] < 0 ? 0 : (int)$new_inst['limit'];
		$new_inst['post_limit'] = (int)$new_inst['post_limit'];
		return $new_inst;
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	QSDCW_Top_Commenters::pre_init();
}

endif;
