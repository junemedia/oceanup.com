<?php
/**
 * Load Saved Instagram Settings
 */
$PostId = $post->ID;
$ISW_Shortcode_Settings = "ISW_Shortcode_Settings_".$PostId;
$ISW_Shortcode_Settings = unserialize(get_post_meta( $PostId, $ISW_Shortcode_Settings, true));
if($ISW_Shortcode_Settings['ISW_Client_Id'] ) {
	$ISW_Client_Id			= $ISW_Shortcode_Settings['ISW_Client_Id'];
	$ISW_Username			= $ISW_Shortcode_Settings['ISW_Username'];
	$ISW_Hashtag			= $ISW_Shortcode_Settings['ISW_Hashtag'];
	$ISW_Show_Heading		= $ISW_Shortcode_Settings['ISW_Show_Heading'];
	$ISW_Scroll				= $ISW_Shortcode_Settings['ISW_Scroll'];
	$ISW_Show_Title			= $ISW_Shortcode_Settings['ISW_Show_Title'];
	$ISW_Image_size			= $ISW_Shortcode_Settings['ISW_Image_size'];
	$ISW_Width        		= $ISW_Shortcode_Settings['ISW_Width'];
	$ISW_Height 			= $ISW_Shortcode_Settings['ISW_Height'];
	$ISW_Responsive         = $ISW_Shortcode_Settings['ISW_Responsive'];
	$ISW_Panel_button_color	= $ISW_Shortcode_Settings['ISW_Panel_button_color'];
	$ISW_bg_color			= $ISW_Shortcode_Settings['ISW_bg_color'];
}
else{
	$ISW_Shortcode_Settings = unserialize(get_option( 'ISW_Settings' ));
	if(count($ISW_Shortcode_Settings)) {
		$ISW_Client_Id			= $ISW_Shortcode_Settings['ISW_Client_Id'];
		$ISW_Username			= $ISW_Shortcode_Settings['ISW_Username'];
		$ISW_Hashtag			= $ISW_Shortcode_Settings['ISW_Hashtag'];
		$ISW_Show_Heading		= $ISW_Shortcode_Settings['ISW_Show_Heading'];
		$ISW_Scroll				= $ISW_Shortcode_Settings['ISW_Scroll'];
		$ISW_Show_Title			= $ISW_Shortcode_Settings['ISW_Show_Title'];
		$ISW_Image_size			= $ISW_Shortcode_Settings['ISW_Image_size'];
		$ISW_Width        		= $ISW_Shortcode_Settings['ISW_Width'];
		$ISW_Height 			= $ISW_Shortcode_Settings['ISW_Height'];
		$ISW_Responsive         = $ISW_Shortcode_Settings['ISW_Responsive'];
		$ISW_Panel_button_color	= $ISW_Shortcode_Settings['ISW_Panel_button_color'];
		$ISW_bg_color			= $ISW_Shortcode_Settings['ISW_bg_color'];
	}
}
?>
<script>
jQuery(document).ready(function(){
	// Set value of "Image Size" DropDown
	 jQuery("#ISW_Image_size").val('<?php if($ISW_Image_size != ""){echo $ISW_Image_size;}else {echo "";}?>');
	 
	 if (jQuery('#ISW_Responsive').is(":checked"))
	{
		jQuery("#ISW_Width").attr("readonly", true); 
	}else{
		jQuery("#ISW_Width").attr("readonly", false); 
	}
			
	jQuery("#ISW_Responsive").click(function(){
		if (jQuery('#ISW_Responsive').is(":checked"))
		{
			jQuery("#ISW_Width").attr("readonly", true); 
		}else{
			jQuery("#ISW_Width").attr("readonly", false);
		}
	});
	 
});
	
