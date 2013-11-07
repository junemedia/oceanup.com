<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null;
/**
 * Disqus API interface class. Hopefully makes communication to disqus universal, for all our widgets.
 */

class QS_Disqus_API {
	protected static $version = '1.0.0';
	protected static $options = array();
	protected static $prefix = 'qsda-';

	protected static $url_format = 'https://disqus.com/api/{version}/{resource}.{output_type}';
	protected static $api_version = '3.0';
	protected static $default_output_type = 'json';

	protected static $last_limits = array('r' => 999, 'of' => 1000, 'tl' => 3600);

	public static function pre_init() {
		self::_load_options();

		add_filter('qsda-query', array(__CLASS__, 'process_request'), 10, 2);
		add_filter('qsda-option', array(__CLASS__, 'get_option'), 10, 2);
		add_filter('qsda-ratelimit', array(__CLASS__, 'get_ratelimit'), 10, 1);
	}

	public static function get_ratelimit($current) {
		return array(self::$last_limits['r'] /* remainder */, self::$last_limits['of'] /* max requests */, self::$last_limits['tl'] /* time remaining for ratelimit reset */);
	}

	public static function get_option($default, $name) {
		return is_scalar($name) && isset(self::$options[$name]) ? self::$options[$name] : $default;
	}

	public static function process_request($current, $args) {
		if (!self::_check_api_creds('disqus_secret_key')) return new WP_Error('Disqus API credentials are missing.', 'credentials_missing');

		$args = wp_parse_args($args, array(
			'output_type' => self::$default_output_type,
			'resource' => '',
			'method' => 'get',
			'data' => array(),
		));
		$args['method'] = strtolower($args['method']);
		$args['version'] = self::$api_version;

		if (empty($args['resource'])) return new WP_Error('Disqus "resource" cannot be empty.', 'empty_resource');

		$method = in_array($args['method'], array('get', 'post')) ? $args['method'] : 'get';
		$data = (array)$args['data'];
		$data['api_secret'] = self::$options['disqus_secret_key'];

		$url_args = array(
			'{output_type}' => $args['output_type'],
			'{resource}' => $args['resource'],
			'{version}' => $args['version'],
		);
		$url = str_replace(array_keys($url_args), array_values($url_args), self::$url_format);

		$req_args = array(
			'timeout' => 15,
			'redirection' => 5,
			'httpversion' => '1.0',
			'user-agent' => 'Quadshot Disqus API '.self::$version.'; '.site_url(),
			'blocking' => true,
			'headers' => array(),
			'body' => null,
			'cookies' => array(),
			'compress' => false,
			'decompress' => true,
			'sslverify' => true,
		);

		if ($method == 'get' ) {
			$url = add_query_arg($data, $url);
			$req_args['method'] = 'GET';
			$resp = wp_remote_get($url, $req_args);
		} elseif ($method = 'post') {
			$req_args['method'] = 'POST';
			$req_args['body'] = $data;
			$resp = wp_remote_post($url, $req_args);
		}

		$respdata = false;

		if (is_array($resp)) {
			if (isset($resp['headers'])) {
				$buffer = 0.05;
				if (isset($resp['headers']['x-ratelimit-reset'])) {
					$ts = time();
					$tl = ($resp['headers']['x-ratelimit-reset'] - $ts); // remaining time in the ratelimit, before the count is reset
					self::$last_limits['tl'] = $tl;
					$buffer = $tl / 3600; // ratio of time left, used to determine if we are ahead or behind schedule for the ratelimit
				}

				if (isset($resp['headers']['x-ratelimit-remaining'], $resp['headers']['x-ratelimit-limit'])) {
					self::$last_limits['r'] = $resp['headers']['x-ratelimit-remaining'];
					self::$last_limits['of'] = $resp['headers']['x-ratelimit-limit'];
					//if ($resp['headers']['x-ratelimit-remaining'] / $resp['headers']['x-ratelimit-limit'] < $buffer)
						//return new WP_Error('Rate limit is closing faster than we expect. Skipping this refresh.', 'ratelimit_behind');
				}
			}

			if (isset($resp['response'])) {
				if (isset($resp['response']['code']) && $resp['response']['code'] != '200') {
					return new WP_Error('Problem getting response from Disqus.', 'response_problem', $resp);
				}
			}

			if (isset($resp['body']) && is_scalar($resp['body'])) {
				$respdata = @json_decode($resp['body']);
				if (empty($respdata)) $respdata = false;
			}
		}

		return $respdata === false ? $current : $respdata;
	}

	protected static function _check_api_creds($only_key=false) {
		if ($only_key && is_string($only_key)) return !empty(self::$options[$only_key]);

		return !(empty(self::$options['disqus_secret_key']) || empty(self::$options['disqus_api_key']));
	}

	protected static function _load_options() {
		self::$options = array();
		
		$opt_list = array(
			'disqus_api_key',
			'disqus_secret_key',
			'disqus_forum_url',
		);

		foreach ($opt_list as $suffix) {
			$val = get_option(self::$prefix.$suffix, '');
			if (empty($val)) {
				$from_comment_plugin = get_option($suffix, '');
				if (!empty($from_comment_plugin)) {
					$val = $from_comment_plugin;
					update_option(self::$prefix.$suffix, $val);
				}
			}
			self::$options[$suffix] = $val;
		}
	}
}

if (defined('ABSPATH') && function_exists('add_action')) {
	QS_Disqus_API::pre_init();
}
