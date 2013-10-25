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
</body>
</html>
