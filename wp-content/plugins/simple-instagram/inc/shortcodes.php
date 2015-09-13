<?php

/**
 * Simple Instagram Shortcodes Class
 *
 * Enqueues Shortcodes
 *
 * @package simple-instagram
 */

require_once( 'class-simple-instagram.php' );

class SI_Shortcodes {

    private static $instance;

    function __construct() {

        add_shortcode( 'si_feed', array( $this, 'si_feed' ) );
        add_shortcode( 'si_popular', array( $this, 'si_popular' ) );
        add_shortcode( 'si_profile', array( $this, 'si_profile' ) );

    }

    /**
     * SI Feed - Enables and returns formatted HTML for
     * the [si_feed] shortcode
     *
     * @param array $atts - The Array of provided attributes
     * @return str $return - The formatted HTML
     */
    public function si_feed( $atts ) {

        $defaults = shortcode_atts( array(
            'limit' => 10,
            'size' => 'medium',
            'wrapper' => 'div',
            'link' => 'true',
            'width' => 'auto',
            'tag' => '',
            'user' => 'self',
        ), $atts );

        foreach ( $defaults as $key => $value ) {
            ${$key} = $value;
        }

        $instagram = new Simple_Instagram();

        $feed = $tag == '' ? $instagram->get_user_media( $user, $limit ) : $instagram->get_tagged_media( $tag, $limit );

        if ( ! $feed || count( $feed ) < 0 ) {
            return '';
        }

        $feed = $this->check_count( $feed, $limit );

        $return = $this->get_image_markup( $feed, $width, $size, $wrapper, $link );

        return $return;
    }

    /**
     * SI Popular - Enables and returns formatted HTML for
     * the [si_popular] shortcode
     *
     * @param array $atts - The Array of provided attributes
     * @return str $return - The formatted HTML
     */
    public function si_popular( $atts ) {

        $defaults = shortcode_atts( array(
            'limit' => 16,
            'size' => 'medium',
            'wrapper' => 'div',
            'link' => 'true',
            'width' => 'auto',
        ), $atts );

        foreach ( $defaults as $key => $value ) {
            ${$key} = $value;
        }

        $instagram = new Simple_Instagram();
        $feed      = $instagram->get_popular_media( $limit );

        if ( ! $feed || 0 == count( $feed ) ) {
            return '';
        }

        $feed   = $this->check_count( $feed, $limit );
        $return = $this->get_image_markup( $feed, $width, $size, $wrapper, $link );

        return $return;
    }

    /**
     * SI Profile - Enables and returns formatted HTML for
     * the [si_profile] shortcode
     *
     * @param array $atts - The Array of provided attributes
     * @return str $return - The formatted HTML
     */
    function si_profile( $atts ) {

        $defaults = shortcode_atts( array(
            'username' => 'true',
            'profile_picture' => 'true',
            'bio' => 'true',
            'website' => 'true',
            'full_name' => 'true',
            'themed' => 'false',
        ), $atts );

        foreach ( $defaults as $key => $value ) {
            ${$key} = $value;
        }

        $instagram = new Simple_Instagram();
        $user      = $instagram->get_user();

        if ( ! $user ) {
            return '';
        }

        $class  = $themed == 'true' ? 'si_profile themed' : 'si_profile';
        $return = '<div class="' . $class . '">';

        if ( 'true' == $profile_picture && $user->profile_picture != '' ) {
            $return .= '<div class="si_profile_picture">';
            $return .= '<img src="' . $user->profile_picture . '">';
            $return .= '</div>';
        }

        if ( 'true' == $username && $user->username != '' ) {
            $return .= '<div class="si_username">' . $user->username . '</div>';
        }

        if ( 'true' == $full_name && $user->full_name != '' ) {
            $return .= '<div class="si_full_name">' . $user->full_name . '</div>';
        }

        if ( 'true' == $bio && $user->bio != '' ) {
            $return .= '<div class="si_bio">' . $user->bio . '</div>';
        }
        
        if ( 'true' == $website && $user->website != '' ) {
            $return .= '<div class="si_website"><a href="' . $user->website . '">View Website</a></div>';
        }

        $return .= '</div>';

        return $return;
    }

    /**
     * Get Image Markup - Take the provided attributes and return formatted
     * HTML markup for this shortcode
     *
     * @param array $feed - The array of media items
     * @param str $width - The requested media width
     * @param str $size - The requested media size
     * @param str $wrapper - The defined item wrapper type
     * @param bool $link - Whether to provide a link to the media
     * @return str $return - The formatted HTML markup
     */
    private function get_image_markup( $feed, $width, $size, $wrapper, $link ) {

        $return  = '<div class="si_feed">';
        $width   = str_replace( 'px', '', $width );
        $w_param = '';

        if ( 'auto' != $width ) {
            $width = $width > 612 ? 612 : $width;
            $w_param = 'width="' . $width . '" height="' . $width . '"';
        }

        if ( 'li' == $wrapper ) {
            $return .= '<ul class="si_feed_list">';
        }

        foreach ( $feed as $image ) {

            $url = $image->images->standard_resolution->url;

            if ( 'auto' == $width ) {

                switch ( $size ) {
                    case 'full':
                        $url = $image->images->standard_resolution->url;
                        break;

                    case 'medium':
                        $url = $image->images->low_resolution->url;
                        break;

                    case 'small':
                        $url = $image->images->thumbnail->url;
                        break;
                }
            }

            // Fix https
            $url = str_replace( 'http://', '//', $url );

            $return .= $wrapper == 'div' ? '<div class="si_item">' : '<li class="si_item">';
            
            $return .= $link == 'true' ? '<a href="' . $image->link . '" target="_blank">' : null;

            $image_caption = is_object( $image->caption ) ? $image->caption->text : '';

            $return .= '<img alt="' . $image_caption. '" src="' . $url . '" ' . $w_param . ' >';

            $return .= $link == 'true' ? '</a>' : null;

            $return .= $wrapper == 'div' ? '</div>' : '</li>';

        }

        $return .= $wrapper == 'div' ? '</div>' : '</ul>';

        return $return;

    }

    /**
     * Check Count - Trims any media feeds that are longer
     * than the provided limit
     *
     * @param array $feed - The media feed
     * @param int $limit - The provided Limit
     * @return obj $feed - The trimmed feed
     */
    private function check_count( $feed, $limit ) {

        if ( count( $feed ) > $limit ) {
            $feed = array_slice( $array, 0, $limit );
        }

        return $feed;
    }

    /**
     * Get Class Instance
     *
     * @return obj
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new SI_Shortcodes();
        }
        return self::$instance;

    }
}