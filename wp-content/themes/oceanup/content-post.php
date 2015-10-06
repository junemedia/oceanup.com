<?php
/**
 * Post Content Template
 *
 * This template is the default page content template. It is used to display the content of the
 * `single.php` template file, contextually, as well as in archive lists or search results.
 *
 * @package WooFramework
 * @subpackage Template
 */

/**
 * Settings for this template file.
 *
 * This is where the specify the HTML tags for the title.
 * These options can be filtered via a child theme.
 *
 * @link http://codex.wordpress.org/Plugin_API#Filters
 */

$settings = array(
				'thumb_w' => 100,
				'thumb_h' => 100,
				'thumb_align' => 'alignleft',
				'post_content' => 'excerpt',
				'comments' => 'both'
				);

$settings = woo_get_dynamic_values( $settings );

$title_before = '<h1 class="title">';
$title_after = '</h1>';

if ( ! is_single() ) {
$title_before = '<h2 class="title">';
$title_after = '</h2>';
$title_before = $title_before . '<a href="' . esc_url( get_permalink( get_the_ID() ) ) . '" rel="bookmark" title="' . the_title_attribute( array( 'echo' => 0 ) ) . '">';
$title_after = '</a>' . $title_after;
}

$page_link_args = apply_filters( 'woothemes_pagelinks_args', array( 'before' => '<div class="page-link">' . __( 'Pages:', 'woothemes' ), 'after' => '</div>' ) );

woo_post_before();
?>
<article <?php post_class(); ?>>
<?php
woo_post_inside_before();
if ( 'content' != $settings['post_content'] && ! is_singular() )
	woo_image( 'width=' . esc_attr( $settings['thumb_w'] ) . '&height=' . esc_attr( $settings['thumb_h'] ) . '&class=thumbnail ' . esc_attr( $settings['thumb_align'] ) );
?>
	<header>
	<?php the_title( $title_before, $title_after ); ?>
	</header>
<?php
woo_post_meta();
?>

<?php
$gallery_id = get_post_meta( $post->ID, '_gallery_embed', true );
if( $gallery_id ) {
	do_action('qsou-gallery-from-gallery-id', $gallery_id);
}
?>


	<section class="entry">
<?php
if ( 'content' == $settings['post_content'] || is_single() ) { the_content( __( 'Continue Reading &rarr;', 'woothemes' ) ); } else { the_excerpt(); }
if ( 'content' == $settings['post_content'] || is_singular() ) wp_link_pages( $page_link_args );
?>
<?php if( !is_singular() ) {
	echo '<div class="post-river-tags">';
	the_tags('<i class="icon-tag"></i> ');
	echo '</div>';
} ?>
	</section><!-- /.entry -->


	<div class="fix"></div>
<?php

woo_post_inside_after();
?>
<?php if (is_singular() && is_active_sidebar('post-content-widget-area')): ?>
	<div class="post-content-widgets">
		<?php dynamic_sidebar('post-content-widget-area') ?>
	</div>
<?php endif; ?>
</article><!-- /.post -->

  <?php if (is_single()): ?>
  <?php // OpenX mid-post ad unit, gets initiated in adunits.js ?>
  <div class="widget adspace-widget mobile">
    <div id="538014858_300x250POST-1" style="width:300px;height:250px;margin:0;padding:0">
      <noscript>
        <iframe id="87cd89828f" name="87cd89828f" src="http://ox-d.junemedia.com/w/1.0/afr?auid=538014858&cb=<?php echo uniqid(); ?>" frameborder="0" scrolling="no" width="300" height="250">
          <a href="http://ox-d.junemedia.com/w/1.0/rc?cs=87cd89828f&cb=<?php echo uniqid(); ?>" >
            <img src="http://ox-d.junemedia.com/w/1.0/ai?auid=538014858&cs=87cd89828f&cb=<?php echo uniqid(); ?>" border="0" alt="">
          </a>
        </iframe>
      </noscript>
    </div>
  </div>
  <?php endif; ?>

<?php
woo_post_after();
$comm = $settings['comments'];
if ( ( 'post' == $comm || 'both' == $comm ) && is_single() ) { comments_template(); }
?>
