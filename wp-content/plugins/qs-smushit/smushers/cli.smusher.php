<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access
if (!defined('QS_FORCE_CLI')) define('QS_FORCE_CLI', false);

// only allow if running from commandline or forced to run with a constant
if ((!defined('PHP_SAPI') || PHP_SAPI != 'cli') || QS_FORCE_CLI)
	return;

require_once 'smusher.php';

class QS_CLI_Smusher extends QS_Smusher {
	protected static $is_available = false;
	protected static $versions = array(
		'jpegoptim' => '1.3.1',
		'optipng' => '0.7.5',
	);
	protected static $mimes = array(
		'image/jpeg' => 'jpegoptim',
		'image/gif' => 'optipng',
		'image/png' => 'optipng',
		'image/bmp' => 'optipng',
		'image/pnm' => 'optipng',
		'image/tiff' => 'optipng',
	);
	protected static $exec_func = '';
	protected static $paths = array(
		'jpegoptim' => '',
		'optipng' => '',
	);

	protected static $special = array(
		'jpegoptim-output-path' => './jpegoptim/',
	);

	protected function _process() {
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
			$func = self::_func_for_mime($iinfo['mime']);

			$new_path = $this->_get_dest_file_name($path);

			if (!is_writable(dirname($new_path))) {
				$this->_add_error('dest_file_permissions', 'Could not write to directory of new file.', $path);
				continue;
			}

			$path_size = filesize($path);

			if ($path == $new_path)
				clearstatcache(); // clear filesize cache, because we want to measure the second version of the file too.
			
			// filename may have changed, so store result to catch filename
			$res = $this->$func($path, $new_path);

			if ($res !== false) {
				$this->_add_raw(array(
					'src' => $path,
					'src_size' => $path_size,
					'dest' => $res,
					'dest_size' => filesize($res),
					'success' => true,
				));
			} else {
				$this->_add_raw(array(
					'src' => $path,
					'src_size' => $path_size,
					'dest' => $path,
					'dest_size' => $path_size,
					'success' => false,
				));
				$this->_add_error('not_optimized', 'The source file could not be optimized.', array('path' => $path, 'path_size' => $path_size, 'res' => $res));
			}
		}

		return count($this->raw) ? true : false;
	}

