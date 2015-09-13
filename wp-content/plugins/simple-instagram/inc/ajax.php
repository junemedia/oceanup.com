<?php

/**
 * Simple Instagram AJAX Class
 *
 * Enqueues AJAX actions
 *
 * @package simple-instagram
 */

require_once( SI_PLUGIN_DIR . '/inc/class-simple-instagram.php' );

class SI_Ajax {

    private static $instance;

    function __construct() {

        add_action( 'wp_ajax_search_users', array( $this, 'search_users' ) );

    }

    /**
     * Search Users - Handles AJAX call to search for a User
     * by their username
     *
     * @return str - The HTML list of users
     */
    public function search_users() {

        if ( ! isset( $_POST['user'] ) || $_POST['user'] == '' ) {
            echo '';
            exit;
        }

        $username  = sanitize_text_field( $_POST['user'] );
        $instagram = new Simple_Instagram();
        $users     = $instagram->get_user_id( $username );

        if ( ! $users || 0 == count( $users )  ) {
            echo '';
            exit;
        }

        foreach ( $users as $result ): ?>
            <div class="si_sr">
                <div class="si_sr_user_image">
                    <img src="<?php echo esc_attr( $result->profile_picture ); ?>">
                </div>
                <div class="si_sr_user_info">
                    <div class="si_sr_user_name">
                        <strong>username: </strong><?php echo $result->username; ?>
                    </div>
                    <div class="si_sr_user_id">
                        <strong>user ID: </strong><?php echo $result->id; ?>
                    </div>
                </div>
            </div>
        <?php endforeach;

        exit;
    }

    /**
     * Get Class Instance
     *
     * @return obj
     */
    public static function get_instance() {

        if ( self::$instance === null ) {
            self::$instance = new SI_Ajax();
        }
        return self::$instance;

    }
}