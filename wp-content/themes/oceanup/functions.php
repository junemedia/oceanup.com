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