// do not do --strip-icc, because it fades the colors
// jpegoptim --strip-com --strip-exif --strip-iptc --strip-xmp <file> 
//
// optipng -o5 -strip all -clobber ../../../../../images/dev/images/2013/10/emmys-awards1-1.png
// Output file: ../../../../../images/dev/images/2013/10/emmys-awards1-1.png
// ../../../../../images/dev/images/2013/10/emmys-awards1-1.png is already optimized.
	protected function _optipng_run($src, $dest) {
		$basename = basename($src);
		$options = array(
			'-o3', // level 3 optimization, moderate number of trials
			'-strip all', // strip all meta, unneeded data, and extra colors
			'-clobber', // overwrite outfile, even if it exists already
		);
		// if not overwriting the existing file, then set the outfile destination path
		if ($dest != $src) $options[] = '-out '.$dest;

		// run the command
		$res = trim(self::_run(self::$paths['optipng'], $src, $options));
		
		// parse result
		if (preg_match('#^.* is already optimized\.$#s', $res)) {
			return false; // not optimized
		}

		// grab any output filenames, in case of extension change or if we asked for a separate file output
		$outfile = preg_replace('#^.*Output file: (.*?)(\n|\r).*$#s', '$1', $res);
		if ($outfile == $res) $outfile = '';

		// if we have an outfile name, and we did not ask for one, then we need to remove the old file, because the extension changed
		if ($outfile && $dest == $src) {
			@unlink($src);
			$dest = $outfile;
		}
		
		return $dest;
	}

	protected function _jpegoptim_run($src, $dest) {
		$basename = basename($src);
		$options = array(
			'-o', // overwrite
			'--strip-com',
			'--strip-exif',
			'--strip-iptc',
			'--strip-xmp',
		);
		// if we are not set to overwrite, we need to temp store the result file, then move it later to the new location
		if ($dest != $src) $options[] = '--dest='.self::$special['jpegoptim-output-path'];

		// run the command
		$res = trim(self::_run(self::$paths['jpegoptim'], $src, $options));
		
		// parse result
		if (!preg_match('#.*optimized\.$#', $res)) {
			return false; // not optimized
		}

		// maybe move file
		if ($dest != $src && !@rename(self::$special['jpegoptim-output-path'].$basename, $dest)) {
			@unlink(self::$special['jpegoptim-output-path'].$basename);
			return false;
		}
		
		return $dest;
	}

	public function set_srcs($srcs=array()) {
		parent::set_srcs($srcs);
		$this->srcs = array_map('realpath', $this->srcs);
	}

	public function get_debug() {
		return array(
			'paths' => self::$paths,
			'exec_func' => self::$exec_func,
		);
	}

	public static function name() { return 'CLI Tools'; }

	public static function requires() {
		return 'the JPEGOptim ('
			.self::$versions['jpegoptim']
			.'or later) and OptiPNG ('
			.self::$versions['optipng']
			.'0.7.5 or later) commandline tools must be installed, and the `shell_exec` or `passthru` functions must be enabled. (Linux only))';
	}

	protected static function _func_for_mime($mime) {
		$base = self::$mimes[$mime];
		return '_'.$base.'_run';
	}

	public static function supports_mime( $mime_type ) {
		$mime_type = trim(strtolower($mime_type));
		return isset(self::$mimes[$mime_type]);
	}

	public static function available( $args = array() ) {
		// cached
		if (self::$is_available) return true;

		self::_exec_enabled();
		if (empty(self::$exec_func))
			return false;

		if (!self::_valid_jpegoptim())
			return false;

		if (!self::_valid_optipng())
			return false;

		self::$is_available = true;
		return true;
	}

	protected static function _run($cmd, $file='', $options='') {
		static $func = false;
		//static $obuf = array('passthru' => true);
		if ($func === false && self::$exec_func) $func = self::$exec_func;
		if (!$func) return '';

		$options = !is_scalar($options) || trim($options) != '' ? (array)$options : array();
		$final_command = $cmd.' '.implode(' ', $options).' '.$file.' 2>&1';

		ob_start();
		$out1 = $func($final_command);
		$out = ob_get_contents();
		ob_end_clean();
		return $out1.$out;
	}

	protected static function _exec_enabled() {
		foreach (array('shell_exec', 'passthru') as $func)
			if (function_exists($func)) {
				self::$exec_func = $func;
				break;
			}
	}

/*
OptiPNG version 0.7.5
jpegoptim v1.3.1  x86_64-unknown-linux-gnu
*/
	protected static function _valid_optipng() {
		// make sure the command exists. if not, fail
		$output = trim(self::_run('which', 'optipng'));
		if (empty($output) || !file_exists($output))
			return false;

		// set the command path
		self::$paths['optipng'] = $output;

		// check version of command. if not minimum, then fail
		$output = trim(self::_run(self::$paths['optipng'], '', '--version'));
		if (($version = preg_replace('#.*OptiPNG version ([\d\.]+).*#s', '$1', $output)) == $output || version_compare(self::$versions['optipng'], $version) > 0)
			return false;

		// otherwise pass
		return true;
	}

	protected static function _valid_jpegoptim() {
		// make sure the command exists. if not, fail
		$output = trim(self::_run('which', 'jpegoptim'));
		if (empty($output) || !file_exists($output))
			return false;

		// set the command path
		self::$paths['jpegoptim'] = $output;

		// check version of command. if not minimum, then fail
		$output = trim(self::_run(self::$paths['jpegoptim'], '', '--version'));
		if (($version = preg_replace('#.*jpegoptim v([\d\.]+).*#s', '$1', $output)) == $output || version_compare(self::$versions['jpegoptim'], $version) > 0)
			return false;

		// make temp folder for jpegoptim output, since there is no way to specify output filename currently, only output directory
		if (!file_exists(self::$special['jpegoptim-output-path'])) @mkdir(self::$special['jpegoptim-output-path']);
		self::$special['jpegoptim-output-path'] = trailingslashit(realpath(self::$special['jpegoptim-output-path']));
		if (!is_writable(self::$special['jpegoptim-output-path']))
			return false;

		// otherwise pass
		return true;
	}
}
