<?php
/**
 * Template Name: Blog
 *
 * The blog page template displays the "blog-style" template on a sub-page. 
 *
 * @package WooFramework
 * @subpackage Template
 */

 get_header();
 global $woo_options;
?>      
    <!-- #content Starts -->
	<?php woo_content_before(); ?>
    <div id="content" class="col-full">
    
    	<div id="main-sidebar-container">    
		
            <!-- #main Starts -->
            <?php woo_main_before(); ?>

            <section id="main" class="col-left">
            	
			<?php get_template_part( 'loop', 'blog' ); ?>
                    
            </section><!-- /#main -->
            <?php woo_main_after(); ?>
    
						<?php get_sidebar('top-ad'); ?>
						
            <?php get_sidebar(); ?>

						<?php get_sidebar( 'alt' ); ?>
    
		</div><!-- /#main-sidebar-container -->         

    </div><!-- /#content -->
	<?php woo_content_after(); ?>
		
<?php get_footer(); ?>
