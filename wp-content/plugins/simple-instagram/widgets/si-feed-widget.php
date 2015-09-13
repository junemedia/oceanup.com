<?php

/**
 * SI Feed Widget Class
 *
 * Creates the SI Feed Widget
 *
 * @package simple-instagram
 */
require_once( SI_PLUGIN_DIR . '/inc/class-simple-instagram.php' );

class SI_Feed_Widget extends WP_Widget {

    public function SI_Feed_Widget() {
        $widget_ops  = array( 'classname' => 'si_feed_widget', 'description' => __( 'A widget to display your Instagram Feed', 'si_feed' ) );
        $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'si_feed_widget' );
        
        $this->WP_Widget( 'si_feed_widget', __( 'Simple Instagram Feed Widget', 'si_feed' ), $widget_ops, $control_ops );
    }

    public function widget( $args, $instance ) {

        extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );
        $count = $instance['count'];
        $user  = isset( $instance['user'] ) ? $instance['user'] : '';
        $user  = $user == '' ? 'self' : $user;

        echo $before_widget;

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }

        $instagram = new Simple_Instagram();
        $feed      = $instagram->get_user_media( $user, $count );
        $return    = '';

        if ( $feed && count( $feed ) > 0 ) {

            $return = '<div class="si_feed_widget">';

            $return .= '<ul class="si_feed_list">';

            foreach ( $feed as $image ) {

                $url = $image->images->standard_resolution->url;

                // Fix https
                $url = str_replace( 'http://', '//', $url );

                $return .= '<li class="si_item">';

                $return .= '<a href="' . $image->link . '" target="_blank">';

                $image_caption = is_object( $image->caption ) ? $image->caption->text : '';
                $return .= '<img alt="'. $image_caption . '" src="' . $url . '">';
                $return .= '</a>';
                $return .= '</li>';
            }

            $return .= '</ul>';

            $return .= '</div>';

        } 

        echo $return;

        echo $after_widget;
    }

    public function update( $new_instance, $old_instance ) {

        $instance = $old_instance;

        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['count'] = $new_instance['count'];
        $instance['user']  = $new_instance['user'];

        return $instance;

    }

    public function form( $instance ) {

        $defaults = array( 'title' => __( 'From Instagram', 'simple-instagram' ), 'count' => __( '0', 'simple-instagram' ), 'user' => __( '', 'simple-instagram' ) );
        $instance = wp_parse_args( (array) $instance, $defaults );
        $style    = 'width:100%;' ?>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php _e( 'Title:', 'simple-instagram' ); ?>
            </label>
            <input 
                id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" 
                value="<?php echo esc_attr( $instance['title'] ); ?>" 
                style="<?php echo esc_attr( $style ); ?>" 
            />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'user' ) ); ?>">
                <?php _e( 'User ID (leave blank to use your own feed):', 'simple-instagram' ); ?>
            </label>
            <input 
                id="<?php echo esc_attr( $this->get_field_id( 'user' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'user' ) ); ?>" 
                value="<?php echo esc_attr( $instance['user'] ); ?>" 
                style="<?php echo esc_attr( $style ); ?>" 
            />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
                <?php _e( 'Number of Images (0 for Unmlimited):', 'simple-instagram' ); ?>
            </label>
            <input id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" 
                value="<?php echo esc_attr( $instance['count'] ); ?>" 
                style="<?php echo esc_attr( $style ); ?>" 
            />
        </p>

      <?php
    }

}