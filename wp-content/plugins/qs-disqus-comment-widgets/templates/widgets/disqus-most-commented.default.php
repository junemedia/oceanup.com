<?php if (isset($posts) && is_array($posts)): ?>
	<?php echo $before_widget; ?>
	<div class="widget-wrap qsda-most-commented">
		<?php echo $before_title.force_balance_tags($title).$after_title ?>
		<div class="post-list">
			<?php foreach ($posts as $post): ?>
				<?php if (!is_object($post) || !isset($post->title)) continue; ?>
				<div class="post-item" id="post-item-<?php echo $post->id ?>">
					<div class="post-link-wrap">
						<?php if (isset($post->link) && is_scalar($post->link)): ?>
							<a class="post-title post-link" href="<?php echo esc_attr($post->link) ?>" title="View this Story"><?php echo force_balance_tags($post->title) ?></a>
						<?php else: ?>
							<span class="post-title"><?php echo force_balance_tags($post->title) ?></span>
						<?php endif; ?>

						<?php if (isset($post->postsInInterval)): ?>
							<span class="comment-count"><span class="comment-number"><?php echo force_balance_tags($post->postsInInterval) ?></span></span>
						<?php endif; ?>
						<div class="clear"></div>
					</div>

					<div class="clear"></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php echo $after_widget; ?>
<?php endif; 
