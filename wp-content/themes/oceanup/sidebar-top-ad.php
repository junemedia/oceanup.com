<?php
/**
 * Top Ad Sidebar Template
 *
 * @package WooFramework
 * @subpackage Template
 */
	global $woo_options;

	$barname = 'top-sidebar-ad-area';
	$barsuffix = '';
	if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';
	woo_sidebar_before();
?>
<aside id="sidebar-top-ad">
	<?php woo_sidebar_inside_before(); ?>

	<?php get_template_part( 'partials/ads/insticator', '300x850' ); ?>

	<?php get_template_part( 'partials/ads/contentad', 'rail' ); ?>

	<div class="widget yarpp"> <?php if ( is_single() ) { related_posts(); } ?> </div>

	<?php get_template_part( 'partials/ads/openx', '160x600btf' ); ?>

	<?php get_template_part( 'partials/ads/zergnet', 'sidebar' ); ?>

	<?php woo_sidebar( $barname.$barsuffix ); ?>

	<?php woo_sidebar_inside_after(); ?>

</aside><!-- /#sidebar-top-ad -->
<?php
	woo_sidebar_after();
?>
