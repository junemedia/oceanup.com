<?php
/**
 * Gallery Content Template
 *
 * This template is the default page content template. It is used to display the content of the
 * `single.php` template file, contextually, as well as in archive lists or search results.
 */

$owp_query = clone $GLOBALS['wp_query'];
$opost = get_post();

$args = array(
	'post_type' => 'attachment',
	'post_status' => 'any',
	'posts_per_page' => 1,
	'post_parent' => $opost->ID,
);
query_posts($args);
wp_reset_postdata();

if (have_posts()) {
	woo_get_template_part( 'content', 'attachment' );
} else {
	woo_get_template_part( 'content', '404' );
}

$GLOBALS['wp_query'] = $owp_query;
wp_reset_postdata();
