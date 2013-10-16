<?php

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
		echo '<aside id="oceanup-header-widgets-top" class="clearfix col-full">';
		dynamic_sidebar( 'oceanup-header-widgets-top' );
		echo '</aside>';
		echo '<aside id="oceanup-header-widgets-bottom" class="clearfix col-full">';
		dynamic_sidebar( 'oceanup-header-widgets-bottom' );
		echo '</aside>';
	}
}
add_action( 'woo_header_after', 'oceanup_header_widget_areas' );

if ( ! function_exists( 'oceanup_register_header_widget_areas' ) ) {
	function oceanup_register_header_widget_areas(){
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
	}
}
add_action( 'widgets_init', 'oceanup_register_header_widget_areas' );

// Gallery/Attachments
if ( ! function_exists( 'oceanup_attachment_title' ) ) {
	function oceanup_attachment_title( $_post_title = '', $_post_id = 0 ){
		if ( get_post_type($GLOBALS['post']->ID) == 'attachment' ){
			$parent = get_post($GLOBALS['post']->post_parent);
			$_post_title = $parent->post_title;
		}
		return $_post_title;
	}
}
add_filter( 'the_title', 'oceanup_attachment_title', 10, 2 );

if ( ! function_exists( 'oceanup_attachment_content' ) ) {
	function oceanup_attachment_content( $_post_content = '', $_post_id = 0 ){
		if ( get_post_type($GLOBALS['post']->ID) == 'attachment' ){
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
add_filter( 'woo_filter_post_meta', 'oceanup_post_meta' );

// Enqueue custom JS for our child theme
if (!function_exists('oceanup_enqueue_scripts')){
	function oceanup_enqueue_scripts(){
		wp_register_script(
			'oceanup-js',
			get_stylesheet_directory_uri() . '/js/oceanup.js',
			'jquery',
			null,
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
		'name' => 'OC Gallery',
		'singular_name' => 'OC Gallery',
		'add_new' => 'Add new OC gallery',
		'add_new_item' => 'Add New Book',
		'edit_item' => 'Edit Gallery',
		'new_item' => 'New Gallery',
		'all_items' => 'All Galleries',
		'view_item' => 'View Gallery',
		'search_items' => 'Search Galleries',
		'not_found' =>  'No galleries found',
		'not_found_in_trash' => 'No galleries found in Trash', 
		'parent_item_colon' => '',
		'menu_name' => 'OC Galleries'
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

