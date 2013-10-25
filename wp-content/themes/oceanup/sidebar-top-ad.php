<?php
/**
 * Alternate Sidebar Template
 *
 * If a `secondary` widget area is active and has widgets,
 * and the selected layout has a third column, display the sidebar.
 *
 * @package WooFramework
 * @subpackage Template
 */
	global $woo_options;
	
	$selected_layout = 'one-col';
	$layouts = array( 'three-col-left', 'three-col-middle', 'three-col-right' );
	if ( is_array( $woo_options ) && array_key_exists( 'woo_layout', $woo_options ) ) { $selected_layout = $woo_options['woo_layout']; }
	
	if ( in_array( $selected_layout, $layouts ) ) {

		$barname = 'top-sidebar-ad-area';
		$barsuffix = '';
		if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';

		if ( woo_active_sidebar( $barname.$barsuffix ) ) {
	
			woo_sidebar_before();
?>
<aside id="sidebar-top-ad">
	<?php
		woo_sidebar_inside_before();
		woo_sidebar( $barname.$barsuffix );
		woo_sidebar_inside_after();
	?>
</aside><!-- /#sidebar-alt -->
<?php
			woo_sidebar_after();
		} elseif ( woo_active_sidebar( $barname ) ) {
	
			woo_sidebar_before();
?>
<aside id="sidebar-top-ad">
	<?php
		woo_sidebar_inside_before();
		woo_sidebar( $barname );
		woo_sidebar_inside_after();
	?>
</aside><!-- /#sidebar-alt -->
<?php
			woo_sidebar_after();
		} // End IF Statement
	} // End IF Statement
?>
