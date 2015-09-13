<?php

/**
 * Simple Instagram Widgets Class
 *
 * Enqueues Widgets
 *
 * @package simple-instagram
 */

require_once( SI_PLUGIN_DIR . '/widgets/si-feed-widget.php' );
require_once( SI_PLUGIN_DIR . '/widgets/si-popular-widget.php' );
require_once( SI_PLUGIN_DIR . '/widgets/si-profile-widget.php' );
require_once( SI_PLUGIN_DIR . '/widgets/si-tag-widget.php' );

class SI_Widgets {

    private static $instance;

    function __construct() {

        add_action( 'widgets_init', array( $this, 'si_register_widgets' ) );

    }

    /**
     * SI Register Widgets - Registers each of the Simple Instagram
     * widgets for use in Sidebars
     */
    function si_register_widgets() {
        register_widget( 'SI_Feed_Widget' );
        register_widget( 'SI_Tag_Widget' );
        register_widget( 'SI_Popular_Widget' );
        register_widget( 'SI_Profile_Widget' );
    }

    /**
     * Get Class Instance
     *
     * @return obj
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new SI_Widgets();
        }
        return self::$instance;

    }
}