<?php
/**
 * Sidebar Template
 *
 * If a `primary` widget area is active and has widgets, display the sidebar.
 *
 * @package WooFramework
 * @subpackage Template
 */

$settings = array(
				'layout' => 'two-col-left',
				'portfolio_layout' => 'one-col'
				);

$settings = woo_get_dynamic_values( $settings );

$layout = $settings['layout'];
// Cater for custom portfolio gallery layout option.
if ( is_tax( 'portfolio-gallery' ) || is_post_type_archive( 'portfolio' ) ) {
	if ( '' != $settings['portfolio_layout'] ) { $layout = $settings['portfolio_layout']; }
}

$barname = 'primary';
$barsuffix = '';
if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';

if ( 'one-col' != $layout ) {
	if ( woo_active_sidebar( $barname.$barsuffix ) ) {
		woo_sidebar_before();
?>
<aside id="sidebar">
<?php
	woo_sidebar_inside_before();
	woo_sidebar( $barname.$barsuffix );
	woo_sidebar_inside_after();
?>
</aside><!-- /#sidebar -->
<?php
		woo_sidebar_after();
	} elseif ( woo_active_sidebar( $barname ) ) {
		woo_sidebar_before();
?>
<aside id="sidebar">
<?php
	woo_sidebar_inside_before();
	woo_sidebar( $barname );
	woo_sidebar_inside_after();
?>
</aside><!-- /#sidebar -->
<?php
		woo_sidebar_after();
	}
}
?>
