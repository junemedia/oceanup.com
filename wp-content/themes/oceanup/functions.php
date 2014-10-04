<?php

function qsou_extra_widget_areas() {
	register_sidebar(array(
		'name' => 'Header Widgets Top',
		'id' => 'oceanup-header-widgets-top',
		'description' => __( 'Widgets in this area will be shown on the header/top widget area.' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => 'Header Widgets Bottom',
		'id' => 'oceanup-header-widgets-bottom',
		'description' => __( 'Widgets in this area will be shown on the header/bottom widget area.' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar( array(
		'name' => __( 'Top Sidebar Ad', 'woothemes' ),
		'id' => 'top-sidebar-ad-area',
		'description' => __( 'Top of double sidebar, usually to hold ads.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );
	register_sidebar( array(
		'name' => __( 'Bottom Bar', 'woothemes' ),
		'id' => 'bottom-widget-area',
		'description' => __( 'Bottom bar, usually to hold ads.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );
	
	register_sidebar( array(
		'name' => __( 'Home 2nd post ad', 'woothemes' ),
		'id' => 'home-river',
		'description' => __( 'To display an ad inline in homepage post river.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );
	register_sidebar( array(
		'name' => __( 'Tag Archives 2nd post ad', 'woothemes' ),
		'id' => 'tag-river',
		'description' => __( 'To display an ads inline in tag archive  post river.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );

	register_sidebar( array(
		'name' => __( 'Home Taboola', 'woothemes' ),
		'id' => 'home-taboola',
		'description' => __( 'To display a taboola widget after first post on home post river.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );
	register_sidebar( array(
		'name' => __( 'Tags Taboola', 'woothemes' ),
		'id' => 'tag-taboola',
		'description' => __( 'To display a taboola widget after first post on  tag/archive post river.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );


}
qsou_extra_widget_areas();
	
function qsou_extra_widget_areas_special() {
	/**** POSTS ONLY ****/
	register_sidebar(array(
		'name' => 'Posts - Below Post Text',
		'id' => 'post-content-widget-area',
		'description' => __( 'Shows below post content, and above post comments.' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));

	/**** PHOTOS PAGE SPECIFIC ******/
	register_sidebar(array(
		'name' => 'Photos - Header Widgets Top',
		'id' => 'oceanup-header-widgets-top-photos',
		'description' => __( 'Widgets in this area will be shown on the header/top widget area, on the photos pages.' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar(array(
		'name' => 'Photos - Header Widgets Bottom',
		'id' => 'oceanup-header-widgets-bottom-photos',
		'description' => __( 'Widgets in this area will be shown on the header/bottom widget area, on the photos pages.' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	));
	register_sidebar( array(
		'name' => __( 'Photos - Top Sidebar Ad', 'woothemes' ),
		'id' => 'top-sidebar-ad-area-photos',
		'description' => __( 'Top of double sidebar, on the photos pages, usually to hold ads.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );
	register_sidebar( array(
		'name' => __( 'Photos - Bottom Bar', 'woothemes' ),
		'id' => 'bottom-widget-area-photos',
		'description' => __( 'Bottom bar, on the photos pages, usually to hold ads.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );

	register_sidebar( array(
		'name' => __( 'Photos - Under Carousel', 'woothemes' ),
		'id' => 'under-carousel-photos',
		'description' => __( 'Box under gallery carousel, for taboola.', 'woothemes' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3>',
		'after_title' => '</h3>'
	) );

		// Widgetized sidebars
	    register_sidebar( array( 'name' => __( 'Photos - Primary', 'woothemes' ), 'id' => 'primary-photos', 'description' => __( 'The default primary sidebar for your website, used in two or three-column layouts.', 'woothemes' ), 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) );
	    register_sidebar( array( 'name' => __( 'Photos - Secondary', 'woothemes' ), 'id' => 'secondary-photos', 'description' => __( 'A secondary sidebar for your website, used in three-column layouts.', 'woothemes' ), 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) );
	
		// Footer widgetized areas
		$total = get_option( 'woo_footer_sidebars', 4 );
		if ( ! $total ) $total = 4;
		for ( $i = 1; $i <= intval( $total ); $i++ ) {
			register_sidebar( array( 'name' => sprintf( __( 'Photos - Footer %d', 'woothemes' ), $i ), 'id' => sprintf( 'footer-%d-photos', $i ), 'description' => sprintf( __( 'Widgetized Footer Region %d.', 'woothemes' ), $i ), 'before_widget' => '<div id="%1$s" class="widget %2$s">', 'after_widget' => '</div>', 'before_title' => '<h3>', 'after_title' => '</h3>' ) );
		}
}
add_action('init', 'qsou_extra_widget_areas_special', 11);

if ( ! function_exists( 'woo_post_meta' ) ) {
	function woo_post_meta() {
		if ( is_page() ) { return; }

		$post_info = '<span class="small">'
			. __( 'By', 'woothemes' )
			. '</span>&nbsp; [post_author_posts_link] <span class="small">'
			. _x( 'on', 'post datetime', 'woothemes' )
			. '</span>&nbsp; [post_date]&nbsp; <span class="small">';
		printf( '<div class="post-meta">%s</div>' . "\n", apply_filters( 'woo_filter_post_meta', $post_info ) );

	} // End woo_post_meta()
}

if (!function_exists('woo_postnav')) {
	function woo_postnav() {
	}
}

if ( ! function_exists( 'oceanup_nav_social' ) ) {
	function oceanup_nav_social() {
		echo '<div id="social-nav-wrapper" class="box left">';
		wp_nav_menu( array( 'sort_column' => 'menu_order', 'container' => 'ul', 'menu_id' => 'social-nav', 'menu_class' => 'nav fl', 'theme_location' => 'social-menu' ) );
		echo '</div>';
	}
}
add_action( 'woo_nav_after', 'oceanup_nav_social', 30 );
register_nav_menu( 'social-menu', 'Social Menu' );

// Tag Drop down
if ( ! function_exists( 'oceanup_tag_dropdown' ) ){
	function oceanup_tag_dropdown(){
		echo '<div id="star-hunt-container" class="box left"><a href="#" id="flexcroll-toggle">Star Hunt</a><span class="arrow"></span><div class="flexcroll" id="flex-list">';
		$tags = get_tags();
		foreach ( $tags as $tag ) {
			$tag_link = get_tag_link( $tag->term_id );
			echo "<a href='{$tag_link}' title='{$tag->name} Tag' class='{$tag->slug}'>{$tag->name}</a>";
		}
		echo '</div></div>';
	}
}
add_action( 'woo_nav_after', 'oceanup_tag_dropdown', 40 );

// Search Box
if ( ! function_exists( 'oceanup_search_field' ) ) {
	function oceanup_search_field() {
		echo '<div id="search-wrapper" class="box right">';
		include_once(locate_template('search-form.php'));
		echo '</div>';
	}
}
add_action( 'woo_nav_after', 'oceanup_search_field', 50 );

// Header Widget Areas
if ( ! function_exists( 'oceanup_header_widget_areas' ) ) {
	function oceanup_header_widget_areas() {
		$barsuffix = '';
		if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';

		foreach (array('oceanup-header-widgets-top', 'oceanup-header-widgets-bottom') as $barname) {
			if (is_active_sidebar($barname.$barsuffix)) {
				echo '<aside id="'.$barname.'" class="clearfix col-full">';
				dynamic_sidebar( $barname.$barsuffix );
				echo '</aside>';
			} elseif (is_active_sidebar($barname)) {
				echo '<aside id="'.$barname.'" class="clearfix col-full">';
				dynamic_sidebar( $barname );
				echo '</aside>';
			}
		}
	}
}
add_action( 'woo_header_after', 'oceanup_header_widget_areas' );

if ( ! function_exists( 'oceanup_register_header_widget_areas' ) ) {
	function oceanup_register_header_widget_areas(){
	}
}
add_action( 'widgets_init', 'oceanup_register_header_widget_areas' );

// Gallery/Attachments
if ( ! function_exists( 'oceanup_attachment_title' ) ) {
	function oceanup_attachment_title( $_post_title = '', $_post_id = 0 ){
		if (empty($_post_id)) $_post_id = $GLOBALS['post']->ID;
		if ( get_post_type($_post_id) == 'attachment' ){
			$parent = get_post($GLOBALS['post']->post_parent);
			$_post_title = $parent->post_title;
		}
		return $_post_title;
	}
}
add_filter( 'the_title', 'oceanup_attachment_title', 10, 2 );

if ( ! function_exists( 'oceanup_attachment_content' ) ) {
	function oceanup_attachment_content( $_post_content = '', $_post_id = 0 ){
		if (empty($_post_id)) $_post_id = $GLOBALS['post']->ID;
		if ( get_post_type($_post_id) == 'attachment' ){
			$_post_content = '<p class="attachment">'.wp_get_attachment_link(0, 'full', false).'</p>';
		}
		return $_post_content;
	}
}
add_filter( 'the_content', 'oceanup_attachment_content', 10, 2 );

// Post Meta
if ( ! function_exists('oceanup_get_previous_image_link') ){
	function oceanup_get_previous_image_link(){
		$args = func_get_args();
		ob_start();
		call_user_func_array('previous_image_link', $args);
		return ob_get_clean();
	}
}
if ( ! function_exists('oceanup_get_next_image_link') ){
	function oceanup_get_next_image_link(){
		$args = func_get_args();
		ob_start();
		call_user_func_array('next_image_link', $args);
		return ob_get_clean();
	}
}
if ( ! function_exists( 'oceanup_post_meta' ) ) {
	function oceanup_post_meta( $_post_info = '' ){
		
		if ( get_post_gallery() ){
			$images =& get_children( 'post_type=attachment&post_mime_type=image&post_parent=' . $GLOBALS['post']->ID );
			$first_image = array_pop($images);
			$_post_info .= '<span class="view-photo-gallery"><a href="'.get_permalink($first_image->ID).'">View Photo Gallery</a></span>';
		}
		if ( get_post_type($GLOBALS['post']->ID) == 'attachment' ){
			$_post_info .= '
				<nav id="image-navigation" class="navigation" role="navigation">
					<span class="previous-image">'.oceanup_get_previous_image_link( 'full', 'Previous' ).'</span>
					<span class="next-image">'.oceanup_get_next_image_link( 'full', 'Next').'</span>
				</nav><!-- #image-navigation -->';
		}
		return $_post_info;
	}
}
//add_filter( 'woo_filter_post_meta', 'oceanup_post_meta' );

// Enqueue custom JS for our child theme
if (!function_exists('oceanup_enqueue_scripts')){
	function oceanup_enqueue_scripts(){
		wp_register_script(
			'oceanup-js',
			get_stylesheet_directory_uri() . '/js/oceanup.js',
			array('jquery', 'prettyPhoto'),
			'1.0.6',
			true
		);

		wp_enqueue_script( 'oceanup-js' );
	}
}
add_action( 'woo_head', 'oceanup_enqueue_scripts' );

//Global options setup
function oceanup_global_options(){
	// Populate WooThemes option in array for use in theme
	global $woo_options;
	$woo_options = get_option( 'woo_options' );
	$woo_options['woo_enable_lightbox'] = 'true';
}
add_action( 'init', 'oceanup_global_options', 200 );

// Redefine some functions
if ( ! function_exists( 'woo_nav' ) ) {
	function woo_nav() {
	}
}
if ( ! function_exists( 'oceanup_nav' ) ) {
	function oceanup_nav(){
		global $woo_options;
		woo_nav_before();
	?>
	<nav id="navigation" role="navigation" class="box left">
		<section class="menus">
		<?php woo_nav_inside(); ?>
		</section><!-- /.menus -->
		<a href="#top" class="nav-close"><span><?php _e('Return to Content', 'woothemes' ); ?></span></a>
	</nav>
	<?php
		woo_nav_after();
	} // End woo_nav_oceanup()
}
add_action( 'woo_header_inside', 'oceanup_nav', 20 );

if ( ! function_exists( 'woo_logo' ) ) {
function woo_logo () {
	$settings = woo_get_dynamic_values( array( 'logo' => '' ) );
	// Setup the tag to be used for the header area (`h1` on the front page and `span` on all others).
	$heading_tag = 'span';
	if ( is_home() || is_front_page() ) { $heading_tag = 'h1'; }

	// Get our website's name, description and URL. We use them several times below so lets get them once.
	$site_title = get_bloginfo( 'name' );
	$site_url = home_url( '/' );
	$site_description = get_bloginfo( 'description' );
?>
<div id="logo" class="box left">
<?php
	// Website heading/logo and description text.
	if ( ( '' != $settings['logo'] ) ) {
		$logo_url = $settings['logo'];
		if ( is_ssl() ) $logo_url = str_replace( 'http://', 'https://', $logo_url );

		echo '<a href="' . esc_url( $site_url ) . '" title="' . esc_attr( $site_description ) . '"><img src="' . esc_url( $logo_url ) . '" alt="' . esc_attr( $site_title ) . '" /></a>' . "\n";
	} // End IF Statement

	echo '<' . $heading_tag . ' class="site-title"><a href="' . esc_url( $site_url ) . '">' . $site_title . '</a></' . $heading_tag . '>' . "\n";
	if ( $site_description ) { echo '<span class="site-description">' . $site_description . '</span>' . "\n"; }
?>
</div>
<?php
} // End woo_logo()
}

// stuff from tsmith functions.php
if( !function_exists( 'oc_add_embed_img' ) ) {
	function oc_add_embed_img( $atts = 0 ) {
		if( is_array( $atts ) ) {
			//do stuff
			$id = array_shift( $atts );
			$img = wp_get_attachment_image( $id, 'large' );
			echo '<div class="from-legacy-table">' . $img . '</div>';
		}
	}
	add_shortcode( 'oc_add_embed_img', 'oc_add_embed_img');
}


// function to add CPT to site
function oc_set_post_types() {
	$labels = array(
		'name' => 'Gallery',
		'singular_name' => 'Gallery',
		'add_new' => 'Add new gallery',
		'add_new_item' => 'Add New Book',
		'edit_item' => 'Edit Gallery',
		'new_item' => 'New Gallery',
		'all_items' => 'All Galleries',
		'view_item' => 'View Gallery',
		'search_items' => 'Search Galleries',
		'not_found' =>  'No galleries found',
		'not_found_in_trash' => 'No galleries found in Trash', 
		'parent_item_colon' => '',
		'menu_name' => 'Galleries'
	);
	$args = array(
		'labels' => $labels,
		'public' => true,
		'publicly_queryable' => true,
		'show_ui' => true, 
		'show_in_menu' => true, 
		'query_var' => true,
		'rewrite' => array( 'slug' => 'gallery' ),
		'capability_type' => 'post',
		'has_archive' => true, 
		'hierarchical' => false,
		'menu_position' => null,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'comments' )
	); 
	register_post_type( 'oc_gallery', $args );
}

// call the previously defined function
add_action( 'init', 'oc_set_post_types' );

/**
 * Prints the attached image with a link to the next attached image.
 *
 * @since Twenty Thirteen 1.0
 *
 * @return void
 */
function twentythirteen_the_attached_image() {
	$post                = get_post();
	var_dump( $post );
	$attachment_size     = apply_filters( 'twentythirteen_attachment_size', array( 724, 724 ) );
	$next_attachment_url = wp_get_attachment_url();

	/**
	 * Grab the IDs of all the image attachments in a gallery so we can get the URL
	 * of the next adjacent image in a gallery, or the first image (if we're
	 * looking at the last image in a gallery), or, in a gallery of one, just the
	 * link to that image file.
	 */
	$attachment_ids = get_posts( array(
		'post_parent'    => $post->post_parent,
		'fields'         => 'ids',
		'numberposts'    => -1,
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'order'          => 'ASC',
		'orderby'        => 'menu_order ID'
	) );

	// If there is more than 1 attachment in a gallery...
	if ( count( $attachment_ids ) > 1 ) {
		foreach ( $attachment_ids as $attachment_id ) {
			if ( $attachment_id == $post->ID ) {
				$next_id = current( $attachment_ids );
				break;
			}
		}

		// get the URL of the next image attachment...
		if ( $next_id )
			$next_attachment_url = get_attachment_link( $next_id );

		// or get the URL of the first image attachment.
		else
			$next_attachment_url = get_attachment_link( array_shift( $attachment_ids ) );
	}

	printf( '<a href="%1$s" title="%2$s" rel="attachment">%3$s</a>',
		esc_url( $next_attachment_url ),
		the_title_attribute( array( 'echo' => false ) ),
		wp_get_attachment_image( $post->ID, $attachment_size )
	);
}

function qsou_change_gallery_output($current, $attr) {
	$post = get_post();

	$owp_query = clone $GLOBALS['wp_query'];

	// We're trusting author input, so let's at least make sure it looks like a valid orderby statement
	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( !$attr['orderby'] )
			unset( $attr['orderby'] );
	}

	extract(shortcode_atts(array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post ? $post->ID : 0,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'include'    => '',
		'exclude'    => ''
	), $attr, 'gallery'));

	ob_start();

	$id = intval($id);
	if ( 'RAND' == $order )
		$orderby = 'none';

	$args = array(
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order' => $order,
		'orderby' => $orderby,
	);
	if (!empty($include)) {
		$args['post__in'] = wp_parse_id_list($include);
	} elseif (!empty($exclude)) {
		$args['post_parent'] = $id;
		$args['post__not_in'] = wp_parse_id_list($exclude);
	} else {
		$args['post_parent'] = $id;
	}

	query_posts($args);
	qsou_gallery_output();

	$out = ob_get_contents();
	ob_end_clean();

	$GLOBALS['wp_query'] = $owp_query;
	wp_reset_postdata();

	return $out;
}
add_filter('post_gallery', 'qsou_change_gallery_output', 0, 2);

function qsou_gallery_output_from_gallery_post($post=null, $current_id=null) {
	if (is_numeric($post)) $post = get_post($post);
	elseif (!is_object($post)) $post = get_post();

	if (!is_object($post) || $post->ID == $current_id) return;

	$owp_query = clone $GLOBALS['wp_query'];

	$in = qsou_get_gallery_image_ids($post->ID, $current_id);

	$args = array(
		'posts_per_page' => -1,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'order' => 'ASC',
		'orderby' => 'post__in',
		'post__in' => wp_parse_id_list($in),
	);

	query_posts($args);
	qsou_gallery_output();

	$GLOBALS['wp_query'] = $owp_query;
	wp_reset_postdata();
}
add_action('qsou-gallery-from-gallery-id', 'qsou_gallery_output_from_gallery_post', 10, 2);

function qsou_get_gallery_image_ids($parent_id, $current_id=false) {
	$args = array(
		'fields' => 'ids',
		'posts_per_page' => -1,
		'post_status' => 'inherit',
		'post_type' => 'attachment',
		'post_mime_type' => 'image',
		'post_parent' => $parent_id,
	);
	$ids = get_posts($args);

	if (!is_array($ids) || empty($ids)) return array();
	if (empty($current_id)) $current_id = array_shift(array_values($ids));

	$front = $back = $in = array();
	$pos = array_search($current_id, $ids);
	$pos = empty($pos) ? 0 : $pos;
	$front = array_slice($ids, $pos);
	$back = $pos > 0 ? array_slice($ids, 0, $pos) : array();

	return array_merge($front, $back);
}

function qsou_gallery_thumb_image_id($gallery_id) {
	$img_id = get_post_thumbnail_id($gallery_id);
	if (empty($img_id)) {
		$ids = qsou_get_gallery_image_ids($gallery_id);
		$img_id = array_shift($ids);
	}
	return $img_id;
}

function qsou_gallery_image_link($current, $parent_id, $image_id, $type=false) {
	$ids = qsou_get_gallery_image_ids($parent_id, $image_id);
	$ids = is_array($ids) ? $ids : array();

	switch (strtolower($type)) {
		case 'next':
			$img_id = isset($ids[1]) ? $ids[1] : 0;
			$current = get_permalink($img_id);
		break;

		case 'prev':
			$img_id = count($ids) > 1 ? array_pop(array_values($ids)) : 0;
			$current = get_permalink($img_id);
		break;
	}

	return $current;
}
add_action('qsou-gallery-image-link', 'qsou_gallery_image_link', 10, 4);

function qsou_gallery_output() {
	static $u = 0;

	if (have_posts()):
		?>
			<div class="qsou-gallery jcarousel-skin-tango" id="qsou-gallery-<?php echo $u ?>">
				<ul class="gallery-image-list">
					<?php while (have_posts()): the_post(); ?>
						<li class="gallery-image-outer"><div class="gallery-image-inner"><div class="gallery-image-wrap"><a href="<?php echo esc_attr(get_permalink()) ?>" class="gallery-image-link"><?php
							echo wp_get_attachment_image(get_the_ID(), array(80,999), array('class' => 'gallery-image'))
						?></a></div></div></li>
					<?php endwhile; ?>
				</ul>
				<div class="clear"></div>
			</div>

			<script language="javascript">
				jQuery(function($) {
					$('#qsou-gallery-<?php echo $u ?>').jcarousel({
						buttonNextHTML: '<div>&gt;</div>',
						buttonPrevHTML: '<div>&lt;</div>'
					});
				});
			</script>
		<?php
		$u++;
	endif;
}


// Add Widgets
foreach( glob(dirname(__FILE__) . '/widgets/*.php') as $filename ){
	include_once( $filename );
}

//add_action('template_include', function($a) { die(__log('here', $a, $GLOBALS['wp_query'])); }, 0, 1);


function qsou_enqueue_scripts() {
	wp_enqueue_script('qsou-jcarousel', get_stylesheet_directory_uri().'/jc/jquery.jcarousel.min.js', array('jquery'), '0.2.9');
	wp_enqueue_style('qsou-jcarousel', get_stylesheet_directory_uri().'/jc/tango/skin.css', array(), '0.2.9');
}
add_action('woothemes_add_javascript', 'qsou_enqueue_scripts');

function qsou_debug($out, $ret=true) {
	echo '<pre>'; is_scalar($out) ? var_dump($out) : print_r($out); echo '</pre>';
	return $ret ? $out : '';
}

function qsou_remove_woo_cat() {
	return '';
}
add_filter( 'woo_shortcode_post_categories', 'qsou_remove_woo_cat' );

function qsou_save_post( $post_id, $post ) {
	if( $post->post_type == 'post' ) {
		if( strpos( $post->post_content, '[gallery' ) !== false ) {
			update_post_meta( $post_id, '_has_gallery', 1 );
		} else {
			update_post_meta( $post_id, '_has_gallery', 0 );
		}
	}

}
add_action( 'save_post', 'qsou_save_post', 10, 2 );

function qsou_meta() { 
	global $post, $wp_query;

	// for a photo page, get the tags of post_parent, otherwise get tags
	if( is_attachment() ) {
		$tags = get_the_tags($post->post_parent);
	} else {
		$tags = get_the_tags();
	}

	// if we have tags, then make a string of them comma-separated
	if( $tags ) {
		foreach( $tags as $tag ) {
			$out .= "$tag->name,";
		}
		$out = substr( $out, 0, -1);
	} else {
		$out = '';
	}

	//$thumb = wp_get_attachment_image( get_post_thumbnail_id() ); //get img tag + classes
	//$thumb = get_the_post_thumbnail( $post->ID , 'post-thumbnail' );

	// Act a little differently depending on page-type: home, tag or single post
	if( is_home() ) {
		$thumb = array( '/wp-content/uploads/2013/11/oceanup-logo.png' );
		$title = get_bloginfo( 'title' );
		$permalink = get_bloginfo( 'home' );
	} elseif( is_tag() ) {
		$thumb = array( '/wp-content/uploads/2013/11/oceanup-logo.png' );
		$title = get_bloginfo( 'title' );
		$permalink = get_term_link( get_query_var( 'tag' ), 'post_tag' );
	} else {
		$thumb = wp_get_attachment_image_src( get_post_thumbnail_id($post->ID), 'thumb');
		$title = $post->post_title;
		$permalink = get_permalink();
	}
?>
	<meta property="og:url" content="<?=$permalink;?>" />
	<meta property="og:title" content="<?=$title;?>" />
	<meta name="keywords" content="<?=$out;?>" />
	<meta property="og:description" content="OCEANUP - Teen Gossip, Celebrity and Entertainment News, Photos and Videos" />
	<meta property="og:image" content="<?=$thumb[0];?>" />
<?php
}

// override of canvas theme
function woo_post_inside_after_default() {
	global $post;

	$post_info ='[post_tags before=""]';
	printf( '<div class="post-utility">%s</div>' . "\n", apply_filters( 'woo_post_inside_after_default', $post_info ) );


	$attachments = get_posts( array( 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_parent' => $post->ID) );
	$out = array();
	foreach( $attachments as $att ) {
		$meta = get_post_meta( $att->ID, '_wp_attachment_metadata', true );
		if (is_array($meta)) $out[] = $meta['image_meta']['credit'];
	}

	//$attribution = isset($image_meta['image_meta'], $image_meta['image_meta']['credit']) ? $image_meta['image_meta']['credit'] : '';
	/*
	$attribution = array_shift( array_unique( $out ) );

	if (!empty($attribution)) {
		echo '<div class="attribution">Photo Credit: ';
		echo force_balance_tags($attribution) . '</div>';
	}
	*/
} // End woo_post_inside_after_default()

function qscomments($out, $atts='') {
	global $post;
	$url = get_permalink();
	//$url = parse_url($url);
	//$url  = 'http://oceanup.com'.$url['path'];
	return '<a href="'.$url.'"><i class="icon-comment"></i></a> <a href="'.$url.'#disqus_thread">'.$post->comment_count.'</a>';
	//str_replace('#comments', '#disqus_thread', $out);
}
add_filter('woo_shortcode_post_comments', 'qscomments', PHP_INT_MAX, 2);

function qs_default_att_page() {
	$u = uniqid('marker');
	?><script> (function($) { var v = $('#tmpl-attachment-display-settings'); v.text(v.text() + '<div class="<?php echo $u ?>"></div><script>jQuery(".<?php echo $u ?>").parent().find(".setting .link-to option").removeAttr("selected").filter("[value=post]").attr("selected", "selected").closest("select").change();</scr'+'ipt>'); })(jQuery); </script><?php
}
add_action('print_media_templates', 'qs_default_att_page');

function qs_hide_admin_bar_on_frontend($show) {
	return is_admin();
}
//add_filter('show_admin_bar', 'qs_hide_admin_bar_on_frontend', 10, 1); 

function qsou_after_post() {
	if (is_single()):
		?>
			<div id="hexagram_3972"></div>
			<script src="//ssl-nau.hexagram.com/js/hexagram.min.js"></script>
		<?php
	endif;
}
add_action('woo_post_after', 'qsou_after_post', 1);

function qsou_loop_after() {
	?>
		<div id="hexagram_3972"></div>
		<script src="//ssl-nau.hexagram.com/js/hexagram.min.js"></script>
	<?php
}
add_action('woo_loop_after', 'qsou_loop_after');

function qs_add_image_size() {
	add_image_size( 'gallery-thumb', 80, 80 );
	add_image_size( 'hot-uppers', 120, 120 );
	add_image_size( 'river-gallery', 407 );
	add_image_size( 'river-single', 457 );
}
add_action( 'init', 'qs_add_image_size' );
add_action( 'admin_init', 'qs_add_image_size' );

function crowd_ignite_single_post( $content ) {
	$out = '';
	if ( is_singular() ) {
		ob_start();
		?>
	<span style="color:#000;font-family:Arial, Helvetica, sans-serif;font-size:15px;font-weight:700;line-height:18px;padding-bottom:6px;text-transform:capitalize;">Around the web</span> <!--POSTARTICLE ALV--> <script type='text/javascript'> var _CI = _CI || {}; (function() { var script = document.createElement('script'); ref = document.getElementsByTagName('script')[0]; _CI.counter = (_CI.counter) ? _CI.counter + 1 : 1; document.write('<div id="_CI_widget_'); document.write(_CI.counter+'"></div>'); script.type = 'text/javascript'; script.src = 'http://widget.crowdignite.com/widgets/28053?v=2&_ci_wid=_CI_widget_'+_CI.counter; script.async = true; ref.parentNode.insertBefore(script, ref); })(); </script> <style> #_ci_widget_div_28053{display:inline;height:auto;width:457px;} #_ci_widget_div_28053 ul{-webkit-margin-after:1em;-webkit-margin-before:1em;-webkit-padding-start:0;display:inline-block;list-style-type:none;margin-left:0;min-height:150px;padding-left:0;width:457px;} #_ci_widget_div_28053 ul li{float:left;list-style-type:none;margin-left:8px;min-height:149px;vertical-align:top;width:147px;} #_ci_widget_div_28053 ul li:first-child{margin-left:0;} #_ci_widget_div_28053 .ci_text{display:block;} #_ci_widget_div_28053 .ci_text > a{color:#428bca;font-family:'Droid Sans', arial, sans-serif;font-size:13px;font-weight:none;line-height:19.5px;} #_ci_widget_div_28053 .ci_text > a:hover{color:#ff4800;} </style>
		<?php
		$out = ob_get_contents();
		ob_end_clean();
	}

	return $content.$out;
}
add_filter( 'the_content', 'crowd_ignite_single_post', 1199, 1 );
