<?php
/**
 * Loop
 *
 * This is the default loop file, containing the looping logic for use in all templates
 * where a loop is required.
 *
 * To override this loop in a particular context (in all archives, for example), create a
 * duplicate of this file and rename it to `loop-archive.php`. Make any changes to this
 * new file and they will be reflected on all your archive screens.
 *
 * @package WooFramework
 * @subpackage Template
 */
 global $more; $more = 0;

woo_loop_before();

if (have_posts()) { $count = 0;
?>

<div class="fix"></div>

<?php
	while (have_posts()) { the_post(); $count++;
		woo_get_template_part( 'content', get_post_type() );
		if( $count == 2 ) { ?>

<?php // OpenX mid-post ad unit, gets initiated in adunits.js ?>
<div class="widget adspace-widget">
	<div id="538014858_300x250MP-1" style="width:300px;height:250px;margin:0;padding:0">
		<noscript>
			<iframe id="87cd89828f" name="87cd89828f" src="http://ox-d.junemedia.com/w/1.0/afr?auid=538014858&cb=INSERT_RANDOM_NUMBER_HERE" frameborder="0" scrolling="no" width="300" height="250">
				<a href="http://ox-d.junemedia.com/w/1.0/rc?cs=87cd89828f&cb=INSERT_RANDOM_NUMBER_HERE" >
					<img src="http://ox-d.junemedia.com/w/1.0/ai?auid=538014858&cs=87cd89828f&cb=INSERT_RANDOM_NUMBER_HERE" border="0" alt="">
				</a>
			</iframe>
		</noscript>
	</div>
</div>

			<?php
		}
		if( $count == 4 ) { ?>

<?php // OpenX mid-post ad unit, gets initiated in adunits.js ?>
<div class="widget adspace-widget">
	<div id="538096513_300x250MP-2" style="width:300px;height:250px;margin:0;padding:0">
		<noscript>
			<iframe id="9d7a4543fe" name="9d7a4543fe" src="http://ox-d.junemedia.com/w/1.0/afr?auid=538096513&cb=INSERT_RANDOM_NUMBER_HERE" frameborder="0" scrolling="no" width="300" height="250">
				<a href="http://ox-d.junemedia.com/w/1.0/rc?cs=9d7a4543fe&cb=INSERT_RANDOM_NUMBER_HERE" >
					<img src="http://ox-d.junemedia.com/w/1.0/ai?auid=538096513&cs=9d7a4543fe&cb=INSERT_RANDOM_NUMBER_HERE" border="0" alt="">
				</a>
			</iframe>
		</noscript>
	</div>
</div>

			<?php

		}

	} // End WHILE Loop

?>
<?php // CrowdIgnite unit, gets initiated in adunits.js ?>
<div class="widget widget_text ci_widget">
	<div class="textwidget">
		<h3 style="border:none;box-sizing:border-box;padding:0 .4em;text-transform:capitalize;text-align:left;margin:0 auto;">More from around the web</h3>
		<div id="post_CI_widget"></div>
	</div>
</div>

<?php



} else {
	get_template_part( 'content', 'noposts' );
} // End IF Statement

woo_loop_after();

woo_pagenav();
?>
