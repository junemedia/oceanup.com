<?php
/**
 * Header Template
 *
 * Here we setup all logic and XHTML that is required for the header section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<link rel="publisher" href="https://plus.google.com/112032429635316978822"/>
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" />
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
<?php qsou_meta(); ?>

<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
<?php wp_head(); ?>
<?php woo_head(); ?>
<script type="text/javascript">
(function() {
var a, s = document.getElementsByTagName("script")[0];
a = document.createElement("script");
a.type="text/javascript";  a.async = true;
a.src = "http://www.luminate.com/widget/async/11c0daab2be/";
s.parentNode.insertBefore(a, s);
})();
</script>
<script type="text/javascript"> window._taboola = window._taboola || []; _taboola.push({article: 'auto'}); !function (e, f, u) { e.async = 1; e.src = u; f.parentNode.insertBefore(e, f); } (document.createElement('script'), document.getElementsByTagName('script')[0], '//cdn.taboola.com/libtrc/oceanup-oceanup/loader.js'); </script> 
<!-- BEGIN TRIGGER TAG INITIALIZATION -->
<script type="text/javascript" src="http://cdn.triggertag.gorillanation.com/js/triggertag.js"></script>
<script type="text/javascript">getTrigger('3883');</script>
<!-- END TRIGGER TAG INITIALIZATION -->

<?php if(is_home()): ?>
<!-- BEGIN GN Ad Tag for OceanUP 1000x1000 home -->
<script type="text/javascript">
	if((typeof(f311042)=='undefined' || f311042 > 0) ){
		if(typeof(gnm_ord)=='undefined') gnm_ord=Math.random()*10000000000000000; if(typeof(gnm_tile) == 'undefined') gnm_tile=1;
		document.write('<scr'+'ipt src="http://n4403ad.doubleclick.net/adj/gn.oceanup.com/home;sect=home;mtfInline=true;sz=1000x1000;tile='+(gnm_tile++)+';ord='+gnm_ord+'?" type="text/javascript"></scr'+'ipt>');
	}
</script>
<!-- END AD TAG -->
<?php else: ?>
<!-- BEGIN GN Ad Tag for OceanUP 1000x1000 ros -->
<script type="text/javascript">
if ((typeof(f311044)=='undefined' || f311044 > 0) ) {
	if(typeof(gnm_ord)=='undefined') gnm_ord=Math.random()*10000000000000000; if(typeof(gnm_tile) == 'undefined') gnm_tile=1;
	document.write('<scr'+'ipt src="http://n4403ad.doubleclick.net/adj/gn.oceanup.com/ros;sect=ros;mtfInline=true;sz=1000x1000;tile='+(gnm_tile++)+';ord='+gnm_ord+'?" type="text/javascript"></scr'+'ipt>');
}
</script>
<!-- END AD TAG -->
<?php endif; ?>
<!-- WAHWAH Radio Player --><script src="http://cdn-s.wahwahnetworks.com/00BA6A/toolbar/publishers/63/wahwahobject.js"></script><!-- End WAHWAH Radio Player -->
<meta name="google-site-verification" content="TgIB1gz9bK1CokHPqDr6z6K6RPM5_qPOoEdjghctIas" />
</head>
<body <?php body_class(); ?>>

<?php woo_top(); ?>
<div id="wrapper">
	<div id="inner-wrapper">
			<?php woo_header_before(); ?>
				<header id="header" class="container">
					<?php woo_header_inside(); ?>
				</header>
			<?php woo_header_after(); ?>
