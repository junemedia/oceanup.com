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
<!--  Mobile viewport scale -->
<meta content="initial-scale=1.0, maximum-scale=1.0, user-scalable=yes" name="viewport"/>
<title><?php woo_title(); ?></title>
<?php woo_meta(); ?>
<?php qsou_meta(); ?>

<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests;block-all-mixed-content">

<link rel="pingback" href="<?php echo esc_url( get_bloginfo( 'pingback_url' ) ); ?>" />
<?php wp_head(); ?>
<?php woo_head(); ?>

<?php // site owner: silvercarrot2012@gmail.com ?>
<meta name="google-site-verification" content="20ZLldDjVYGq2rFw1XgNrYmcgYnGLxJzN90k6dGnbGk" />

<?php get_template_part( 'partials/ads/adthrive', 'js' ); ?>

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
