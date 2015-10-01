<?php
/**
 * Top Ad Sidebar Template
 *
 * @package WooFramework
 * @subpackage Template
 */
	global $woo_options;

	$barname = 'top-sidebar-ad-area';
	$barsuffix = '';
	if (is_singular(array('attachment', 'oc_gallery'))) $barsuffix = '-photos';
	woo_sidebar_before();
?>
<aside id="sidebar-top-ad">

  <?php // these four widgets all get initiated in js/adunits.js ?>
	<div class="widget adspace-widget">
		<div id="538014855_300x250ATF" style="width:300px;height:250px;margin:0;padding:0">
			<noscript>
				<iframe id="cecbd3d7da" name="cecbd3d7da" src="//ox-d.junemedia.com/w/1.0/afr?auid=538014855&cb=INSERT_RANDOM_NUMBER_HERE" frameborder="0" scrolling="no" width="300" height="250">
					<a href="//ox-d.junemedia.com/w/1.0/rc?cs=cecbd3d7da&cb=INSERT_RANDOM_NUMBER_HERE" >
						<img src="//ox-d.junemedia.com/w/1.0/ai?auid=538014855&cs=cecbd3d7da&cb=INSERT_RANDOM_NUMBER_HERE" border="0" alt="">
					</a>
				</iframe>
			</noscript>
		</div>
	</div>

	<div class="widget adspace-widget">
		<div id="538014856_300x250BTF" style="width:300px;height:250px;margin:0;padding:0">
			<noscript>
				<iframe id="5fb32e2163" name="5fb32e2163" src="http://ox-d.junemedia.com/w/1.0/afr?auid=538014856&cb=INSERT_RANDOM_NUMBER_HERE" frameborder="0" scrolling="no" width="300" height="250">
					<a href="http://ox-d.junemedia.com/w/1.0/rc?cs=5fb32e2163&cb=INSERT_RANDOM_NUMBER_HERE" >
						<img src="http://ox-d.junemedia.com/w/1.0/ai?auid=538014856&cs=5fb32e2163&cb=INSERT_RANDOM_NUMBER_HERE" border="0" alt="">
					</a>
				</iframe>
			</noscript>
		</div>
	</div>

	<div class="widget widget-text">
		<div class="textwidget">
			<h3 style="border:none;box-sizing:border-box;padding:0 .4em;text-transform:capitalize;width:270px;text-align:left;margin:0 auto;">We Recommend</h3>
			<div id="_CI_widget_34389"></div>
			<style>
				#_CI_widget_34389 { width: 270px; margin-right: auto; margin-left: auto; }
				#_CI_widget_34389 ci_image_anchor img { border-color: transparent; }
			</style>
		</div>
	</div>

	<div class="widget adspace-widget">
		<div id="538096517_160x600BTF" style="width:300px;height:600px;margin:0;padding:0">
			<noscript>
				<iframe id="47ca519d95" name="47ca519d95" src="http://ox-d.junemedia.com/w/1.0/afr?auid=538096517&cb=INSERT_RANDOM_NUMBER_HERE" frameborder="0" scrolling="no" width="300" height="600">
					<a href="http://ox-d.junemedia.com/w/1.0/rc?cs=47ca519d95&cb=INSERT_RANDOM_NUMBER_HERE" >
						<img src="http://ox-d.junemedia.com/w/1.0/ai?auid=538096517&cs=47ca519d95&cb=INSERT_RANDOM_NUMBER_HERE" border="0" alt="">i
					</a>
				</iframe>
			</noscript>
		</div>
	</div>
  <?php /* end adunits.js widgets */ ?>

<?php
	woo_sidebar_inside_before();
	woo_sidebar( $barname.$barsuffix );
	woo_sidebar_inside_after();
?>

</aside><!-- /#sidebar-top-ad -->
<?php
	woo_sidebar_after();
?>
