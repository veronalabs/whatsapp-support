<?php
/**
 * Plugin Name:       WP WhatsApp Support
 * Plugin URI:        https://wordpress.org/plugins/whatsapp-support/
 * Description:       Add support to your clients directly with WhatsApp.
 * Version:           1.1
 * Author:            VeronaLabs
 * Author URI:        https://veronalabs.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       whatsapp-support
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Plugin Defines.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'WHATSAPPSUPPORT_VERSION', '1.1' );
define( 'WHATSAPPSUPPORT_DIR', plugin_dir_path( __FILE__ ) );
define( 'WHATSAPPSUPPORT_URL', plugin_dir_url( __FILE__ ) );


/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-whatsappsupport.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_whatsappsupport() {

	$plugin = new WhatsAppSupport();
	$plugin->run();

}

run_whatsappsupport();