</script>
<style>
label.custom-option.button {
    width: 20px !important;
}
label.custom-option {
    display: inline-block !important;
    height: 20px !important;
    padding: 0 !important;
    position: relative !important;
    vertical-align: top !important;
	border-style: inherit !important;
	border-radius: 0px !important;
}
*{
	-webkit-box-sizing:padding-box;
	-moz-box-sizing:padding-box;
	box-sizing:padding-box !important;
}
.file-input .btn {
	 line-height: 16px !important;
}
.fa{
	line-height: 3 !important;
}
</style>
<div class="row">
	<article class="col-sm-8 col-md-12">
		<div class="col-md-8 col-md-offset-2">
		
			<!-- START Registration form -->
			
			<div class="panel panel-form">
				<!-- Form header -->
				<div class="panel-heading">
					<h2 class="title">Instagram Setting Page</h2>
				</div>
				
				<div class="panel-body">
					<form role="form">
						<!-- Client Id -->
						<div class="form-group">
							<label for="client-id" class="control-label">Client Id<span class="required-field">*</span></label>
							<div class="has-feedback">
								<input type="text" class="form-control" id="ISW_Client_Id" name="ISW_Client_Id" value="<?php echo $ISW_Client_Id; ?>"/>
								<span class="fa fa-key form-control-feedback" aria-hidden="true"></span>
							</div>
							<label for="client-id" class="control-label">For Client ID <a href="https://weblizar.com/get-instagram-client-id/" target="_blank">Click Here</a></label>
						</div>
						
						<!-- Username -->
						<div class="form-group">
							<label for="username" class="control-label">User Name <span class="required-field">*</span></label>
							<div class="has-feedback">
								<input type="text" class="form-control" id="ISW_Username" name="ISW_Username" value="<?php echo $ISW_Username; ?>"/>
								<span class="fa fa-user form-control-feedback" aria-hidden="true"></span>
							</div>
						</div>
						
						<!-- Show Heading and Scroll checkbox -->
						<div class="form-group">
							<div class="col-sm-6 col-md-4">
								<div class="checkbox">
									<label class="custom-option button">
										<input type="checkbox" value="true"  <?php if($ISW_Show_Heading == 'true' ) { echo "checked"; } ?> name="ISW_Show_Heading" id="ISW_Show_Heading">
										<span class="button-checkbox"></span>
									</label>
									<label for="checkbox-lang-1">Show Heading</label>
								</div>
							</div>
							
							<div class="col-sm-6 col-md-4" style="display:none">
								<div class="checkbox">
									<label class="custom-option button">
										<input type="checkbox" value="true" <?php if($ISW_Scroll == 'true' ) { echo "checked"; } ?> name="ISW_Scroll" id="ISW_Scroll" >
										<span class="button-checkbox"></span>
									</label>
									<label for="checkbox-lang-2">Scroll</label>
								</div>
							</div>
							
							<div class="col-sm-6 col-md-4">
								<div class="checkbox">
									<label class="custom-option toggle" data-off="No" data-on="Yes">
										<input type="checkbox" id="ISW_Show_Title" name="ISW_Show_Title" <?php if($ISW_Show_Title == 'yes' ) { echo "checked"; } ?> value="yes" />
										<span class="button-checkbox"></span>
									</label>
									<label for="checkbox-toggle-lang-3-2">Show Title</label>
								</div>
							</div>	
						</div>
					
						<!-- Image size and Language Dropdown -->
						<div class="form-group">
							<div class="row">
								<div class="col-sm-4">
								<label class="control-label">Image size</label>
									<select class="custom-select" id="ISW_Image_size" name="ISW_Image_size">
										<option value="small">Small</option>
										<option value="medium">Medium</option>
										<!-- <option value="large">Large</option>
										<option value="xlarge">xLarge</option> -->
									</select>
								</div>
								
							</div>
						</div>
						
						<!-- Width, Responsive and Height option -->
						<div class="row">
							<div class="col-sm-4 form-group">
								<label for="Width" class="control-label">Width</label>
								<div class="has-feedback">
									<input type="text" class="form-control" id="ISW_Width" name="ISW_Width" value="<?php echo $ISW_Width;?>" />
									<span class="form-control-feedback" aria-hidden="true"><b>px</b></span>
								</div>
							</div>
							
							<div class="col-sm-4 form-group">
								<label for="name" class="control-label">&nbsp;</label>
								<div class="checkbox">
									<label class="custom-option button">
										<input type="checkbox" value="yes" <?php if($ISW_Responsive == 'yes') { echo "checked"; } ?> name="ISW_Responsive" id="ISW_Responsive">
										<span class="button-checkbox"></span>
									</label>
									<label for="checkbox-lang-2">Responsive</label>
								</div>
							</div>
							
							<div class="col-sm-4 form-group">
								<label for="height" class="control-label">height</label>
								<div class="has-feedback">
									<input type="text" class="form-control" id="ISW_Height" name="ISW_Height" value="<?php echo $ISW_Height;?>" />
									<span class="form-control-feedback" aria-hidden="true"><b>px</b></span>
								</div>
							</div>
						</div>
						
						<!-- Panel, button color -->
						<div class="form-group">
						<label for="Hashtag" class="control-label">Panel, button color</label>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									<label class="custom-option button">
										<input type="radio" id="ISW_Panel_button_color_1" name="ISW_Panel_button_color" <?php if($ISW_Panel_button_color == '#285989' ) { echo "checked"; } ?> value="#285989" checked="checked" />
										<span class="button-radio"></span>
									</label>
									<label for="radio-lang-1">Color 1</label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									<label class="custom-option button">
										<input type="radio" id="ISW_Panel_button_color_2" name="ISW_Panel_button_color" <?php if($ISW_Panel_button_color == '#dd5454' ) { echo "checked"; } ?> value="#dd5454" />
										<span class="button-radio"></span>
									</label>
									<label for="radio-lang-2">Color 2</label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									
									<label for="radio-lang-3"></label>
								</div>
							</div>
							<p><b>Upgrade to pro for get Unlimited Colour Scheme for Panel and button , </b> <a href="https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/" target="_new">check demo</a></p>
		
						</div>
						
						<!-- background color -->
						<div class="form-group">
						<label for="Hashtag" class="control-label"> background color</label>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									<label class="custom-option button">
										<input type="radio" id="ISW_bg_color_1" name="ISW_bg_color" <?php if($ISW_bg_color == '#fff' ) { echo "checked"; } ?> value="#fff" checked="checked" />
										<span class="button-radio"></span>
									</label>
									<label for="radio-lang-1">Color 1</label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									<label class="custom-option button">
										<input type="radio" id="ISW_bg_color_2" name="ISW_bg_color" <?php if($ISW_bg_color == '#c4c4c4' ) { echo "checked"; } ?> value="#c4c4c4" />
										<span class="button-radio"></span>
									</label>
									<label for="radio-lang-2">Color 2</label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									<label class="custom-option button">
										<input type="radio" id="ISW_bg_color_3" name="ISW_bg_color" <?php if($ISW_bg_color == 'transparent' ) { echo "checked"; } ?> value="transparent" />
										<span class="button-radio"></span>
									</label>
									<label for="radio-lang-3">Transparent</label>
								</div>
							</div>
							<p><b>Upgrade to pro for get Unlimited Colour Scheme for Background , </b> <a href="https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/" target="_new">check demo</a></p>
		
							</div>
						
						<!-- Hashtag -->
						<div class="form-group" >
							<label for="Hashtag" class="control-label">Hashtag </label>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									
									<label for="radio-lang-3"></label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									
									<label for="radio-lang-3"></label>
								</div>
							</div>
							<div class="col-sm-4 col-md-3">
								<div class="radio">
									
									<label for="radio-lang-3"></label>
								</div>
							</div>
							<p><b>Upgrade to pro for get Hashtag Fetaure in plugin , </b> <a href="https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/" target="_new">check demo</a></p>
		
						</div>
					</form>
				</div>
				
				<!-- Form footer -->
				
			<div class="panel-footer">
				<span class="required-field">*</span> - required field
				<div style="float:right">
				<style>
				.fag-rate-us {
				  color: #F8504B !important;
				  margin-bottom:5px;
				}
				.fag-rate-us span.dashicons {
				  width: 30px;
				  height: 30px;
				  margin-top: -4px;
				}
				</style>
				
			</div>
		</div>
			
		<!-- END Registration form -->
		<div>
		<h3>If you like our plugin then please show us some Love, Rate Us On WordPress : <h3> 
					<a class="upgrade-to-pro-demo fag-rate-us" style=" text-decoration: none; height: 40px; width: 40px;" href="https://wordpress.org/plugins/instagram-shortcode-and-widget/" target="_blank">
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
						<span class="dashicons dashicons-star-filled"></span>
					</a>
		</div>
		</div>
		
	</article>
		
	<div class="clearfix"></div>
</div>
		
		<script>
			jQuery(document).ready(function() {
				jQuery('.custom-select').fancySelect(); // Custom select
				jQuery('[data-toggle="tooltip"]').tooltip() // Tooltip
			});
		</script>
