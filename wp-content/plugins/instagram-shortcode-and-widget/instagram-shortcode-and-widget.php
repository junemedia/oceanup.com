<?php
/**
 * Plugin Name: Instagram Shortcode And Widget
 * Version: 1.1
 * Description: A jQuery responsive Instagram widget. The widget blends well with the design of your site because you can change its color.You can display the information from your account or by any hashtag.
 * Author: Weblizar
 * Author URI: https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/
 * Plugin URI: https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/
 */
 
/**
 * Constant Variable
 */
define("ISW_TEXT_DOMAIN","ISW_TEXT_DOMAIN" );
define("ISW_PLUGIN_URL", plugin_dir_url(__FILE__));

add_action('plugins_loaded', 'ISW_Language_Translater');
function ISW_Language_Translater() {
	load_plugin_textdomain(ISW_TEXT_DOMAIN, FALSE, dirname( plugin_basename(__FILE__)).'/languages/' );
}

// Apply default settings on activation
register_activation_hook( __FILE__, 'ISW_Default_settings' );
function ISW_Default_settings(){
    $DefaultSettingsArray = serialize( array(
		'ISW_Client_Id'          	=> "",
		'ISW_Username'          	=> "",
		'ISW_Hashtag'     			=> "",
		'ISW_Show_Heading'         	=> "true",
		'ISW_Scroll'				=> "true",
		'ISW_Show_Title'			=> "yes",
		'ISW_Image_size'     		=> "medium",
        'ISW_Width'      			=> "400",
		'ISW_Height'      			=> "400",
		'ISW_Responsive'			=> "yes",
        'ISW_Panel_button_color'  	=> "#285989",
        'ISW_bg_color'				=> '#fff'
    ));
    add_option("ISW_Settings", $DefaultSettingsArray);	
}

// Function To Remove Feature Image
function ISW_remove_feature_image() {
	remove_meta_box('postimagediv','isw_shortcode','side');
}
add_action('do_meta_boxes', 'ISW_remove_feature_image');


/**
* Short Code Detach Function To UpLoad JS And CSS
*/  
function ISW_Shortcode_detect() {
    global $wp_query;
    $Posts = $wp_query->posts;
    $Pattern = get_shortcode_regex();

	/**   
    * css and js
    */
	wp_enqueue_script('jquery');
	wp_enqueue_style('instalink-min-css-1', ISW_PLUGIN_URL.'css/instalink-1.5.0.min.css');
	wp_enqueue_script( 'instalink-min-js-1', ISW_PLUGIN_URL.'js/instalink-1.5.0.min.js');		
}
add_action( 'wp_enqueue_scripts', 'ISW_Shortcode_detect' );

add_filter( 'widget_text', 'do_shortcode' );

add_action('admin_menu' , 'ISW_SettingsPage');
function ISW_SettingsPage() {
    add_submenu_page('edit.php?post_type=isw_shortcode', 'Upgrade To Pro', 'Upgrade To Pro', 'administrator', 'isw-get-instagram-shortcode-pro', 'isw_get_instagram_shortcode_pro_page');
	add_submenu_page('edit.php?post_type=isw_shortcode', __('Help and Support', ISW_TEXT_DOMAIN), __('Help and Support', ISW_TEXT_DOMAIN), 'administrator', 'ISW-help-page', 'ISW_Help_and_Support_page');
}

function isw_get_instagram_shortcode_pro_page() {
    //css
	wp_enqueue_style('isw-font-awesome', ISW_PLUGIN_URL.'css/font-awesome.min.css');
	wp_enqueue_style('isw-pricing-table-css', ISW_PLUGIN_URL.'css/pricing-table.css');
    wp_enqueue_style('bootstrap.min.css-1', ISW_PLUGIN_URL.'css/bootstrap.min.css');
    require_once("get-instagram-shortcode-pro.php");
}

function ISW_Help_and_Support_page() {
	wp_enqueue_style('bootstrap.min.css-1', ISW_PLUGIN_URL.'css/bootstrap.min.css');
    require_once("isw_help_and_support.php");
}

class ISW {
    private static $instance;
	var $counter;

    public static function forge() {
        if (!isset(self::$instance)) {
            $className = __CLASS__;
            self::$instance = new $className;
        }
        return self::$instance;
    }
	
	private function __construct() {
		$this->counter = 0;
        add_action('admin_print_scripts-post.php', array(&$this, 'ISW_admin_print_scripts'));
        add_action('admin_print_scripts-post-new.php', array(&$this, 'ISW_admin_print_scripts'));
        //add_shortcode('ispgallery', array(&$this, 'shortcode'));
        if (is_admin()) {
			add_action('init', array(&$this, 'IstagramShortcode'), 1);
			add_action('add_meta_boxes', array(&$this, 'add_all_isw_meta_boxes'));
			add_action('admin_init', array(&$this, 'add_all_isw_meta_boxes'), 1);
			add_action('save_post', array(&$this, 'ISW_Save_Settings'), 9, 1);
		}
    }
	
