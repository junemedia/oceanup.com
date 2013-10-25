<?php
/**
 * Template Name: Gallery List
 */

get_header();
?>
       
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    

				<!-- #main Starts -->
				<?php woo_main_before(); ?>
				<section id="main">
					<div class="gallery-list">
						<?php
							$owp_query = clone $GLOBALS['wp_query'];

							$per_page = 18;
							$pg = 1;
							$args = array(
								'post_type' => 'oc_gallery',
								'post_status' => 'publish',
								'posts_per_page' => 18,
							);
							if (isset($owp_query->query_vars['paged'])) $pg = $args['paged'] = $owp_query->query_vars['paged'];
							query_posts($args);

							woo_loop_before();
							
							if (have_posts()) { $count = 0;
								while (have_posts()) { the_post(); $count++;
									
									woo_get_template_part( 'photos', get_post_type() ); // Get the post content template file, contextually.
								}
							}

							woo_pagination();
							
							woo_loop_after();

							$GLOBALS['wp_query'] = $owp_query;
						?>     
					</div>
				</section><!-- /#main -->
				<?php woo_main_after(); ?>

				<?php get_sidebar(); ?>

			</div><!-- /#main-sidebar-container -->         

			<?php get_sidebar('alt'); ?>

    </div><!-- /#content -->
	<?php woo_content_after(); ?>

<?php get_footer(); ?>
