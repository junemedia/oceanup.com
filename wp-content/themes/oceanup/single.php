<?php
/**
 * Single Post Template
 *
 * This template is the default page template. It is used to display content when someone is viewing a
 * singular view of a post ('post' post_type).
 * @link http://codex.wordpress.org/Post_Types#Post
 *
 * @package WooFramework
 * @subpackage Template
 */

get_header();
?>

    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">

    	<div id="main-sidebar-container">
<?php
	if( current_user_can( 'edit_post' ) ) {
		edit_post_link('Edit this','<div class="">', '</div>');
	}
?>

            <!-- #main Starts -->
            <?php woo_main_before(); ?>
            <section id="main">
<?php
	woo_loop_before();

	if (have_posts()) { $count = 0;
		while (have_posts()) { the_post(); $count++;
			woo_get_template_part( 'content', get_post_type() ); // Get the post content template file, contextually.
		}
	}

	woo_loop_after();
?>
		<?php // CrowdIgnite widget, gets populated in adunits.js ?>
		<div class="widget widget_text">
			<div class="textwidget">
				<h3 style="border:none;box-sizing:border-box;padding:0 .4em;text-transform:capitalize;text-align:left;margin:0 auto;">More from around the web</h3>
				<div id="post_CI_widget"></div>
			</div>
		</div>
            </section><!-- /#main -->
            <?php woo_main_after(); ?>

						<?php get_sidebar('top-ad'); ?>

            <?php get_sidebar(); ?>

						<?php get_sidebar( 'alt' ); ?>

		</div><!-- /#main-sidebar-container -->

    </div><!-- /#content -->
	<?php woo_content_after(); ?>


<?php get_footer(); ?>
