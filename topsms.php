<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://eux.com.au
 * @since             1.0.0
 * @package           Topsms
 *
 * @wordpress-plugin
 * Plugin Name:       TopSMS
 * Plugin URI:        https://eux.com.au
 * Description:       An WooCommerce Add-On for SMS notifications
 * Version:           1.0.0
 * Author:            EUX
 * Author URI:        https://eux.com.au
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       topsms
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TOPSMS_VERSION', '1.0.0' );
define('TOPSMS_MANAGER_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-topsms-activator.php
 */
function activate_topsms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-topsms-activator.php';
	Topsms_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-topsms-deactivator.php
 */
function deactivate_topsms() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-topsms-deactivator.php';
	Topsms_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_topsms' );
register_deactivation_hook( __FILE__, 'deactivate_topsms' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-topsms.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_topsms() {

	$plugin = new Topsms();
	$plugin->run();

}
run_topsms();
