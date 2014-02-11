<?php
/**
 * Footer Template
 *
 * Here we setup all logic and XHTML that is required for the footer section of all screens.
 *
 * @package WooFramework
 * @subpackage Template
 */

 global $woo_options;

	woo_footer_top();
 	woo_footer_before();

	$barname = 'bottom-widget-area';
	$barsuffix = '';
	if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';
?>
	<?php if (is_active_sidebar($barname.$barsuffix)): ?>
		<div class="bottom-widget-area">
			<?php dynamic_sidebar($barname.$barsuffix); ?>
		</div>
	<?php elseif (is_active_sidebar($barname)): ?>
		<div class="bottom-widget-area">
			<?php dynamic_sidebar($barname); ?>
		</div>
	<?php endif; ?>

	<footer id="footer" class="col-full">

		<?php woo_footer_inside(); ?>

		<div id="copyright" class="col-left">
			<?php woo_footer_left(); ?>
		</div>

		<div id="credit" class="col-right">
			<?php woo_footer_right(); ?>
		</div>

	</footer>

	<?php woo_footer_after(); ?>

	</div><!-- /#inner-wrapper -->

</div><!-- /#wrapper -->

<div class="fix"></div><!--/.fix-->

<?php wp_footer(); ?>
<?php woo_foot(); ?>
<!-- WAHWAH Radio Player --><script src="http://cdn-s.wahwahnetworks.com/00BA6A/toolbar/publishers/63/wahwahobject.js"></script><!-- End WAHWAH Radio Player -->
<script type="text/javascript"> var _gaq = _gaq || []; _gaq.push(['_setAccount', 'UA-3131475-2']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })(); </script>
</body>
</html>
