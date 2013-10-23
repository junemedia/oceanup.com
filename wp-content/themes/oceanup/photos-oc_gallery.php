<div class="gallery-item">
	<article id="post-<?php echo get_the_ID() ?>" class="gallery <?php post_class() ?>">
		<header>
			<?php $img_id = function_exists('qsou_gallery_thumb_image_id') ? qsou_gallery_thumb_image_id(get_the_ID()) : get_post_thumbnail_id(get_the_ID()); ?>
			<?php if (!empty($img_id)): ?>
				<?php
					list($src, $w, $h) = wp_get_attachment_image_src($img_id, array(250, 9999));
					$r = !empty($h) ? $w/$h : (100/75);
					$keyhole = $r < (100/75) ? 'too-tall' : 'too-wide';
				?>
				<div class="image-outer">
					<div class="image-inner">
						<div class="image-wrap"><a href="<?php echo esc_attr(get_permalink(get_the_ID())) ?>" class="image-gallery-link"
							><?php echo wp_get_attachment_image($img_id, array(250, 9999), false, array('class' => 'gallery-main-image '.$keyhole)) ?></a></div>
					</div>
				</div>
			<?php endif; ?>
			<?php /* no title for now
			<h4><a href="<?php echo esc_attr(get_permalink(get_the_ID())) ?>" class="image-gallery-title-link"><?php the_title() ?></a></h4>
			*/ ?>
		</header>
	</article>
</div>
