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
<meta charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>" />
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
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
