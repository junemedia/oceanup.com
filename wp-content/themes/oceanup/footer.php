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
?>
	<?php if (is_active_sidebar('bottom-widget-area')): ?>
		<div class="bottom-widget-area">
			<?php dynamic_sidebar('bottom-widget-area'); ?>
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
<script type="text/javascript"> var _sz = {};  (function() {      var s = document.createElement('script'); s.type = "text/javascript";      s.async = true; s.src = "http://www.senzari.com/widgets/63/all.js?t=" + (new Date()).valueOf();      var a = document.getElementsByTagName('script')[0]; a.parentNode.insertBefore(s, a);  })(); </script>
<script type="text/javascript"> var _gaq = _gaq || []; _gaq.push(['_setAccount', 'UA-3131475-2']); _gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })(); </script>
</body>
</html>