	//Required JS & CSS
	public function ISW_admin_print_scripts() {
		if ( 'isw_shortcode' == $GLOBALS['post_type'] ) {		
			
			wp_enqueue_style('googlefont.css-1', ISW_PLUGIN_URL.'css/googlefont.css');
			wp_enqueue_style('font-awesome.min.css-1', ISW_PLUGIN_URL.'css/font-awesome.min.css');
			wp_enqueue_style('bootstrap.min.css-1', ISW_PLUGIN_URL.'css/bootstrap.min.css');
			wp_enqueue_style('flat-form.css-1', ISW_PLUGIN_URL.'css/flat-form.css');

			wp_enqueue_script('jquery');
			wp_enqueue_script( 'bootstrap.min.js-1', ISW_PLUGIN_URL.'js/bootstrap.min.js');	
			wp_enqueue_script( 'fancySelect.js-1', ISW_PLUGIN_URL.'js/fancySelect.js');
			
		}
	}
	
	// Register Custom Post Type
	public function IstagramShortcode() {
		$labels = array(
			'name' => __('Instagram Shortcode And Widget','ISW_TEXT_DOMAIN' ),
			'singular_name' => __('Instagram Shortcode And Widget','ISW_TEXT_DOMAIN' ),
			'add_new' => __('Add New Instagram', 'ISW_TEXT_DOMAIN' ),
			'add_new_item' => __('Add New Instagram', 'ISW_TEXT_DOMAIN' ),
			'edit_item' => __('Edit Instagram', 'ISW_TEXT_DOMAIN' ),
			'new_item' => __('New Instagram', 'ISW_TEXT_DOMAIN' ),
			'view_item' => __('View Instagram', 'ISW_TEXT_DOMAIN' ),
			'search_items' => __('Search Instagram', 'ISW_TEXT_DOMAIN' ),
			'not_found' => __('No Instagram found', 'ISW_TEXT_DOMAIN' ),
			'not_found_in_trash' => __('No Instagram found in Trash', 'ISW_TEXT_DOMAIN' ),
			'parent_item_colon' => __('Parent Instagram:', 'ISW_TEXT_DOMAIN' ),
			'all_items' => __('All Instagram Shortcode', 'ISW_TEXT_DOMAIN' ),
			'menu_name' => __('Instagram Shortcode', 'ISW_TEXT_DOMAIN' ),
		);

		$args = array(
			'labels' => $labels,
			'hierarchical' => false,
			'supports' => array( 'title','thumbnail'),
			'public' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'menu_position' => 10,
			'menu_icon' => 'dashicons-camera',
			'show_in_nav_menus' => false,
			'publicly_queryable' => false,
			'exclude_from_search' => true,
			'has_archive' => true,
			'query_var' => true,
			'can_export' => true,
			'rewrite' => false,
			'capability_type' => 'post'
		);

        register_post_type( 'isw_shortcode', $args );
        add_filter( 'manage_edit-isw_gallery_columns', array(&$this, 'isw_gallery_columns' )) ;
        add_action( 'manage_isw_gallery_posts_custom_column', array(&$this, 'isw_gallery_manage_columns' ), 10, 2 );
	}
	
	function isw_gallery_columns( $columns ){
        $columns = array(
            'cb' => '<input type="checkbox" />',
            'title' => __( 'Instagram Shortcode Pro','ISW_TEXT_DOMAIN' ),
            'shortcode' => __( 'Instagram Shortcode','ISW_TEXT_DOMAIN' ),
            'date' => __( 'Date','ISW_TEXT_DOMAIN' )
        );
        return $columns;
    }

    function isw_gallery_manage_columns( $column, $post_id ){
        global $post;
        switch( $column ) {
          case 'shortcode' :
            echo '<input type="text" value="[ISW id='.$post_id.']" readonly="readonly" />';
            break;
          default :
            break;
        }
    }
	
