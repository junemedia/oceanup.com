<?php

/**
 * Simple Instagram Scripts Class
 *
 * Enqueues Scripts actions
 *
 * @package simple-instagram
 */
class SI_Scripts {

    private static $instance;
    protected $plugin_slug = 'simple-instagram';

    function __construct() {

        // Load admin style sheet and JavaScript.
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

        // Load public style sheet
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_styles' ) );
    }

    /**
     * Enqueue Admin Styles - Add the proper Admin Styles
     */
    public function enqueue_admin_styles() {

        $screen = get_current_screen();
        if ( 'settings_page_simple-instagram' == $screen->id ) {
            wp_enqueue_style( $this->plugin_slug . '-admin-styles', SI_PLUGIN_URL . 'admin/assets/css/admin.css' );
            wp_enqueue_style( 'font-awesome', '//netdna.bootstrapcdn.com/font-awesome/4.0.3/css/font-awesome.css' );
        }
    }

    /**
     * Enqueue Admin Scripts - Add the proper Admin Scripts
     */
    public function enqueue_admin_scripts() {

        $screen = get_current_screen();

        if ( 'settings_page_simple-instagram' == $screen->id ) {
            wp_enqueue_script( $this->plugin_slug . '-admin-script', SI_PLUGIN_URL . 'admin/assets/js/admin.js' );
        }
    }

    /**
     * Enqueue Public Styles - Add the proper Public Styles
     */
    public function enqueue_public_styles() {
        wp_enqueue_style( $this->plugin_slug . '-public-styles', SI_PLUGIN_URL . 'public/assets/css/public.css' );
    }

    /**
     * Get Class Instance
     *
     * @return obj
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new SI_Scripts();
        }
        return self::$instance;
    }
}