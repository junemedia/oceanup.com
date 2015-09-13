<?php

/**
 * Simple Instagram Admin Class
 *
 * Enqueues Admin actions
 *
 * @package simple-instagram
 */
class SI_Admin
{

    private static $instance;
    private $plugin_screen_hook_suffix;
    protected $plugin_slug = 'simple-instagram';

    function __construct() {

        $si_shortcodes   = SI_Shortcodes::get_instance();
        $si_widgets      = SI_Widgets::get_instance();
        $si_scripts      = SI_Scripts::get_instance();
        $si_ajax         = SI_Ajax::get_instance();
        $plugin_basename = plugin_basename( plugin_dir_path( __DIR__ ) . $this->plugin_slug . '.php' );

        add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
        add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );
        add_filter( 'plugin_action_links_' . $plugin_basename, array( $this, 'add_action_links' ) );
        add_action( 'admin_notices', array( $this, 'si_authorize_notice' ) );

    }

    /**
     * Add Plugin Admin Menu - Adds the Simple Instagram 
     * Settings menu item
     */
    public function add_plugin_admin_menu() {

        $this->plugin_screen_hook_suffix = add_options_page( __( 'Simple Instagram Settings', $this->plugin_slug ), __( 'Simple Instagram', $this->plugin_slug ), 'manage_options', $this->plugin_slug, array( $this, 'display_plugin_admin_page' ) );
    }

    /**
     * Display Plugin Admin Page - Loads the admin.php file
     * to display the Simple Instagram settings menu
     */
    public function display_plugin_admin_page() {
        include_once( SI_PLUGIN_DIR . '/admin/views/admin.php' );
    }

    /**
     * Add Action Links - Adds the Settings Link to the main
     * Settings admin menu
     */
    public function add_action_links( $links ) {

        return array_merge( array( 'settings' => '<a href="' . admin_url( 'options-general.php?page=' . $this->plugin_slug ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>' ), $links );
    }

    /**
     * Load Plugin Text Domain - Loads the text domain for
     * Simple Instagram
     */
    public function load_plugin_textdomain() {

        $domain = $this->plugin_slug;
        $locale = apply_filters( 'plugin_locale', get_locale(), $domain );

        load_textdomain( $domain, trailingslashit( WP_LANG_DIR ) . $domain . '/' . $domain . '-' . $locale . '.mo' );

    }  

    /**
     * SI Authorize Notice - Displays a notice on the dashboard
     * reminding users to authorize the Simple Instagram plugin
     */
    public function si_authorize_notice() {

        $screen = get_current_screen();
        if ( 'settings_page_simple-instagram' == $screen->id ) {
            return;
        }

        $si_access_token = get_option( 'si_access_token' );

        if ( $si_access_token ) {
            return;
        }?>

        <div class="error">
            <p><?php _e( sprintf( "<b>Simple Instagram:</b> You'll need to %s before your Instagram feeds will display.", '<a href="' . esc_url( admin_url( 'options-general.php?page=simple-instagram' ) ) . '">authorize your account</a>' ), 'simple-instagram' ); ?></p>
        </div>

        <?php

    }
    
    /**
     * Get Class Instance
     *
     * @return obj
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new SI_Admin();
        }
        return self::$instance;
    }
}