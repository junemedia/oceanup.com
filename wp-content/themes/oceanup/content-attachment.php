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

$image_meta = get_post_meta(get_the_ID(), '_wp_attachment_metadata', true);
$attribution = isset($image_meta['image_meta'], $image_meta['image_meta']['credit']) ? $image_meta['image_meta']['credit'] : '';
?>
	<header>
	<?php the_title( $title_before, $title_after ); ?>
	</header>

	<section class="entry">
		<div class="current-image-wrapper" id="image-<?php echo get_the_ID() ?>">
			<div class="current-image-outer"><div class="current-image-inner"><div class="current-image-wrap"><a
				href="<?php echo esc_attr(apply_filters('qsou-gallery-image-link', get_permalink(), wp_get_post_parent_id(get_the_ID()), get_the_ID(), 'next')) ?>" class="current-image-link"><?php
//echo 'id: ' . get_the_ID();
					echo wp_get_attachment_image(get_the_ID(), array(407, 9999), false, array('class' => 'current-image'))
			?></a></div></div></div>

			<a href="<?php echo esc_attr(apply_filters('qsou-gallery-image-link', get_permalink(), wp_get_post_parent_id(get_the_ID()), get_the_ID(), 'next')) ?>" class="next image-nav">&gt;</a>
			<a href="<?php echo esc_attr(apply_filters('qsou-gallery-image-link', get_permalink(), wp_get_post_parent_id(get_the_ID()), get_the_ID(), 'prev')) ?>" class="prev image-nav">&lt;</a>

		</div>

		<?php do_action('qsou-gallery-from-gallery-id', wp_get_post_parent_id(get_the_ID()), get_the_ID()); ?>
	</section><!-- /.entry -->
	<div class="fix"></div>
	<?php 
	// this shows a credit/attribution under the photo, above the carousel
	/*if (!empty($attribution)): ?>
		<div class="attribution">Photo Credit: <?php echo force_balance_tags($attribution) ?></div>
	<?php endif; */ ?>

<?php if ( is_active_sidebar( 'under-carousel-photos' ) ) : ?>
<ul id="boola">
	<?php dynamic_sidebar( 'under-carousel-photos' ); ?>
</ul>
<?php endif; ?>
<?php
woo_post_inside_after();
?>
</article><!-- /.post -->
<?php
woo_post_after();
$comm = $settings['comments'];
if ( ( 'post' == $comm || 'both' == $comm ) && is_single() ) { comments_template(); }
?>
