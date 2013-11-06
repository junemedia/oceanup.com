<?php if (isset($comments) && is_array($comments)): ?>
	<?php echo $before_widget; ?>
	<div class="widget-wrap qsda-recent-comments">
		<?php echo $before_title.force_balance_tags($title).$after_title ?>
		<div class="comment-list">
			<?php foreach ($comments as $comment): ?>
				<div class="comment-item" id="comment-item-<?php echo $comment->id ?>">
					<?php if (isset($comment->author)): ?>
						<div class="comment-author">
							<?php if (isset($comment->author->avatar, $comment->author->avatar->small, $comment->author->avatar->small->cache)): ?>
								<div class="comment-avatar-wrap"><img src="<?php echo esc_attr($comment->author->avatar->small->cache) ?>" class="comment-avatar" /></div>
							<?php endif; ?>
							<div class="comment-author-name">
								<?php if (isset($comment->author->profileUrl)): ?>
									<a class="author-name author-link" target="_blank" href="<?php echo esc_attr($comment->author->profileUrl) ?>"><?php echo force_balance_tags($comment->author->name) ?></a>
								<?php else: ?>
									<span class="author-name"><?php echo force_balance_tags($comment->author->name) ?></span>
								<?php endif; ?>
							</div>
							<div class="clear"></div>
						</div>
					<?php endif; ?>

					<?php $story_link = ''; ?>
					<?php if (isset($comment->thread_info) && is_object($comment->thread_info)): ?>
						<div class="post-link-wrap">
							<?php $story_link = $comment->thread_info->link; ?>
							<a class="post-link" href="<?php echo esc_attr($story_link) ?>" title="View this Story"><?php echo force_balance_tags($comment->thread_info->title) ?></a>
							<div class="clear"></div>
						</div>
					<?php endif; ?>

					<?php if (!empty($story_link)): ?>
						<a class="comment-text comment-link" href="<?php echo esc_attr($story_link).'#comment-'.$comment->id ?>" title="View this comment"><?php
							echo !empty($length) && strlen($comment->raw_message) > $length ? substr($comment->raw_message, 0, $length).'...' : $comment->raw_message;
						?></a>
					<?php else: ?>
						<span class="comment-text"><?php echo !empty($length) && strlen($comment->raw_message) > $length ? substr($comment->raw_message, 0, $length).'...' : $comment->raw_message; ?></span>
					<?php endif; ?>

					<div class="clear"></div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php echo $after_widget; ?>
<?php endif; 
