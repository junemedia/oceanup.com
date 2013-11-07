<?php if (isset($commenters) && is_array($commenters)): ?>
	<?php echo $before_widget; ?>
	<div class="widget-wrap qsda-top-commenters">
		<?php echo $before_title.force_balance_tags($title).$after_title ?>
		<div class="commenter-list">
			<?php foreach ($commenters as $commenter): if (isset($commenter->id)):  ?>
				<div class="commenter-item" id="commenter-item-<?php echo $commenter->id ?>">
					<div class="commenter-author">
						<?php if (isset($commenter->avatar, $commenter->avatar->large, $commenter->avatar->large->cache)): ?>
							<div class="commenter-avatar-wrap"><img src="<?php echo esc_attr($commenter->avatar->large->cache) ?>" class="commenter-avatar" /></div>
						<?php endif; ?>
						<div class="commenter-author-name">
							<?php if (isset($commenter->profileUrl)): ?>
								<a class="author-name author-link" target="_blank" href="<?php echo esc_attr($commenter->profileUrl) ?>"><?php echo force_balance_tags($commenter->name) ?></a>
							<?php else: ?>
								<span class="author-name"><?php echo force_balance_tags($commenter->name) ?></span>
							<?php endif; ?>
							<div class="comment-count-wrap"><span class="comment-count">Comments: <?php echo force_balance_tags($commenter->numPosts) ?></span></div>
						</div>
						<div class="clear"></div>
					</div>

					<?php if (!empty($commenter->recent_posts)): ?>
						<div class="post-list">
							<?php foreach ($commenter->recent_posts as $rpost): if (isset($rpost->link, $rpost->title)): ?>
								<div class="post-item">
									<div class="post-link-wrap">
										<a class="post-link" href="<?php echo esc_attr($rpost->link) ?>" title="View this Story"><?php echo force_balance_tags($rpost->title) ?></a>
										<div class="clear"></div>
									</div>
								</div>
							<?php endif; endforeach; ?>
						</div>
					<?php endif; ?>

					<div class="clear"></div>
				</div>
			<?php endif; endforeach; ?>
		</div>
	</div>
	<?php echo $after_widget; ?>
<?php endif; 
