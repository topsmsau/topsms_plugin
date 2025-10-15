<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Topsms
 * @subpackage Topsms/includes
 * @author     EUX <samee@eux.com.au>
 */
class Topsms {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Topsms_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TOPSMS_VERSION' ) ) {
			$this->version = TOPSMS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'topsms';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Topsms_Loader. Orchestrates the hooks of the plugin.
	 * - Topsms_Admin. Defines all hooks for the admin area.
	 * - Topsms_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-topsms-loader.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'public/class-topsms-public.php';

		$this->loader = new Topsms_Loader();
	}


	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Topsms_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Add admin menu.
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'topsms_add_admin_menu' );

		// Actions for rest api routes.
		$this->loader->add_action( 'rest_api_init', $plugin_admin, 'topsms_register_routes' );

		// Redirect to registration on activation.
		$this->loader->add_action( 'admin_init', $plugin_admin, 'topsms_activation_redirect' );

		// Cron jobs for topsms api access token refresh.
		$this->loader->add_filter( 'cron_schedules', $plugin_admin, 'topsms_add_cron_interval' );
		$this->loader->add_action( 'topsms_refresh_tokens_hook', $plugin_admin, 'topsms_refresh_tokens' );
		$this->loader->add_action( 'wp_loaded', $plugin_admin, 'topsms_schedule_token_refresh' );

		// Send notifications on order status changed.
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'topsms_order_status_changed', 10, 4 );

		// Ajax handler for contacts list.
		$this->loader->add_action( 'wp_ajax_topsms_save_contacts_list_filter', $plugin_admin, 'topsms_save_contacts_list_filter' );
		$this->loader->add_action( 'wp_ajax_topsms_delete_contacts_list_filter', $plugin_admin, 'topsms_delete_contacts_list_filter' );

		// Handle sms unsubscribe.
		$this->loader->add_action( 'init', $plugin_admin, 'topsms_handle_unsubscribe' );

		// To hide admin notices from other plugins.
		$this->loader->add_action( 'admin_head', $plugin_admin, 'topsms_hide_other_notices', 1 );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Topsms_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Customer consent checkbox on the checkout page.
		$this->loader->add_action( 'woocommerce_review_order_before_submit', $plugin_public, 'topsms_add_customer_consent_checkout_checkbox', 20 );
		$this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'topsms_save_customer_consent_checkout_checkbox' );

		// Add topsms surcharge to cart.
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'topsms_add_surcharge_to_cart' );

		// AJAX handler to update the customer consent in session.
		$this->loader->add_action( 'wp_ajax_topsms_update_consent', $plugin_public, 'topsms_update_customer_consent' );
		$this->loader->add_action( 'wp_ajax_nopriv_topsms_update_consent', $plugin_public, 'topsms_update_customer_consent' );

		// Add sms notifications menu item to my account.
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'topsms_add_sms_notifications_tab' );
		$this->loader->add_action( 'init', $plugin_public, 'topsms_sms_notifications_endpoint' );
		$this->loader->add_action( 'woocommerce_account_sms-notifications_endpoint', $plugin_public, 'topsms_sms_notifications_content' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Topsms_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
