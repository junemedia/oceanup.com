<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

// base smusher class
abstract class QS_Smusher {
	protected $raw = array();
	protected $errors = array();
	protected $srcs = array();
	protected $args = array();

	protected static $def_raw = array(
		'src' => '', // source file
		'src_size' => 0, // source file size
		'dest' => '', // destination file
		'dest_size' => 0, // destination file size
		'success' => false, // operation completed successfully
	);

	protected static $def_args = array(
		'overwrite' => false, // overwrite original file with resulting file?
		'segment' => '.smushed', // segement to add to the new filename, before the extension, if we are not overwriting the original file
	);

	public static function available($args=array()) {
		return false;
	}

	public static function supports_mime($mime_type) {
		return false;
	}

	public static function name() { return __CLASS__; }
	public static function requires() { return ''; }

	public function __construct($args='') {
		if (!empty($args))
			$this->set_args($args);
	}

	public function reset($hard=false) {
		$this->raw = $this->errors = $this->srcs = array();
		if ($hard) $this->args = array();
	}

	abstract protected function _process();

	public function smush($srcs=array(), $args='') {
		if (!empty($args)) 
			$this->set_args($args);

		if (!empty($srcs))
			$this->set_srcs($srcs);

		if ($this->_process())
			return $this->_response();

		return array();
	}

	public function set_args($args='') {
		$this->args = $this->_sane_args($args);
	}

	public function set_srcs($srcs=array()) {
		$this->srcs = is_array($srcs) ? $srcs : ( empty($srcs) ? array() : (array)$srcs );
	}

	public function get_debug() {}
	public function get_errors() { return $this->errors; }

	protected function _get_dest_file_name($src) {
		if ($this->args['overwrite']) {
			$new_path = $src;
		} else {
			$dir = dirname($src);
			$bn = basename($src);
			$bn = explode('.', $bn);
			$ext = array_pop($bn);
			$bn = implode('.', $bn);
			$new_path = $dir.'/'.$bn.$this->args['segment'].'.'.$ext;
		}

		return $new_path;
	}

	protected function _sane_args($args='') {
		return wp_parse_args($args, wp_parse_args($this->args, self::$def_args));
	}

	final protected function _response() {
		$out = array();

		foreach ($this->raw as $k => $item) {
			$out[$k] = apply_filters('qs-smusher-response', array(
				'src' => $item['src'],
				'src_size' => $item['src_size'],
				'dest' => $item['dest'],
				'dest_size' => $item['dest_size'],
				'save_bytes' => $item['src_size'] - $item['dest_size'],
				'save_perc' => $item['src_size'] != 0 ? 1 - ($item['dest_size'] / $item['src_size']) : 0,
				'optimizer' => get_class($this),
			), $item);
		}

		return $out;
	}

	protected function _add_error($code, $msg, $data) {
		$this->errors[] = new WP_Error($code, $msg, $data);
	}

	protected function _add_raw($data) {
		$data = wp_parse_args($data, self::$def_raw);
		if (empty($data['src']) || empty($data['src_size'])) {
			$this->_add_error('invalid_src_file', 'No source file or source file size.', $data);
			return false;
		}

		if ($data['success']) {
			if (empty($data['dest'])) $data['dest'] = $data['src'];
			if (empty($data['dest_size'])) $data['dest_size'] = $data['src_size'];
		}

		$k = basename($data['src']).'::'.$data['src_size'];
		$this->raw[$k] = $data;

		return true;
	}
}
