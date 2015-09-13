<?php
/**
 * Adds Foo_Widget widget.
 */
class InstagramShortcodeWidget extends WP_Widget {

    /**
     * Register widget with WordPress.
     */
    function __construct() {
        parent::__construct(
            'isw_widget', // Base ID
            'Instagram widget', // Name
            array( 'description' => __( 'A jQuery responsive Instagram widget which can display the information from your account or by any hashtag', ISW_TEXT_DOMAIN ), ) // Args
		
        );
		
	}

    /**
     * Front-end display of widget.
     */
    public function widget( $args, $instance ) {
        $Title    	=   apply_filters( 'isw_widget_title', $instance['Title'] );
		echo $args['before_widget'];
		
		 $InstagramId	=   apply_filters( 'isw_widget_shortcode', $instance['Shortcode'] ); 

		if(is_numeric($InstagramId)) {
			if ( ! empty( $instance['Title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['Title'] ). $args['after_title'];
			}
			echo do_shortcode( '[ISW id='.$InstagramId.']' );
		} else {
			echo "<p>Sorry! No Instagram Shortcode Found.</p>";
		}
		echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance Previously saved values from database.
     */
    public function form( $instance ) {

        if ( isset( $instance[ 'Title' ] ) ) {
            $Title = $instance[ 'Title' ];
        } else {
            $Title = "Instagram Shortcode";
        }

        if ( isset( $instance[ 'Shortcode' ] ) ) {
            $Shortcode = $instance[ 'Shortcode' ];
        } else {
            $Shortcode = "Select Any Instagram";
        }
		
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'Title' ); ?>"><?php _e( 'Widget Title' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'Title' ); ?>" name="<?php echo $this->get_field_name( 'Title' ); ?>" type="text" value="<?php echo esc_attr( $Title ); ?>">
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'Shortcode' ); ?>"><?php _e( 'Select Instagram' ); ?> (Required)</label>
			<?php
				/**
				 * Get All Instagram Shortcode Custom Post Type
				 */
				$ISW_CPT_Name = "isw_shortcode";
				$ISW_All_Posts = wp_count_posts( $ISW_CPT_Name )->publish;
				global $All_Instagram;
				$All_Instagram = array('post_type' => $ISW_CPT_Name, 'orderby' => 'ASC', 'posts_per_page' => $ISW_All_Posts);
				$All_Instagram = new WP_Query( $All_Instagram );		
				?>
				<select id="<?php echo $this->get_field_id( 'Shortcode' ); ?>" name="<?php echo $this->get_field_name( 'Shortcode' ); ?>" style="width: 100%;">
						<option value="Select Any Instagram" <?php if($Shortcode == "Select Any Instagram") echo 'selected="selected"'; ?>>Select Any Instagram</option>
				<?php
				if( $All_Instagram->have_posts() ) {	 ?>	
						<?php while ( $All_Instagram->have_posts() ) : $All_Instagram->the_post();	
							$PostId = get_the_ID(); 
							$PostTitle = get_the_title($PostId);
						?>
						<option value="<?php echo $PostId; ?>" <?php if($Shortcode == $PostId) echo 'selected="selected"'; ?>><?php if($PostTitle) echo $PostTitle; else _e("No Title", ISW_TEXT_DOMAIN); ?></option>
						<?php endwhile; ?>
					<?php
				}  else  { 
					echo "<option>Sorry! No Instagram Shortcode Found.</option>";
				}
			?>
				</select>
        </p>
        <?php
    }

    /**
     * Sanitize widget form values as they are saved.
     */
    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['Title'] = ( ! empty( $new_instance['Title'] ) ) ? strip_tags( $new_instance['Title'] ) : '';
        $instance['Shortcode'] = ( ! empty( $new_instance['Shortcode'] ) ) ? strip_tags( $new_instance['Shortcode'] ) : 'Select Any Instagram';
        
        return $instance;
    }

} // end of class Instagram Shortcode Pro Widget Class

// Register Instagram Shortcode Pro Widget
function InstagramShortcodeWidget() {
    register_widget( 'InstagramShortcodeWidget' );
}
add_action( 'widgets_init', 'InstagramShortcodeWidget' );
?>