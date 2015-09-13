<?php
add_shortcode( 'ISW', 'Instagram_shortcode_page' );
function Instagram_shortcode_page( $Id ) {
    ob_start();
	
	/**
	 * Load Hex to Rgb color code function
	 */
	$CPT_Name = "ISW_shortcode";
	$All_Instagram = array( 'post_id' => $Id['id'], 'post_type' => $CPT_Name, 'orderby' => 'ASC');
	$loop = new WP_Query( $All_Instagram );

	while ( $loop->have_posts() ) : $loop->the_post();
	
	/**
     * Load Saved Instagram Shortcode Pro Settings
     */

    if(!isset($All_Instagram['post_id'])) {
        $All_Instagram['post_id'] = "";		
    } else {
		$ISW_Id = $All_Instagram['post_id'];
		$ISW_Shortcode_Settings = "ISW_Shortcode_Settings_".$ISW_Id;
		$ISW_Shortcode_Settings = unserialize(get_post_meta( $ISW_Id, $ISW_Shortcode_Settings, true));
		if(count($ISW_Shortcode_Settings)) {
			$ISW_Client_Id			= $ISW_Shortcode_Settings['ISW_Client_Id'];
			$ISW_Username			= $ISW_Shortcode_Settings['ISW_Username'];
			$ISW_Hashtag			= $ISW_Shortcode_Settings['ISW_Hashtag'];
			$ISW_Language			= "en";
			$ISW_Show_Heading		= $ISW_Shortcode_Settings['ISW_Show_Heading'];
			$ISW_Scroll				= $ISW_Shortcode_Settings['ISW_Scroll'];
			$ISW_Show_Title			= $ISW_Shortcode_Settings['ISW_Show_Title'];
			$ISW_Image_size			= $ISW_Shortcode_Settings['ISW_Image_size'];
			$ISW_Width        		= $ISW_Shortcode_Settings['ISW_Width'];
			$ISW_Height 			= $ISW_Shortcode_Settings['ISW_Height'];
			$ISW_Responsive         = $ISW_Shortcode_Settings['ISW_Responsive'];
			$ISW_Panel_button_color	= $ISW_Shortcode_Settings['ISW_Panel_button_color'];
			$ISW_bg_color			= $ISW_Shortcode_Settings['ISW_bg_color'];
			$ISW_Text_color			= "#fff";
			$ISW_Ban_by_username	= "";
		}
	}
	
	
	if($ISW_Responsive == "yes"){
		$ISW_Width = "100%";
	}else{
		$ISW_Width = $ISW_Width.'px';
	}
	$ISW_Height = $ISW_Height.'px';

	 ?>
	 <style>
		.instagram-title{
			font-weight: bold;font-size:18px;padding-bottom:20px; border-bottom:3px solid #f1f1f1; margin-bottom: 20px; text-align:center
		}
		@media (-webkit-min-device-pixel-ratio: 1.5), (min-device-pixel-ratio:1.5) {
			#ISW_container_<?php echo $ISW_Id; ?> .instalink-header-logo {
				background: url('<?php echo ISW_PLUGIN_URL.'images/instagram.GIF'; ?>') no-repeat;
				background-size: 18px 18px;
			}
		}
		#ISW_container_<?php echo $ISW_Id; ?> .instalink-cap::before {
			background: url('<?php echo ISW_PLUGIN_URL.'images/instagram.GIF'; ?>') no-repeat;
		}
		
		#ISW_container_<?php echo $ISW_Id; ?>{
			margin-bottom:40px;
		}
		ISW_container_<?php echo $ISW_Id; ?> a {
			border-bottom: none !important;
		}
	 </style>
	
	<div id="ISW_container_<?php echo $ISW_Id; ?>" style="widht:<?php echo $ISW_Width; ?>">
		<?php if($ISW_Show_Title=="yes"){?>
			<div class="instagram-title" style="width:<?php echo $ISW_Width;?>" >
				<?php echo get_the_title($ISW_Id);?>
			</div>
		<?php } ?>
		<div data-il data-il-client-id="<?php echo $ISW_Client_Id; ?>" data-il-username="<?php echo $ISW_Username; ?>" <?php if( $ISW_Hashtag != ""){?> data-il-hashtag="<?php echo $ISW_Hashtag;?>"<?php } ?> data-il-lang="<?php echo $ISW_Language; ?>" data-il-show-heading="<?php echo $ISW_Show_Heading; ?>" data-il-scroll="<?php echo $ISW_Scroll; ?>" data-il-width="<?php echo $ISW_Width; ?>" data-il-height="<?php echo $ISW_Height; ?>" data-il-image-size="<?php echo $ISW_Image_size; ?>" data-il-bg-color="<?php echo $ISW_Panel_button_color; ?>" data-il-content-bg-color="<?php echo $ISW_bg_color; ?>" data-il-font-color="<?php echo $ISW_Text_color; ?>" <?php if( $ISW_Ban_by_username != ""){?> data-il-ban="<?php echo $ISW_Ban_by_username;?>"<?php } ?>></div>
	</div>
<?php

    return ob_get_clean();
	endwhile;
}
?>