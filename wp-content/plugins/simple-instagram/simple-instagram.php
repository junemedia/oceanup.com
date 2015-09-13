<?php
/**
 * simple-instagram
 *
 * A plugin to allow users to include Instagram feeds, media, and information.
 *
 * @package   Simple Instagram
 * @author    Aaron Speer <adspeer@gmail.com>
 * @license   GPL-2.0+
 * @link      http://example.com
 * @copyright 2013 Aaron Speer
 *
 * @wordpress-plugin
 * Plugin Name:       Simple Instagram
 * Description:       A plugin to allow users to include InstaGram feeds, media, and information.
 * Version:           2.0.2
 * Author:            Aaron Speer
 * Author URI:        aaronspeer.com
 * Text Domain:       simple-instagram
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Domain Path:       /languages
 * GitHub Plugin URI: https://github.com/<owner>/<repo>
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'SI_PLUGIN_DIR', dirname( __FILE__ ) );
define( 'SI_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

include_once SI_PLUGIN_DIR . '/inc/admin.php';
include_once SI_PLUGIN_DIR . '/inc/scripts.php';
include_once SI_PLUGIN_DIR . '/inc/widgets.php';
include_once SI_PLUGIN_DIR . '/inc/shortcodes.php';
include_once SI_PLUGIN_DIR . '/inc/ajax.php';

add_action( 'plugins_loaded', 'si_init' );

/**
 * SI Init - Initialize the main Admin Class
 */
function si_init() {

    $si_admin = SI_Admin::get_instance();

}