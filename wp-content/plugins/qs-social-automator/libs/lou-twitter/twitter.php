<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

require_once 'tmhOAuth'.DIRECTORY_SEPARATOR.'tmhOAuth.php';

if (!class_exists('lou_twitter_oauth')):
class lou_twitter_oauth extends tmhOAuth {
	protected $api_url_format = 'https://api.twitter.com/1.1/:endpoint:';
	protected $default_endpoint = array(
		'method' => 'GET',
		'replacements' => array(),
		'useauth' => true,
		'multipart' => false,
	);
	protected $endpoints = array(
		'statuses/update_with_media' => array( 'method' => 'POST', 'multipart' => true ),
		'statuses/update' => array( 'method' => 'POST' ),
		'statuses/destroy/:id:' => array( 'method' => 'POST', 'replacements' => array( 'id' => 'id' ) ),
	);

	public function api($endpoint, $fields=array()) {
		$endp = $this->_get_endpoint($endpoint);
		$replacements = array();
		foreach ($endp['replacements'] as $k => $v) {
			if (!isset($fields[$v]))
				throw new Exception('Missing required endpoint field ['.$v.'] from field list.', 8372);
			$replacements[':'.$k.':'] = $fields[$v];
			unset($fields[$v]);
		}
		$endpoint = str_replace(array_keys($replacements), array_values($replacements), $endpoint);

		return $this->request($endp['method'], $this->url('1.1/'.$endpoint), $fields, $endp['useauth'], $endp['multipart']);
	}

	protected function _get_endpoint($endpoint) {
		$ep = isset($this->endpoints[$endpoint]) ? $this->endpoints[$endpoint] : $this->default_endpoint;
		return array_merge($this->default_endpoint, $ep);
	}
}
endif;
