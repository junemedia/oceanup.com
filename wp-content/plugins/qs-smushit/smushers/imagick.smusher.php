<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

require_once 'smusher.php';

class QS_Imagick_Smusher extends QS_Smusher {
	protected static $is_available = false;

	protected $image = false;

	public function __construct($args='') {
		parent::__construct($args);

		if (self::available())
			$this->image = new Imagick();
	}

	protected function _process() {
		if (!$this->image) return false; // not initialized
		if (empty($this->srcs)) return false; // none to process
		
		foreach ($this->srcs as $path) {
			if (!is_readable($path)) {
				$this->_add_error('src_file_permissions', 'Could not read source file.', $path);
				continue;
			}

			$iinfo = @getimagesize($path);
			if (!is_array($iinfo)) {
				$this->_add_error('src_file_not_image', 'The source file is not an image file.', $path);
				continue;
			}

			if (!self::supports_mime($iinfo['mime'])) {
				$this->_add_error('src_file_format_unsupported', 'The file format of the source file, is not supported by the '.self::name().' Smusher.', array('path' => $path, 'info' => $iinfo));
				continue;
			}

			$new_path = $this->_get_dest_file_name($path);

			if (!is_writable(dirname($new_path))) {
				$this->_add_error('dest_file_permissions', 'Could not write to directory of new file.', $path);
				continue;
			}

			$path_size = filesize($path);

			try {
				$res = $this->_process_file($path, $path_size, $new_path);

				if (!is_readable($new_path)) {
					$this->_add_error('dest_file_not_found', 'After Smushing, we could not find the destination file.', array('file_info' => array($path, $path_size, $new_path)));
					continue;
				}

				$this->_add_raw(array(
					'src' => $path,
					'src_size' => $path_size,
					'dest' => $new_path,
					'dest_size' => filesize($new_path),
					'success' => $res,
				));
			} catch(Exception $e) {
				$this->_add_error('imagick_exception', 'Unexpected exception from Imagick class.', array('exception' => $e, 'file_info' => array($path, $path_size, $new_path)));
			}
		}

		return count($this->raw) ? true : false;
	}

	protected function _process_file($src, $src_size, $dest) {
		$this->image->clear();
		$test_filename = dirname($dest).'/'.md5($dest);

		$this->image->readImage($src);
		$size = array( 'w' => $this->image->getImageWidth(), 'h' => $this->image->getImageHeight() );
		if (empty($size['w']) || empty($size['h'])) throw new Exception('Invalid image size.'.@json_encode($size), 50009);

		$this->image->resizeImage($size['w'], $size['h'], imagick::FILTER_CUBIC, 1);
		$this->image->stripImage();

		// never ever increase size
		$this->image->writeImage($test_filename);
		$file_size = filesize($test_filename);
		// if larger, fail
		if ($file_size > $src_size) {
			@unlink($test_filename);
			$this->image->clear();
			return false;
		}

		// if cannot move file to proper location, fail
		if (!@rename($test_filename, $dest)) {
			@unlink($test_filename);
			return false;
		}

		// success!
		$this->image->clear();
		return true;
	}

	public function set_srcs($srcs=array()) {
		parent::set_srcs($srcs);
		$this->srcs = array_map('realpath', $this->srcs);
	}

	public static function name() { return 'ImageMagick'; }

	public static function requires() {
		return 'the Imagick PHP Library must be installed.';
	}

	// support function stolen from /wp-includes/class-wp-image-editor-imagick.php
	/**
	 * Checks to see if editor supports the mime-type specified.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $mime_type
	 * @return boolean
	 */
	public static function supports_mime( $mime_type ) {
		$imagick_extension = strtoupper( self::get_extension( $mime_type ) );

		if ( ! $imagick_extension )
			return false;

		// setIteratorIndex is optional unless mime is an animated format.
		// Here, we just say no if you are missing it and aren't loading a jpeg.
		if ( ! method_exists( 'Imagick', 'setIteratorIndex' ) && $mime_type != 'image/jpeg' )
				return false;

		try {
			return ( (bool) @Imagick::queryFormats( $imagick_extension ) );
		}
		catch ( Exception $e ) {
			return false;
		}
	}

	// support function stolen from /wp-includes/class-wp-image-editor-imagick.php
	/**
	 * Checks to see if current environment supports Imagick.
	 *
	 * We require Imagick 2.2.0 or greater, based on whether the queryFormats()
	 * method can be called statically.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @return boolean
	 */
	public static function available( $args = array() ) {
		// cached
		if (self::$is_available) return true;

		// First, test Imagick's extension and classes.
		if ( ! extension_loaded( 'imagick' ) || ! class_exists( 'Imagick' ) || ! class_exists( 'ImagickPixel' ) )
			return false;

		if ( version_compare( phpversion( 'imagick' ), '2.2.0', '<' ) )
			return false;

		$required_methods = array(
			'clear',
			'destroy',
			'valid',
			'getimage',
			'writeimage',
			'getimageblob',
			'getimagegeometry',
			'getimageformat',
			'setimageformat',
			'setimagecompression',
			'setimagecompressionquality',
			'setimagepage',
			'scaleimage',
			'cropimage',
			'rotateimage',
			'flipimage',
			'flopimage',
			'stripimage', // added LOUSHOU
		);

		// Now, test for deep requirements within Imagick.
		if ( ! defined( 'imagick::COMPRESSION_JPEG' ) )
			return false;

		if ( array_diff( $required_methods, get_class_methods( 'Imagick' ) ) )
			return false;

		self::$is_available = true;
		self::_queue_presmusher();
		return true;
	}

	/**
	 * Returns first matched extension from Mime-type,
	 * as mapped from wp_get_mime_types()
	 *
	 * @since 3.5.0
	 * @access protected
	 *
	 * @param string $mime_type
	 * @return string|boolean
	 */
	protected static function get_extension( $mime_type = null ) {
		$extensions = explode( '|', array_search( $mime_type, wp_get_mime_types() ) );

		if ( empty( $extensions[0] ) )
			return false;

		return $extensions[0];
	}

	protected static function _queue_presmusher() {
		add_filter('wp_image_editors', array(__CLASS__, 'load_presmusher'), 0, 1);
		//add_action('admin_notices', function() { echo '<div class="updated"><p>Imagick Installed!</p></div>'; }, 10);
	}

	public static function load_presmusher($list) {
		require_once 'imagick.presmusher.php';
		return $list;
	}
}
