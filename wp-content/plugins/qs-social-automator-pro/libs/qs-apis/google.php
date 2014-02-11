<?php
namespace QS\APIs {
(__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
use QS\helpers as h;

abstract class google {
	protected $settings = array();
	protected $defaults = array(
		'username' => '',
		'password' => '',
		'post_to_page' => '',
		'login_url' => '',
		'login_endpoint_url' => 'https://accounts.google.com/ServiceLoginAuth',
		'pre_file_upload_url' => 'https://plus.google.com/_/upload/photos/resumable?authuser=0',
		'cookies' => '',
		'user_agent' => 'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/28.0.1500.44 Safari/537.36',
	);

	protected $url_formats = array();

	protected $origin = '';
	protected $ssl_check_url = 'https://accounts.google.com/ServiceLogin';
	protected $ssl_errno = 0;
	protected $ssl_errmsg = '';
	protected $disable_ssl_verify = false;

	protected $connected = false;
	protected $logged_in = false;
	protected $cookies = array();

	protected $referer = '';

	protected $_instance_keys = array();
	private $_basic_keys = array('settings', 'connected', 'logged_in', 'cookies', 'referer');

	public function __construct($args='') {
		if (!empty($args)) $this->setSettings($args);
	}

	public function getInstance() {
		$data = array();
		foreach (array_merge($this->_instance_keys, $this->_basic_keys) as $k)
			$data[$k] = $this->{$k};
		return $data;
	}

	public function setInstance($data) {
		foreach (array_merge($this->_instance_keys, $this->_basic_keys) as $k)
			$this->{$k} = $data[$k];
	}

	public function setSettings($args='') {
		if (is_string($args)) parse_str($args, $args);
		if (is_object($args)) $args = (array)$args;
		if (!is_array($args)) throw new Exception('The setting supplied are not in an accepted format.', '5001');
		$this->settings = array_merge($this->defaults, $this->settings, $args);
		return $this;
	}

	public function getCookies() {
		return $this->settings['cookies'];
	}

	abstract public function post($message, $fields='', $args='');
	abstract public function connect();
	abstract public function login();
	abstract public function verify();

	protected function _curl($url, $fields=array(), $method='GET', $body_only=false, $extra_headers=array(), $extra_opts=array()) {
		if (is_string($extra_headers)) parse_str($extra_headers, $extra_headers);
		//if (is_string($fields)) parse_str($fields, $fields);
		$extra_opts = is_array($extra_opts) ? $extra_opts : array();
		$method = in_array(($method = strtoupper($method)), array('GET', 'POST')) ? $method : (empty($fields) ? 'GET'  : 'POST');

		$c = curl_init($url);
		$headers = array(
			'Accept' => 'text/html, application/xhtml+xml, */*',
			'Cache-Control' => 'no-cache',
			'Connection' => 'Keep-Alive',
			'Accept-Language' => 'en-us',
		);

		$opts = array(
			CURLOPT_HTTPHEADER => $this->_headers(array_merge($headers, $extra_headers)),
			CURLOPT_USERAGENT => $this->settings['user_agent'],
			CURLOPT_RETURNTRANSFER => 1,
			CURLINFO_HEADER_OUT => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_TIMEOUT => 8,
			CURLOPT_CONNECTTIMEOUT => 8,
			CURLOPT_FOLLOWLOCATION => 1,
			CURLOPT_MAXREDIRS => 8,
			CURLOPT_AUTOREFERER => 1,
			CURLOPT_COOKIEFILE => '',
		);

		$cookie_str = $this->_request_cookie_str($this->cookies, $url);
		if (!empty($cookie_str)) $opts[CURLOPT_COOKIE] = $cookie_str;

		if (!empty($this->referer)) {
			$opts[CURLOPT_REFERER] = $this->referer;
		}

		if ($this->disable_ssl_verify) {
			$opts[CURLOPT_SSL_VERIFYHOST] = 0;
			$opts[CURLOPT_SSL_VERIFYPEER] = 0;
		}

		if ($method == 'GET') {
			$opts[CURLOPT_HTTPGET] = 1;
		} else if ($method == 'POST') {
			$opts[CURLOPT_POST] = 1;
			$opts[CURLOPT_POSTFIELDS] = is_array($fields) || is_object($fields) ? http_build_query($fields) : $fields;
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
		$stats['request_fields'] = is_array($fields) || is_object($fields) ? http_build_query($fields) : $fields;
		$stats['response_headers'] = substr($resp, 0, $stats['header_size']);
		$stats['response_body'] = substr($resp, $stats['header_size']);

		curl_close($c);
		if ($body_only) return $stats['response_body'];

		$stats['set_cookies'] = $this->_parse_response_cookies($stats['response_headers']);
		foreach ($stats['set_cookies'] as $k => $v) $this->cookies[$k] = $v;

		return $stats;
	}

	protected function _parse_response_cookies($raw_headers) {
		$cookies = array();

		preg_match_all('#^set-cookie:(.*?)$#mi', $raw_headers, $cookie_headers, PREG_SET_ORDER);
		foreach ($cookie_headers as $header_value) {
			list($k, $v) = $this->_parse_one_set_cookie($header_value[1]);
			$cookies[$k] = $v;
		}

		return $cookies;
	}

	protected function _parse_one_set_cookie($hval) {
		$k = '';
		$v = array();

		$parts = preg_split('#\s*;\s*#', trim($hval));

		$actual = array_shift($parts);
		$actual_pieces = explode('=', $actual);
		$k = array_shift($actual_pieces);
		$v['value'] = implode('=', $actual_pieces);

		if (count($parts)) foreach ($parts as $piece) {
			@list($pk, $pv) = explode('=', $piece);
			$v[strtolower($pk)] = $pv === null ? '1' : $pv;
		}

		return array($k, $v);
	}

	protected function _request_cookie_str($arr=array(), $url='') {
		$p = parse_url($url);
		$imp_cookies = array();

		if (is_array($arr)) foreach ($arr as $k => $v) {
			if (is_array($v)) {
				$add = true;
				if (isset($v['expires']) && $v['expires'] && $this->_is_expired($v['expires'])) $add = false;
				if (isset($v['path']) && !preg_match('#^'.preg_quote($v['path'], '#').'#', $p['path'])) $add = false;
				if (isset($v['httponly']) && !preg_match('#^http#', $p['scheme'])) $add = false;
				if (isset($v['secure']) && !preg_match('#^https#', $p['scheme'])) $add = false;

				if ($add && isset($v['value'])) $imp_cookies[] = rawurlencode($k).'='.rawurlencode($v['value']);
			}
		}

		return implode('; ', $imp_cookies);
	}

	protected function _is_expired($date) {
		$time = strtotime($date);
		return $time < time();
	}

	protected function _headers($extra_headers=array()) {
		if (is_object($extra_headers)) $extra_headers = (array)$extra_headers;
		if (!is_array($extra_headers)) throw new GoogleException('Extra headers are not in the correct format.', 10000, array('extra_headers' => $extra_headers));

		$out = array();
		foreach ($extra_headers as $k => $v) {
			if (is_scalar($v)) {
				if (is_numeric($k)) $out[] = $v;
				else $out[] = $k.': '.$v;
			}
		}

		return $out;
	}

	protected function _is_ssl($url) {
		return preg_match('#^https:#', $url);
	}

	protected function _ssl_check($url, $recache=false) {
		static $cache = array();
		$url = $this->_url($url, true);
		$uk = md5($url);
		if (!$recache && isset($cache[$uk])) return $cache[$uk];

		$out = true;

		$c = curl_init($url);
		$opts = array(
			CURLOPT_HTTPHEADER => $this->_headers(array(
				'accept' => 'Accept: text/html, application/xhtml+xml, */*',
				'Cache-Control' => 'no-cache',
				'Connection' => 'Keep-Alive',
				'Accept-Language' => 'en-us',
			)),
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_USERAGENT => $this->settings['login_url'],
		);

		curl_setopt_array($c, $opts);
		$res = curl_exec($c);
		$err = curl_errno($c);

		if ($err) {
			$out = false;
			$this->ssl_errno = $err;
			$this->ssl_errmsg = curl_error($c);
		}
		curl_close($c);

		return $cache[$uk] = $out;
	}

	protected function _url($url, $force_ssl=false, $force_non_ssl=false) {
		if (empty($url)) throw new GoogleException('Could not parse the empty url.', 10001, array('provided' => $url));
		$p = parse_url($url);
		if ($p === false) throw new GoogleException('Seriously malformed urls cannot be parsed.', 10002, array('provided' => $url));

		return ($force_ssl ? 'https' : ($force_non_ssl ? 'http' : (isset($p['scheme']) ? $p['scheme'] : 'http'))).'://'
			.(isset($p['user']) ? $p['user'].(isset($p['pass']) ? ':'.$p['pass'] : '').'@' : '') 
			.$p['host'].(isset($p['port']) ? ':'.$p['port'] : '') 
			.(isset($p['path']) ? $p['path'] : '/')
			.(isset($p['query']) ? '?'.$p['query'] : '');
	}

	protected function _pf($name) { // pre-processor formatting. most urls will require some basic replacements
		return $this->_url(h\_f(array(
			'8date' => date('Ymd'),
			'16digits' => h\_rd(16),
			'rint' => rand(5000000, 9810237),
		), $this->_f($name)));
	}

	protected function _f($name) { // url foramt
		if (!isset($this->url_formats[$name]))
			throw new GoogleException('The requested url format ['.$name.'], does not exist.', 20001, array('name' => $name, 'formats' => $this->url_formats));

		return $this->url_formats[$name];
	}
	
	protected function _sane_message($message, $condense=false) {
		$message = utf8_encode(self::edecode($message));
		// nl2br and encode
		if ($condense) $message = preg_replace('#(<br\s*?/?\s*?'.'>|\s)+#si', ' ', $message);
		else $message = preg_replace('#(<br\s*?/?\s*?'.'>|'."\n|\r|\r\n|\n\r".')#si', '%5Cn', $message);
		// escape specials
		$message = addslashes($message);
		// urlencode
		$message = rawurlencode($message);
		// misc extra replacements, because of potential breakages
		$message = str_replace(array('%0A%0A', '%0A', '%0D'), array('%20', '', '%5C'), $message);

		return $message;
	}

	protected static $entities = array('quot' => '&#34;', 'amp' => '&#38;', 'lt' => '&#60;', 'gt' => '&#62;', 'apos' => '&#39;', 'oelig' => '&#338;', 'oelig' => '&#339;', 'scaron' => '&#352;',
		'scaron' => '&#353;', 'yuml' => '&#376;', 'circ' => '&#710;', 'tilde' => '&#732;', 'ensp' => '&#8194;', 'emsp' => '&#8195;', 'thinsp' => '&#8201;', 'zwnj' => '&#8204;', 'zwj' => '&#8205;',
		'lrm' => '&#8206;', 'rlm' => '&#8207;', 'ndash' => '&#8211;', 'mdash' => '&#8212;', 'lsquo' => '&#8216;', 'rsquo' => '&#8217;', 'sbquo' => '&#8218;', 'ldquo' => '&#8220;', 'rdquo' => '&#8221;',
		'bdquo' => '&#8222;', 'dagger' => '&#8224;', 'dagger' => '&#8225;', 'permil' => '&#8240;', 'lsaquo' => '&#8249;', 'rsaquo' => '&#8250;', 'euro' => '&#8364;', 'fnof' => '&#402;', 'alpha' => '&#913;',
		'beta' => '&#914;', 'gamma' => '&#915;', 'delta' => '&#916;', 'epsilon' => '&#917;', 'zeta' => '&#918;', 'eta' => '&#919;', 'theta' => '&#920;', 'iota' => '&#921;', 'kappa' => '&#922;',
		'lambda' => '&#923;', 'mu' => '&#924;', 'nu' => '&#925;', 'xi' => '&#926;', 'omicron' => '&#927;', 'pi' => '&#928;', 'rho' => '&#929;', 'sigma' => '&#931;', 'tau' => '&#932;', 'upsilon' => '&#933;',
		'phi' => '&#934;', 'chi' => '&#935;', 'psi' => '&#936;', 'omega' => '&#937;', 'alpha' => '&#945;', 'beta' => '&#946;', 'gamma' => '&#947;', 'delta' => '&#948;', 'epsilon' => '&#949;',
		'zeta' => '&#950;', 'eta' => '&#951;', 'theta' => '&#952;', 'iota' => '&#953;', 'kappa' => '&#954;', 'lambda' => '&#955;', 'mu' => '&#956;', 'nu' => '&#957;', 'xi' => '&#958;',
		'omicron' => '&#959;', 'pi' => '&#960;', 'rho' => '&#961;', 'sigmaf' => '&#962;', 'sigma' => '&#963;', 'tau' => '&#964;', 'upsilon' => '&#965;', 'phi' => '&#966;', 'chi' => '&#967;',
		'psi' => '&#968;', 'omega' => '&#969;', 'thetasym' => '&#977;', 'upsih' => '&#978;', 'piv' => '&#982;', 'bull' => '&#8226;', 'hellip' => '&#8230;', 'prime' => '&#8242;', 'prime' => '&#8243;',
		'oline' => '&#8254;', 'frasl' => '&#8260;', 'weierp' => '&#8472;', 'image' => '&#8465;', 'real' => '&#8476;', 'trade' => '&#8482;', 'alefsym' => '&#8501;', 'larr' => '&#8592;', 'uarr' => '&#8593;',
		'rarr' => '&#8594;', 'darr' => '&#8595;', 'harr' => '&#8596;', 'crarr' => '&#8629;', 'larr' => '&#8656;', 'uarr' => '&#8657;', 'rarr' => '&#8658;', 'darr' => '&#8659;', 'harr' => '&#8660;',
		'forall' => '&#8704;', 'part' => '&#8706;', 'exist' => '&#8707;', 'empty' => '&#8709;', 'nabla' => '&#8711;', 'isin' => '&#8712;', 'notin' => '&#8713;', 'ni' => '&#8715;', 'prod' => '&#8719;',
		'sum' => '&#8721;', 'minus' => '&#8722;', 'lowast' => '&#8727;', 'radic' => '&#8730;', 'prop' => '&#8733;', 'infin' => '&#8734;', 'ang' => '&#8736;', 'and' => '&#8743;', 'or' => '&#8744;',
		'cap' => '&#8745;', 'cup' => '&#8746;', 'int' => '&#8747;', 'there4' => '&#8756;', 'sim' => '&#8764;', 'cong' => '&#8773;', 'asymp' => '&#8776;', 'ne' => '&#8800;', 'equiv' => '&#8801;',
		'le' => '&#8804;', 'ge' => '&#8805;', 'sub' => '&#8834;', 'sup' => '&#8835;', 'nsub' => '&#8836;', 'sube' => '&#8838;', 'supe' => '&#8839;', 'oplus' => '&#8853;', 'otimes' => '&#8855;',
		'perp' => '&#8869;', 'sdot' => '&#8901;', 'lceil' => '&#8968;', 'rceil' => '&#8969;', 'lfloor' => '&#8970;', 'rfloor' => '&#8971;', 'lang' => '&#9001;', 'rang' => '&#9002;', 'loz' => '&#9674;',
		'spades' => '&#9824;', 'clubs' => '&#9827;', 'hearts' => '&#9829;', 'diams' => '&#9830;', 'nbsp' => '&#160;', 'iexcl' => '&#161;', 'cent' => '&#162;', 'pound' => '&#163;', 'curren' => '&#164;',
		'yen' => '&#165;', 'brvbar' => '&#166;', 'sect' => '&#167;', 'uml' => '&#168;', 'copy' => '&#169;', 'ordf' => '&#170;', 'laquo' => '&#171;', 'not' => '&#172;', 'shy' => '&#173;',
		'reg' => '&#174;', 'macr' => '&#175;', 'deg' => '&#176;', 'plusmn' => '&#177;', 'sup2' => '&#178;', 'sup3' => '&#179;', 'acute' => '&#180;', 'micro' => '&#181;', 'para' => '&#182;',
		'middot' => '&#183;', 'cedil' => '&#184;', 'sup1' => '&#185;', 'ordm' => '&#186;', 'raquo' => '&#187;', 'frac14' => '&#188;', 'frac12' => '&#189;', 'frac34' => '&#190;', 'iquest' => '&#191;',
		'agrave' => '&#192;', 'aacute' => '&#193;', 'acirc' => '&#194;', 'atilde' => '&#195;', 'auml' => '&#196;', 'aring' => '&#197;', 'aelig' => '&#198;', 'ccedil' => '&#199;', 'egrave' => '&#200;',
		'eacute' => '&#201;', 'ecirc' => '&#202;', 'euml' => '&#203;', 'igrave' => '&#204;', 'iacute' => '&#205;', 'icirc' => '&#206;', 'iuml' => '&#207;', 'eth' => '&#208;', 'ntilde' => '&#209;',
		'ograve' => '&#210;', 'oacute' => '&#211;', 'ocirc' => '&#212;', 'otilde' => '&#213;', 'ouml' => '&#214;', 'times' => '&#215;', 'oslash' => '&#216;', 'ugrave' => '&#217;', 'uacute' => '&#218;',
		'ucirc' => '&#219;', 'uuml' => '&#220;', 'yacute' => '&#221;', 'thorn' => '&#222;', 'szlig' => '&#223;', 'agrave' => '&#224;', 'aacute' => '&#225;', 'acirc' => '&#226;', 'atilde' => '&#227;',
		'auml' => '&#228;', 'aring' => '&#229;', 'aelig' => '&#230;', 'ccedil' => '&#231;', 'egrave' => '&#232;', 'eacute' => '&#233;', 'ecirc' => '&#234;', 'euml' => '&#235;', 'igrave' => '&#236;',
		'iacute' => '&#237;', 'icirc' => '&#238;', 'iuml' => '&#239;', 'eth' => '&#240;', 'ntilde' => '&#241;', 'ograve' => '&#242;', 'oacute' => '&#243;', 'ocirc' => '&#244;', 'otilde' => '&#245;',
		'ouml' => '&#246;', 'divide' => '&#247;', 'oslash' => '&#248;', 'ugrave' => '&#249;', 'uacute' => '&#250;', 'ucirc' => '&#251;', 'uuml' => '&#252;', 'yacute' => '&#253;', 'thorn' => '&#254;',
		'yuml' => '&#255;'
	);

	public static function edecode($str) {
		return html_entity_decode(preg_replace_callback('#(&([a-z][^&;]*?);)#is', array(__CLASS__, 'edecode_replace'), $str), ENT_COMPAT, 'utf-8');
	}

	public static function edecode_replace($matches, $remove=true) {
		$key = strtolower($matches[2]);
		return isset(self::$entities[$key]) ? self::$entities[$key] : ( $remove ? '' : $matches[0] );
	}
}

class GoogleException extends \Exception {
	protected $data = array();

	public function __construct($message, $code='', $data=array(), Exception $previous=null) {
		$this->data = $data;
		parent::__construct($message, $code, $previous);
	}

	public function getData() {
		return $this->data;
	}
}

}
