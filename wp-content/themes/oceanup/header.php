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
<script type="text/javascript"> window._taboola = window._taboola || []; _taboola.push({article: 'auto'}); !function (e, f, u) { e.async = 1; e.src = u; f.parentNode.insertBefore(e, f); } (document.createElement('script'), document.getElementsByTagName('script')[0], '//cdn.taboola.com/libtrc/oceanup-oceanup/loader.js'); </script> 

<meta name="google-site-verification" content="TgIB1gz9bK1CokHPqDr6z6K6RPM5_qPOoEdjghctIas" />
</head>
<body <?php body_class(); ?>>

<!-- <?php echo gethostname(); ?> -->

<?php woo_top(); ?>
<div id="wrapper">
	<div id="inner-wrapper">
			<?php woo_header_before(); ?>
				<header id="header" class="container">
					<?php woo_header_inside(); ?>
				</header>
			<?php woo_header_after(); ?>
