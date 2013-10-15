<?php
include 'wp-load.php';
// the-images.txt is the liest of files from /images/ output by tar -tf images.tar
// the-post-names.txt is the files from the above list, sanitized and removed file ext
// next is to find fid for each image and add it as postmeta to the attachment post.
$arr = file('the-images.txt');
$arr = array_reverse($arr);
//print_r($arr);
$i = 0;

foreach( $arr as $row ) {
	$proper = sanitize_title_with_dashes ( $row );

	$tmp = explode( '-', $proper );
	array_pop( $tmp );
	$proper = implode( '-', $tmp);
	echo "" . $proper . "\n";
	//echo substr( $proper, -3 ) . "\n";
	$i++;
	//if( $i > 40 ) break;
}


/*
$path = dirname(__FILE__) . '/images/' ;
$dir = opendir( $path );
$i = 0;
$files = array();

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
*/

/*
// a form w/ a file upload
<form method="post" enctype="multipart/form-data">
<input type="file" name="single" />
<input type="submit" name="submit" />
</form>
*/
?>
<?php
/* // get yyyy/mm date from file
$the_date = filemtime( './mileylesbian_1.jpg' );
echo date('Y/m', $the_date) . "\n";
*/

/* // get tables and count(*)s
$sql = "show tables from drupal";

$tables = $wpdb->get_results($sql);
$out = array();

echo "<pre>\n";
foreach($tables as $table) {
	$table_name = $table->Tables_in_drupal;
	
	$q = "select count(*) cnt from drupal.{$table_name}";
	//echo "$q\n";

	$cnt = $wpdb->get_row($q);
	//var_dump($cnt);
	$out[$table_name] = $cnt;
}
//print_r($out);
echo "</pre>\n";
echo "<table>\n";
foreach( $out as $key => $val ) {
	echo "<tr><td>$key</td><td>{$val->cnt}</td></tr>\n";
}
echo "</table>\n";
*/


