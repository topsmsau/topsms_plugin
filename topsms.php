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
 * Plugin Name:             TopSMS
 * Plugin URI:              https://topsms.com.au
 * Description:             Enhance your WooCommerce store with automated SMS notifications based on order status changes. Built exclusively for Australian businesses.
 * Version:                 2.0.20
 * Requires at least:       5.0
 * Requires PHP:            7.4
 * Tested up to:            6.8
 * WC requires at least:    7.0
 * WC tested up to:         8.8
 * Author:                  EUX
 * Author URI:              https://eux.com.au
 * License:                 GPL-2.0+
 * License URI:             http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:             topsms
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TOPSMS_VERSION', '2.0.20' );
define( 'TOPSMS_DB_VERSION', '2.0.3' );
define( 'TOPSMS_MANAGER_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
// Define path to the included plugin.
define( 'TOPSMS_ANALYTICS_PATH', plugin_dir_path( __FILE__ ) . 'topsms-analytics/' );

// Include the main file of the plugin you want to include.
if ( file_exists( TOPSMS_ANALYTICS_PATH . 'topsms-analytics.php' ) ) {
	require_once TOPSMS_ANALYTICS_PATH . 'topsms-analytics.php';
}

/**
 * Handle admin notices for WooCommerce dependency
 */
function topsms_admin_notices() {
	// Check for the transient that was set during activation.
	if ( get_transient( 'topsms_woocommerce_missing' ) ) {
		?>
		<div class="error">
			<p><?php esc_html_e( 'TopSMS requires WooCommerce to be installed and active. The plugin has been deactivated.', 'topsms' ); ?></p>
		</div>
		<?php
		// Delete the transient so the notice only appears once.
		delete_transient( 'topsms_woocommerce_missing' );
	}
}
add_action( 'admin_notices', 'topsms_admin_notices' );

/**
 * Check WooCommerce dependency on admin init
 */
function topsms_check_woocommerce_dependency() {
	// Only run this check if our plugin is active.
	if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {
		// If WooCommerce is not active.
		if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ), true ) ) {
			// Deactivate the plugin.
			deactivate_plugins( plugin_basename( __FILE__ ) );

			// Set a transient to show the notice.
			set_transient( 'topsms_woocommerce_missing', true, 5 );

			// If we're on the plugins page after activation, prevent the "activated" notice.
			if ( isset( $_GET['activate'] ) && isset( $_GET['_wpnonce'] ) ) {
				// Unslash and sanitize the nonce value before verification.
				$nonce = sanitize_key( wp_unslash( $_GET['_wpnonce'] ) );

				// The plugin file path should match exactly what WordPress uses.
				$plugin_file = 'woocommerce/woocommerce.php';

				if ( wp_verify_nonce( $nonce, 'activate-plugin_' . $plugin_file ) ) {
					unset( $_GET['activate'] );
				}
			}
		}
	}
}
add_action( 'admin_init', 'topsms_check_woocommerce_dependency' );

// Declare HPOS compatibility.
add_action(
	'before_woocommerce_init',
	function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-topsms-activator.php
 */
function topsms_activate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-topsms-activator.php';
	Topsms_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-topsms-deactivator.php
 */
function topsms_deactivate() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-topsms-deactivator.php';
	Topsms_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'topsms_activate' );
register_deactivation_hook( __FILE__, 'topsms_deactivate' );

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
function topsms_run() {

	$plugin = new Topsms();
	$plugin->run();
}
topsms_run();



/**
 * Check database version for plugin update.
 * If database version isn't updated, run activation function to create new tables.
 *
 * @since    2.0.3
 */
function topsms_check_plugin_update() {
	if ( get_option( 'topsms_db_version' ) !== TOPSMS_DB_VERSION ) {
		require_once plugin_dir_path( __FILE__ ) . 'includes/class-topsms-activator.php';
		Topsms_Activator::update();
	}
}
add_action( 'plugins_loaded', 'topsms_check_plugin_update' );