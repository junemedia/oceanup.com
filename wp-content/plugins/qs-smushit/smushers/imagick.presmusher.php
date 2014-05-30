<?php (__FILE__ == $_SERVER['SCRIPT_FILENAME']) ? die(header('Location: /')) : null; // prevent direct access

class QS_Imagick extends WP_Image_Editor_Imagick {
	// required, because of scoping. copied directly from core class.
	/**
	 * Saves current image to file.
	 *
	 * @since 3.5.0
	 * @access public
	 *
	 * @param string $destfilename
	 * @param string $mime_type
	 * @return array|WP_Error {'path'=>string, 'file'=>string, 'width'=>int, 'height'=>int, 'mime-type'=>string}
	 */
	public function save( $destfilename = null, $mime_type = null ) {
		$saved = $this->_save( $this->image, $destfilename, $mime_type );

		if ( ! is_wp_error( $saved ) ) {
			$this->file = $saved['path'];
			$this->mime_type = $saved['mime-type'];

			try {
				$this->image->setImageFormat( strtoupper( $this->get_extension( $this->mime_type ) ) );
			}
			catch ( Exception $e ) {
				return new WP_Error( 'image_save_error', $e->getMessage(), $this->file );
			}
		}

		return $saved;
	}

	// modified to add stripImage() and qspsm marker
	protected function _save( $image, $filename = null, $mime_type = null ) {
		list( $filename, $extension, $mime_type ) = $this->get_output_format( $filename, $mime_type );

		if ( ! $filename )
			$filename = $this->generate_filename( null, null, $extension );

		try {
			// Store initial Format
			$orig_format = $this->image->getImageFormat();

			$this->image->setImageFormat( strtoupper( $this->get_extension( $mime_type ) ) );
			//$this->image->stripImage(); // added LOUSHOU
			$this->make_image( $filename, array( $image, 'writeImage' ), array( $filename ) );

			// Reset original Format
			$this->image->setImageFormat( $orig_format );
		}
		catch ( Exception $e ) {
			return new WP_Error( 'image_save_error', $e->getMessage(), $filename );
		}

		// Set correct file permissions
		$stat = stat( dirname( $filename ) );
		$perms = $stat['mode'] & 0000666; //same permissions as parent folder, strip off the executable bits
		@ chmod( $filename, $perms );

		/** This filter is documented in wp-includes/class-wp-image-editor-gd.php */
		return array(
			'path'      => $filename,
			'file'      => wp_basename( apply_filters( 'image_make_intermediate_size', $filename ) ),
			'width'     => $this->size['width'],
			'height'    => $this->size['height'],
			'mime-type' => $mime_type,
			'qspsm'      => 'v3-pre:'.filesize($filename),
		);
	}

	public static function add_self($list) {
		foreach ($list as $ind => $item)
			if ($item == 'WP_Image_Editor_Imagick')
				$list[$ind] = __CLASS__;
		return $list;
	}
}

add_filter('qs-smush-presmushers', array('QS_Imagick', 'add_self'), PHP_INT_MAX, 1);
