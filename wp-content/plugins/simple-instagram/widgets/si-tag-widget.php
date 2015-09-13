<?php

/**
 * SI Tag Widget Class
 *
 * Creates the SI Tag Widget
 *
 * @package simple-instagram
 */
class SI_Tag_Widget extends WP_Widget {

    public function SI_Tag_Widget() {
        $widget_ops  = array( 'classname' => 'si_tag_widget', 'description' => __( 'A widget to display an Instagram Feed by Tag', 'si_feed' ) );
        $control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'si_tag_widget' );

        $this->WP_Widget( 'si_tag_widget', __( 'Simple Instagram Tag Widget', 'si_tag' ), $widget_ops, $control_ops );
    }

    public function widget( $args, $instance ) {

        extract( $args );

        $title = apply_filters( 'widget_title', $instance['title'] );
        $count = $instance['count'];
        
        if ( $count > 25 ) {
            $count = 25;
        }

        $tag = $instance['tag'];
        
        echo $before_widget;

        if ( $title ) {
            echo $before_title . $title . $after_title;
        }
        
        $instagram = new Simple_Instagram();
        $feed      = $instagram->get_tagged_media( $tag, $count );

        if ( ! $feed || 0 == count( $feed ) ) {
            echo '';
            return;
        }

        $return  = '<div class="si_feed_widget">';
        $return .= '<ul class="si_feed_list">';

        foreach ( $feed as $image ) {

            $url     = $image->images->standard_resolution->url;
            $url     = str_replace( 'http://', '//', $url );
            $return .= '<li class="si_item">';
            $return .= '<a href="' . $image->link . '" target="_blank">';
            $image_caption = is_object( $image->caption ) ? $image->caption->text : '';
            $return .= '<img alt="'. $image_caption . '" src="' . $url . '">';
            $return .= '</a>';
            $return .= '</li>';
        }

        $return .= '</ul>';
        $return .= '</div>';

        echo $return;

        echo $after_widget;
    }


    public function update( $new_instance, $old_instance ) {
        $instance          = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['count'] = $new_instance['count'];
        $instance['tag']   = strip_tags( $new_instance['tag'] );
        return $instance;
    }

    public function form( $instance ) {

        $defaults = array( 'title' => __( 'From Instagram', 'simple-instagram' ), 'count' => __( '12', 'simple-instagram' ), 'tag' => __( 'food', 'simple-instagram' ) );
        $instance = wp_parse_args( (array) $instance, $defaults ); 
        $style    = 'width:100%;'; ?>

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
            <label for="<?php echo esc_attr( $this->get_field_id( 'tag' ) ); ?>">
                <?php _e( 'Tag:', 'simple-instagram' ); ?>
            </label>
            <input 
                id="<?php echo esc_attr( $this->get_field_id( 'tag' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'tag' ) ); ?>" 
                value="<?php echo esc_attr( $instance['tag'] ); ?>" 
                style="<?php echo esc_attr( $style ); ?>" 
            />
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>">
                <?php _e( 'Number of Images (25 Maximum):', 'simple-instagram' ); ?>
            </label>
            <input 
                id="<?php echo esc_attr( $this->get_field_id( 'count' ) ); ?>" 
                name="<?php echo esc_attr( $this->get_field_name( 'count' ) ); ?>" 
                value="<?php echo esc_attr( $instance['count'] ); ?>" 
                style="<?php echo esc_attr( $style ); ?>" 
            />
        </p>

      <?php
    }

}