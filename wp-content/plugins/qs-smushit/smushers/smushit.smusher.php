<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

require_once 'smusher.php';

class QS_Smushit_Smusher extends QS_Smusher {
	protected static $smush_submit_url = 'http://www.smushit.com/ysmush.it/ws.php';

	protected function _process() {
		if (empty($this->srcs)) return false; // none to process

		$data = $result = array();
		$ind = 0;
		foreach ($this->scrs as $path) {
			if (!is_readable($path)) {
				$this->_add_error('src_file_permissions', 'Could not read source file.', $path);
				continue;
			}

			$new_path = $this->_get_dest_file_name($path);

			if (!is_writable(dirname($new_path))) {
				$this->_add_error('dest_file_permissions', 'Could not write to directory of new file.', $path);
				continue;
			}

			$path_size = filesize($path);
			$key = basename($path).'::'.$path_size;
			$data['files['.$ind.']'] = '@'.$path;

			$result[$key] = array(
				'src' => $path,
				'src_size' => $path_size,
				'dest' => $new_path,
				'dest_size' => $path_size,
				'success' => false,
			);
		}

		$curl_result = self::_curl(self::$smush_submit_url, $data, 'POST');
		if (!is_array($curl_result) || empty($curl_result)) return false;

		$curl_resp = @json_decode($res['response_body']);
		if (!is_array($resp)) return false;

		$this->_process_response($result, $curl_resp);

		return count($this->raw) ? true : false;
	}

	protected function _process_response($items, $resp){
		foreach ($resp as $item) {
			if (!is_object($item)) {
				$this->_add_error('smushit_unexpected_response', 'Part of the response from Smushit was not in the proper format.', $item);
				continue;
			}

			$item->src = urldecode($item->src);
			$key = basename($item->src).'::'.$item->src_size;

			if (!isset($items[$key])) {
				$this->_add_error('smushit_invalid_response', 'We could not match part of the Smushit response to the items we requested.', array('resp' => $resp, 'request' => $items));
				continue;
			}

			$res = $items[$key];

			if ($item->dest_file < 0) {
				$res['dest'] = $res['src'];
				$res['success'] = false;
				$this->_add_image($res);
				continue;
			}

			$img_curl = self::_curl($item->dest, '', 'GET');

			if (!is_array($img_curl)) {
				$this->_add_error('smushit_fetch_problem', 'Could not fetch the Smushit generated image.', array('image' => $item, 'curl' => $img_curl));
				continue;
			} else if ($img_curl['download_content_length'] != $item->dest_size) {
				$this->_add_error('smushit_checksum_fail', 'The file we fetched did not match the size reported from Smushit.', array('image' => $item, 'curl' => $img_curl));
				continue;
			} else {
				$success = @file_put_contents($res['dest'], $img_curl['response_body']);
				if (!$success) {
					$res['dest_size'] = $success;
					$res['success'] = true;
				} else {
					$res['dest'] = $res['src'];
					$res['success'] = false;
				}
			}

			$this->_add_image($res);
		}

		return count($this->raw) ? true : false;
	}

	protected function _headers($extra_headers=array()) {
		if (is_object($extra_headers)) $extra_headers = (array)$extra_headers;
		if (!is_array($extra_headers)) throw new Exception('Extra headers are not in the correct format.', 10000, array('extra_headers' => $extra_headers));

		$out = array();
		foreach ($extra_headers as $k => $v) {
			if (is_scalar($v)) {
				if (is_numeric($k)) $out[] = $v;
				else $out[] = $k.': '.$v;
			}
		}

		return $out;
	}

	protected function _curl($url, $fields=array(), $method='GET', $body_only=false, $extra_headers=array(), $extra_opts=array()) {
		if (is_string($extra_headers)) parse_str($extra_headers, $extra_headers);
		$extra_opts = is_array($extra_opts) ? $extra_opts : array();

		$c = curl_init($url);
		$headers = array(
			'Accept' => 'text/html, application/xhtml+xml, */*',
			'Cache-Control' => 'no-cache',
			'Connection' => 'Keep-Alive',
			'Accept-Language' => 'en-us',
			'User-Agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.44 Safari/537.36',
		);

		$opts = array(
			CURLOPT_HTTPHEADER => self::_headers(array_merge($headers, $extra_headers)),
			//CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.44 Safari/537.36',
			CURLOPT_RETURNTRANSFER => 1,
			CURLINFO_HEADER_OUT => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_TIMEOUT => 0.1,
			CURLOPT_CONNECTTIMEOUT => 0.1,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_MAXREDIRS => 18,
			CURLOPT_AUTOREFERER => 1,
			CURLOPT_COOKIEFILE => '',
		);

		if ($method == 'GET') {
			$opts[CURLOPT_HTTPGET] = 1;
		} else if ($method == 'POST') {
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = $fields;
		} else {
			$opts[CURLOPT_CUSTOMREQUEST] = $method;
		}

		foreach ($extra_opts as $k => $v) $opts[$k.''] = $v;

		curl_setopt_array($c, $opts);

		$resp = curl_exec($c);

		if (($errno = curl_errno($c)) && ($errmsg = curl_error($c)) && strpos($errmsg, 'SSL') !== false) {
			curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
			$resp = curl_exec($c);
		}

		$stats = curl_getinfo($c);
		$stats['_last_errno'] = curl_errno($c);
		$stats['_last_errmsg'] = curl_error($c);
		$stats['request_fields'] = $fields; //is_array($fields) || is_object($fields) ? http_build_query($fields) : $fields;
		$stats['response_headers'] = substr($resp, 0, $stats['header_size']);
		$stats['response_body'] = substr($resp, $stats['header_size']);

		curl_close($c);
		if ($body_only) return $stats['response_body'];

		return $stats;
	}

	public static function name() { return 'Smush.it'; }

	public static function requires() {
		return 'the cURL PHP Library must be installed.';
	}

	public static function supports_mime( $mime_type ) { return true; }

	public static function available( $args = array() ) {
		if (!function_exists('curl_init') || !function_exists('curl_close'))
			return false;

		//add_action('admin_notices', function() { echo '<div class="updated"><p>Using SmushIt...</p></div>'; }, 10);
		return true;
	}
}