	public function add_all_isw_meta_boxes() {	
		add_meta_box( __('Apply Setting On Instagram', 'ISW_TEXT_DOMAIN'), __('Apply Setting On Instagram', 'ISW_TEXT_DOMAIN'), array(&$this, 'ISW_settings_meta_box_function'), 'isw_shortcode', 'normal', 'low');
		
		add_meta_box ( __('Gallery Shortcode', 'ISW_TEXT_DOMAIN'), __('Gallery Shortcode', 'ISW_TEXT_DOMAIN'), array(&$this, 'ISW_shotcode_meta_box_function'), 'isw_shortcode', 'side', 'low');
		
		// Go for a widget
		add_meta_box(__('Activate Instagram Widget', 'ISW_TEXT_DOMAIN') , __('Activate Instagram Widget', 'ISW_TEXT_DOMAIN'), array(&$this,'ISW_use_widget_meta_box'), 'isw_shortcode', 'side', 'low');
		
		// Rate Us Meta Box
		add_meta_box(__('Get Instagram Shortcode and Widget Pro Only In $6', 'ISW_TEXT_DOMAIN') , __('Get Instagram Shortcode and Widget Pro Only In $6', 'ISW_TEXT_DOMAIN'), array(&$this,'ISW_upgrade_to_pro_image_meta_box_function'), 'isw_shortcode', 'side', 'low');
		
		// Rate Us Meta Box
		add_meta_box(__('Show us some love, Rate Us', 'ISW_TEXT_DOMAIN') , __('Show us some love, Rate Us', 'ISW_TEXT_DOMAIN'), array(&$this,'ISW_Rate_us_meta_box_function'), 'isw_shortcode', 'side', 'low');
		// Upgrade To Pro Version Meta Box
		add_meta_box(__('Upgrade To Pro Version', 'ISW_TEXT_DOMAIN') , __('Upgrade To Pro Version', 'ISW_TEXT_DOMAIN'), array(&$this,'ISW_upgrade_to_pro_function'), 'isw_shortcode', 'side', 'low');
		// Pro Features Meta Box
		add_meta_box(__('Pro Features', 'ISW_TEXT_DOMAIN') , __('Pro Features', 'ISW_TEXT_DOMAIN'), array(&$this,'ISW_pro_features'), 'isw_shortcode', 'side', 'low');
	}

	/**
	 * loads all saved Instagram Shortcode Settings
	 */
    public function ISW_settings_meta_box_function($post) { 
		require_once('instagram-metabox.php');
	}
	
	public function ISW_shotcode_meta_box_function() { ?>
		<p><?php _e("Use below shortcode in any Page/Post to publish your Instagram", ISW_TEXT_DOMAIN);?></p>
		<input readonly="readonly" type="text" value="<?php echo "[ISW id=".get_the_ID()."]"; ?>">
		<?php 
	}
	
	// Widget area path meta box
	function ISW_use_widget_meta_box(){
	?>
		<div>
			<p>To activate widget into any widget area</p>
			<p><a href="<?php get_site_url();?>./widgets.php" >Click Here</a>. </p>
			<p>Find <b>Instagram Widget</b> and place it to your widget area. Select any Instagram for the dropdown and save changes.</p>
		</div>	
	<?php 
	}
	
	// Upgarde To Pro Box Function
	function ISW_upgrade_to_pro_image_meta_box_function(){
		?>
		<div>
		<a href="https://wordpress.org/plugins/instagram-shortcode-and-widget/" target="_blank" > <img src="<?php echo  ISW_PLUGIN_URL.'images/instagram-shortcode-pro.png'; ?>" alt="" style="width:100%;height:auto"/></a>
			<div class="upgrade-to-pro" style="text-align:center;margin-bottom:10px;">
			<a href="https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/" target="_new" class="button button-primary button-hero">Try To Pro Now</a>
		</div>	
		</div>
		<?php 
	}
	// Rate Us Meta Box Function
	function ISW_Rate_us_meta_box_function() { ?>
		<style>
		.fag-rate-us span.dashicons{
			width: 30px;
			height: 30px;
		}
		.fag-rate-us span.dashicons-star-filled:before {
			content: "\f155";
			font-size: 30px;
		}
		</style>
		<div align="center">
			<p>Please Review & Rate Us On WordPress</p>
			<a class="upgrade-to-pro-demo .fag-rate-us" style=" text-decoration: none; height: 40px; width: 40px;" href="https://wordpress.org/plugins/instagram-shortcode-and-widget/" target="_blank">
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
				<span class="dashicons dashicons-star-filled"></span>
			</a>
		</div>
		<div class="upgrade-to-pro-demo" style="text-align:center;margin-bottom:10px;margin-top:10px;">
			<a href="https://wordpress.org/plugins/instagram-shortcode-and-widget/" target="_blank" class="button button-primary button-hero">RATE US</a>
		</div>
		<?php
	}
	
