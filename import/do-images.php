<?php
include 'wp-load.php';
if( !function_exists( 'wp_handle_upload' ) ) include_once 'wp-admin/includes/file.php';

/*
_files (
	['single'] => array(
		[name] => 20130729_082723_Riot_Bat.jpg
		[type] => image/jpeg
		[tmp_name] => /tmp/phpkOZlio
		[error] => 0
		[size] => 277340
	)
)
*/


// real images folder
$dir = opendir( dirname(__FILE__) . '/images' );
$i = 0;
$files = array();
$path = dirname(__FILE__) . '/images/' ;

function do_file( $file ) {
	if( empty($file['name']) ) die( 'empty file name');
	//print_r( $file );
	$over = array( 'test_form' => false );
	$timestamp = filemtime( $file['tmp_name'] );
	$dt = date( 'Y/m', $timestamp);
	//echo "$dt\n";
	$url = wp_handle_upload( $file, $over, $dt );
	do_post($url);
}

/*
	// $url = array(
    [file] => /home/tsmith/www/wordpress-3.6/wp-content/uploads/2010/12/hometsmithwwwwordpress-3.6img.jpg
	[url] => http://current/wp-content/uploads/2010/12/hometsmithwwwwordpress-3.6img.jpg
	[type] => image/jpeg
	);

*/
function do_post( $url ) {
	$wp_filetype = $url['type'];
	$filename = $url['file'];
	$wp_upload_dir = wp_upload_dir();
	$attachment = array(
		'guid' => $wp_upload_dir['url'] . '/' . basename( $filename ), 
		'post_mime_type' => $wp_filetype,
		'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
		'post_content' => '',
		'post_status' => 'inherit'
	);
	$attach_id = wp_insert_attachment( $attachment, $url['file'] );
	// you must first include the image.php file
	// for the function wp_generate_attachment_metadata() to work
	require_once(ABSPATH . 'wp-admin/includes/image.php');
	$attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
	wp_update_attachment_metadata( $attach_id, $attach_data );
}

// read an input dir, generate array like $_FILES
while( false !== ( $entry = readdir( $dir ) ) ) {
	if( $entry !== '.' && $entry !== '..' ) {
		$i++;
		//echo "$i : $entry\n";
		$sz = filesize( $path . $entry );
		$tp = image_type_to_mime_type( exif_imagetype( $path . $entry ) );
		$f = $path . $entry;
		$files[] = array( 
			'name' => "$entry",
			'type' => $tp,
			'tmp_name' => "$f",
			'error' => 0,
			'size' => $sz,
		);
	}
}
closedir($dir);

// loop over that array, and do wp_handle_upload, which will call do_post and add an attachment
foreach( $files as $file ) {
	if( is_array( $file ) && !empty( $file ) ) {
		do_file($file);
	} else {
		echo "file is empty";
	}
}


