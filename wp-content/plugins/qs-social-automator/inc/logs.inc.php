<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

if (!class_exists('qssa_logger')):
class qssa_logger {
	protected static $log_types = array(
		'log' => 'qssa-log', // comment type for auto post results
		'details' => 'qssa-detailed-log', // comment type for auto post results
	);
	protected static $log_contexts = array(
		'neutral' => '- ',
		'good' => '[<span class="qssa-log-good">GOOD</span>] - ',
		'bad' => '[<span class="qssa-log-bad">BAD</span>] - ',
	);
	protected static $main_settings = array();

	public static function pre_init() {
		self::$main_settings = apply_filters('qs-sa/plugin/settings', self::$main_settings);
		// remainder of init should happen after all plugins are loaded, because that will give time for other plugins to add hooks for ours
		add_action('qs-sa/plugin/loaded', array(__CLASS__, 'after_plugins'), 1000);
	}

	public static function after_plugins() {
		if (is_admin()) {
			// setup metaboxes
			add_action('add_meta_boxes', array(__CLASS__, 'setup_meta_boxes'), 9);
		}

		// add the log action for public logging
		add_action('qs-sa/autopost/log', array(__CLASS__, 'autopost_log'), 10, 4);

		// hide our comment log if not specifically requested
		add_filter('comments_clauses', array(__CLASS__, 'hide_comment_log'), 10, 2);
	}

	public static function autopost_log($type=false, $post_id=false, $msg=false, $context='neutral') {
		$type = $type && is_scalar($type) && isset(self::$log_types[$type]) ? self::$log_types[$type] : self::$log_types['log']; // default to regular comment log
		$post_id = $post_id ? $post_id : 0; // default to system log, rather than specific post
		$msg = trim($msg) ? trim($msg) : self::_dbgbt(2); 
		$time = current_time('mysql');
		$context = is_scalar($context) && isset(self::$log_contexts[$context]) ? self::$log_contexts[$context] : self::$log_contexts['neutral'];

		$msg = $context.$msg;
		
		$data = array(
			'comment_post_ID' => $post_id,
			'comment_author' => 'qssa-social-automator',
			'comment_author_email' => get_bloginfo('admin_email'),
			'comment_author_url' => site_url(),
			'comment_content' => $msg,
			'comment_type' => $type,
			'comment_parent' => 0,
			'user_id' => 1,
			'comment_author_IP' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : 'cli',
			'comment_agent' => 'qssa-social-automator logger',
			'comment_date' => $time,
			'comment_approved' => 1,
		);

		return wp_insert_comment($data);
	}

	public static function hide_comment_log($clauses, $query) {
		if (!isset($query->query_vars['qs-sa'])) {
			$clauses['where'] .= ' AND comment_type not in ("'.implode('","', array_values(self::$log_types)).'") ';
		} else {
			$clauses['where'] = preg_replace('# AND comment_type (IN \([^\)]*?\)|= (\'|")[^\2]*?\2)#', '', $clauses['where']);
			if (isset($query->query_vars['qs-sa-detail'])) {
				$clauses['where'] .= ' AND comment_type in ("'.implode('","', array_values(self::$log_types)).'") ';
			} else {
				$clauses['where'] .= ' AND comment_type in ("'.self::$log_types['log'].'") ';
			}
		}
		return $clauses;
	}

	public static function setup_meta_boxes() {
		foreach (self::$main_settings['post_types'] as $screen) {
			add_meta_box(
				'qs-sa-log',
				'Quadshot Social Automator - Log',
				array(__CLASS__, 'mb_log'),
				$screen,
				'advanced',
				'low'
			);
		}
	}

	public static function mb_log($post, $mb) {
		$comments = get_comments(array(
			'qs-sa' => true,
			'qs-sa-detail' => true,
			'post_id' => $post->ID,
		));

		?>
			<ul class="log-comments icon-16">
				<?php if (count($comments)): ?>
					<?php foreach ($comments as $comment): ?>
						<li class="log-item" id="comment-<?php echo $comment->comment_ID ?>">
							<span class="log-time">[<?php echo human_time_diff(strtotime($comment->comment_date_gmt)) ?>]</span>
							<?php echo $comment->comment_content ?>
						</li>
					<?php endforeach; ?>
				<?php else: ?>
					<li class="no-logs log-item">No logs yet for this post.</li>
				<?php endif; ?>
			</ul>
			<script type="text/javascript">
				if (typeof jQuery == 'function' || typeof jQuery == 'object') (function($) {
					$(document).on('click', '.log-comments .log-item .toggle-details', function(e) { e.preventDefault(); $(this).next('.details').toggle(); });
				})(jQuery);
			</script>
		<?php
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	qssa_logger::pre_init();
}
endif;