	// Upgarde to Pro Meta Box Function
	function ISW_upgrade_to_pro_function(){
		?>
		<div class="upgrade-to-pro-demo" style="text-align:center;margin-bottom:10px;margin-top:10px;">
			<a href="http://demo.weblizar.com/instagram-shortcode-and-widget-pro/"  target="_new" class="button button-primary button-hero">View Live Demo</a>
		</div>
		<div class="upgrade-to-pro-admin-demo" style="text-align:center;margin-bottom:10px;">
			<a href="http://demo.weblizar.com/instagram-shortcode-and-widget-pro-admin-demo/wp-admin" target="_new" class="button button-primary button-hero">View Admin Demo</a>
		</div>
		<div class="upgrade-to-pro" style="text-align:center;margin-bottom:10px;">
			<a href="https://weblizar.com/plugins/instagram-shortcode-and-widget-pro/" target="_new" class="button button-primary button-hero">Upgarde To Pro</a>
		</div>
		<?php
	}
	
	// Pro Features Meta Box Function
	function ISW_pro_features(){
	?>
		<ul style="">
			<li class="plan-feature">Responsive Design</li>
			<li class="plan-feature">Instagram Widget Option</li>
			<li class="plan-feature">Hashtag Option</li>
			<li class="plan-feature">Profile with Hashtag Option</li>
			<li class="plan-feature">Rectangle/Circle Image Design Layout</li>
			<li class="plan-feature">Unlimited Panel and button color</li>
			<li class="plan-feature">Unlimited background color</li>
			<li class="plan-feature">Unlimited Text color Option</li>
			<li class="plan-feature">Unlimited counter label Color Option</li>
			<li class="plan-feature">Ban by username Option</li>
			<li class="plan-feature">Upload Logo Option</li>
			<li class="plan-feature">Language Option</li>
			<li class="plan-feature">Responsive Instagram Layout</li>
			<li class="plan-feature">Instagram has Unique Shortcode</li>
			<li class="plan-feature">Shortcode Button on post or page</li>
			<li class="plan-feature">Unique settings for each Instagram</li>
			<li class="plan-feature">Hide/Show Instagram Title</li>
			<li class="plan-feature">Hide/Show Heading</li>
			<li class="plan-feature">Hide/Show Scroll</li>
			
		</ul>
	<?php 
	} 

	//save settings meta box values
	public function ISW_Save_Settings($PostID) {		
	  if(isset($PostID) && isset($_POST['ISW_Client_Id'])){
		
		$ISW_Client_Id  		= $_POST['ISW_Client_Id'];
		$ISW_Username    		= $_POST['ISW_Username'];
		$ISW_Hashtag    		= $_POST['ISW_Hashtag'];
		if(isset($_POST['ISW_Show_Heading'])){
			$ISW_Show_Heading		= $_POST['ISW_Show_Heading'];
		}else{
			$ISW_Show_Heading		= "false";
		}
		if(isset($_POST['ISW_Scroll'])){
			$ISW_Scroll				= $_POST['ISW_Scroll'];
		}else{
			$ISW_Scroll				= "false";
		}
		if(isset($_POST['ISW_Show_Title'])){
			$ISW_Show_Title			= $_POST['ISW_Show_Title'];
		}else{
			$ISW_Show_Title			= "no";
		}
		$ISW_Image_size			= $_POST['ISW_Image_size'];
		$ISW_Width 				= $_POST['ISW_Width'];
		$ISW_Height				= $_POST['ISW_Height'];
		$ISW_Responsive			= $_POST['ISW_Responsive'];
		$ISW_Panel_button_color	= $_POST['ISW_Panel_button_color'];
		$ISW_bg_color           = $_POST['ISW_bg_color'];
		

		$ISW_Settings_Array = serialize( array(
			'ISW_Client_Id'				=> $ISW_Client_Id,
			'ISW_Username'     			=> $ISW_Username,
			'ISW_Hashtag'    			=> $ISW_Hashtag,
			'ISW_Show_Heading'			=> $ISW_Show_Heading,
			'ISW_Scroll'     			=> $ISW_Scroll,
			'ISW_Show_Title'     		=> $ISW_Show_Title,
			'ISW_Image_size'      		=> $ISW_Image_size,
			'ISW_Width'      			=> $ISW_Width,
			'ISW_Height'         		=> $ISW_Height,
			'ISW_Responsive'         	=> $ISW_Responsive,
			'ISW_Panel_button_color'	=> $ISW_Panel_button_color,
			'ISW_bg_color'				=> $ISW_bg_color
		) );

		$ISW_Shortcode_Settings = "ISW_Shortcode_Settings_".$PostID;
		update_post_meta($PostID, $ISW_Shortcode_Settings, $ISW_Settings_Array);
	  }
	}
}

global $ISW;
$ISW = ISW::forge();

/**
 * ISW Short Code [ISW].
 */
 require_once("instagram-page-use-code.php");
 require_once("instagram-widget.php");

?>