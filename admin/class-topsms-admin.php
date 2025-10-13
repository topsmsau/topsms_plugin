<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Topsms
 * @subpackage Topsms/admin
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Topsms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Topsms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/topsms-admin.css', array(), time(), 'all' );
		wp_enqueue_style( 'topsms-admin-style', plugin_dir_url( __FILE__ ) . 'css/topsms-admin-app.css', array(), time(), 'all' );
		wp_enqueue_style( 'wp-components' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Topsms_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Topsms_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */
		// React-based script enqueue.
		wp_enqueue_script(
			'topsms-admin-app',
			plugin_dir_url( __FILE__ ) . 'js/topsms-admin-app.js',
			array(
				'wp-element',
				'wp-components',
				'wp-i18n',
				'wp-data',
				'wp-api-fetch',
				'wp-blocks',
				'wp-block-editor',
			),
			time(),
			true
		);
		wp_localize_script(
			'wp-api',
			'wpApiSettings',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
		wp_enqueue_script( 'wp-api' );

		// Other custom JS scripts.
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/topsms-admin.js', array( 'jquery' ), time(), false );
		wp_localize_script(
			$this->plugin_name,
			'topsmsAdmin',
			array(
				'nonce'    => wp_create_nonce( 'topsmsAdmin' ),
				'adminUrl' => admin_url( 'admin.php?page=topsms-contacts-list' ),
			)
		);
	}

	/**
	 * Load files all files and dependencies required.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-helper-admin.php';
		$this->helper = new Topsms_Helper_Admin( $this->plugin_name, $this->version );

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-rest-api-admin.php';
		$this->rest_api = new Topsms_Rest_Api_Admin( $this->plugin_name, $this->version, $this->helper );

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-contacts-list-admin.php';

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-campaigns-admin.php';
	}

	/**
	 * Register custom routes for the REST API.
	 *
	 * @since    1.0.0
	 */
	public function topsms_register_routes() {
		// Sending otp.
		register_rest_route(
			'topsms/v1',
			'/send-otp',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_send_otp' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Verifying otp.
		register_rest_route(
			'topsms/v1',
			'/verify-otp',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_verify_otp' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Fetching automations status settings.
		register_rest_route(
			'topsms/v1',
			'/automations/status/(?P<status_key>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_automations_status_settings' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Saving automations status enabled setting.
		register_rest_route(
			'topsms/v1',
			'/automations/status/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_save_automations_status_enabled' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Saving automations status template.
		register_rest_route(
			'topsms/v1',
			'/automations/status/save-template',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_save_automations_status_template' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Fetching settings (general).
		register_rest_route(
			'topsms/v1',
			'/settings/(?P<key>[a-zA-Z0-9_-]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_settings' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Saving settings (general).
		register_rest_route(
			'topsms/v1',
			'/settings/save',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_save_settings' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Saving input fields for general settings.
		register_rest_route(
			'topsms/v1',
			'/settings/save-surcharge',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_save_settings_' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Saving input fields for general settings.
		register_rest_route(
			'topsms/v1',
			'/settings/save-input',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_save_settings_' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Get user data.
		register_rest_route(
			'topsms/v1',
			'/user',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_user_data' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Register REST API route with parameters.
		register_rest_route(
			'topsms/v1',
			'/logs',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_analytics_logs' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
				'args'                => array(
					'after'        => array(
						'description'       => 'Limit results to those after the specified date (ISO 8601 format)',
						'type'              => 'string',
						'format'            => 'date-time',
						'validate_callback' => function ( $param ) {
							return empty( $param ) || rest_parse_date( $param );
						},
					),
					'before'       => array(
						'description'       => 'Limit results to those before the specified date (ISO 8601 format)',
						'type'              => 'string',
						'format'            => 'date-time',
						'validate_callback' => function ( $param ) {
							return empty( $param ) || rest_parse_date( $param );
						},
					),
					'page'         => array(
						'description'       => 'Current page of the collection',
						'type'              => 'integer',
						'default'           => 1,
						'minimum'           => 1,
						'sanitize_callback' => 'absint',
					),
					'per_page'     => array(
						'description'       => 'Maximum number of items to be returned in result set',
						'type'              => 'integer',
						'default'           => 10,
						'minimum'           => 1,
						'maximum'           => 100,
						'sanitize_callback' => 'absint',
					),
					'status'       => array(
						'description' => 'Filter by SMS status',
						'type'        => 'string',
						'enum'        => array( 'delivered', 'sent', 'pending', 'failed' ),
					),
					'order_status' => array(
						'description' => 'Filter by order status',
						'type'        => 'string',
					),
				),
			)
		);

		// Sending a single sms.
		register_rest_route(
			'topsms/v2',
			'/send-sms',
			array(
				'methods'             => 'POST',
				'callback'            => array( $this->rest_api, 'topsms_send_test_sms' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Fetching available segments/saved filters from the contacts list.
		register_rest_route(
			'topsms/v2',
			'/bulksms/lists',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_saved_filters' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Fetching contacts based on selected list/segment.
		register_rest_route(
			'topsms/v2',
			'/bulksms/lists/(?P<filter_id>[a-zA-Z0-9_]+)',
			array(
				'methods'             => 'GET',
				'callback'            => array( $this->rest_api, 'topsms_get_list' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Clearing transient based on the transient id.
		register_rest_route(
			'topsms/v2',
			'/bulksms/clear-transient',
			array(
				'methods'             => array( 'POST', 'DELETE' ),
				'callback'            => array( $this->rest_api, 'topsms_clear_list_transient' ),
				'permission_callback' => function () {
					return '__return_true'; // sendBeacon is used on the frontend, so allow public access.
				},
			)
		);

		// Schedule campaign / send campaign instantly.
		register_rest_route(
			'topsms/v2',
			'/bulksms/schedule-campaign',
			array(
				'methods'             => array( 'POST' ),
				'callback'            => array( $this->rest_api, 'topsms_schedule_campaign' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Save campaign as draft.
		register_rest_route(
			'topsms/v2',
			'/bulksms/save-campaign',
			array(
				'methods'             => array( 'POST' ),
				'callback'            => array( $this->rest_api, 'topsms_save_campaign_as_draft' ),
				'permission_callback' => function () {
					return current_user_can( 'manage_options' );
				},
			)
		);

		// Webhook endpoint to get campaign status from Supabase.
		register_rest_route(
			'topsms/v2',
			'/bulksms/campaign-status',
			array(
				'methods'             => array( 'POST' ),
				'callback'            => array( $this->rest_api, 'topsms_scheduled_campaign_status' ),
				'permission_callback' => '__return_true',
			)
		);
	}

	/**
	 * Add an admin menu for Topsms.
	 *
	 * @since 1.0.0
	 */
	public function topsms_add_admin_menu() {
		$is_connected = $this->topsms_check_connection();

		$icon = file_get_contents( plugin_dir_path( __FILE__ ) . 'assets/topsms-icon.svg' );

		if ( $icon ) {
			$icon_data = 'data:image/svg+xml;base64,' . base64_encode( $icon );
		} else {
			$icon_data = 'dashicons-smartphone';
		}

		add_menu_page(
			__( 'TopSMS', 'topsms' ),
			__( 'TopSMS', 'topsms' ),
			'manage_options',
			$is_connected ? 'topsms' : 'topsms-setup',
			$is_connected ? array( $this, 'topsms_display_automations_page' ) : array( $this, 'topsms_display_setup_page' ),
			$icon_data,
			55
		);

		add_submenu_page(
			'topsms',
			__( 'Automation', 'topsms' ),
			__( 'Automation', 'topsms' ),
			'manage_options',
			'topsms-automations',
			array( $this, 'topsms_display_automations_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Settings', 'topsms' ),
			__( 'Settings', 'topsms' ),
			'manage_options',
			'topsms-settings',
			array( $this, 'topsms_display_settings_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Bulk SMS', 'topsms' ),
			__( 'Bulk SMS', 'topsms' ),
			'manage_options',
			'topsms-bulksms',
			array( $this, 'topsms_display_bulk_sms_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Contacts', 'topsms' ),
			__( 'Contacts', 'topsms' ),
			'manage_options',
			'topsms-contacts-list',
			array( $this, 'topsms_display_contacts_list_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Campaigns', 'topsms' ),
			__( 'Campaigns', 'topsms' ),
			'manage_options',
			'topsms-campaigns',
			array( $this, 'topsms_display_campaigns_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Analytics', 'topsms' ),
			__( 'Analytics', 'topsms' ),
			'manage_options',
			'topsms-analytics',
			array( $this, 'topsms_display_analytics_page' )
		);

		// Remove the duplicated submenu.
		remove_submenu_page( 'topsms', 'topsms' );
	}

	/**
	 * Render the setup page.
	 *
	 * @since    1.0.0
	 */
	public function topsms_display_setup_page() {
		$is_connected = $this->topsms_check_connection();

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsNonce',
			array(
				'restUrl'     => esc_url_raw( rest_url() ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'isConnected' => $is_connected,
			)
		);

		// Container for React app.
		printf(
			'<div class="wrap">
                <div id="topsms-admin-setup" class="topsms-app"></div>
            </div>'
		);
	}


	/**
	 * Render the analytics page.
	 *
	 * @since    1.0.0
	 */
	public function topsms_display_analytics_page() {

		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		wp_safe_redirect( admin_url( 'admin.php?page=wc-admin&path=%2Fanalytics%2Ftopsms-analytics' ) );
		exit;
	}

	/**
	 * Render the automations page.
	 *
	 * @since    1.0.0
	 */
	public function topsms_display_automations_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsNonce',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL,
			)
		);

		// Container for React app.
		printf(
			'<div class="wrap">
                <div id="topsms-admin-automations" class="topsms-app"></div>
            </div>'
		);
	}

	/**
	 * Render the general settings page.
	 *
	 * @since    1.0.0
	 */
	public function topsms_display_settings_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsNonce',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL,
			)
		);

		// Container for React app.
		printf(
			'<div class="wrap">
                <div id="topsms-admin-settings" class="topsms-app"></div>
            </div>'
		);
	}

	/**
	 * Checks if access token and refresh token exist to determine
	 * if the plugin is connected to the TopSMS API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   boolean    True if connected, false otherwise.
	 */
	private function topsms_check_connection() {
		$access_token  = get_option( 'topsms_access_token' );
		$refresh_token = get_option( 'topsms_refresh_token' );

		// Check if the token exists.
		if ( ! empty( $access_token ) && ! empty( $refresh_token ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Redirect to setup page after plugin activation.
	 *
	 * @since    1.0.0
	 */
	public function topsms_activation_redirect() {
		// Check if our transient is set and we're on the plugins page.
		if ( get_transient( 'topsms_activation_redirect' ) && is_admin() ) {
			// Delete the transient.
			delete_transient( 'topsms_activation_redirect' );

			// Check if is connected.
			$is_connected = $this->topsms_check_connection();

			// Only redirect if woocommerce is activated and both tokens exist and user has admin permissions and the plugin is connected.
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) && ! $is_connected ) {
				// Important: exit after redirect to stop further execution.
				wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
				exit;
			}
		}
	}

	/**
	 * Register custom cron interval for token refresh.
	 *
	 * Adds a new cron schedule interval of 55 minutes for token refresh operations.
	 *
	 * @since    1.0.0
	 * @param    array $schedules    List of existing cron schedules.
	 * @return   array    Modified list of cron schedules.
	 */
	public function topsms_add_cron_interval( $schedules ) {
		$schedules['every_55_minutes'] = array(
			'interval' => 55 * 60,
			'display'  => 'Every 55 Minutes',
		);
		return $schedules;
	}

	/**
	 * Refresh TopSMS API access tokens.
	 *
	 * @since    1.0.0
	 * @return   boolean    True on successful refresh, false on failure.
	 */
	public function topsms_refresh_tokens() {
		// Get the current refresh token.
		$refresh_token = get_option( 'topsms_refresh_token', true );

		if ( ! $refresh_token ) {
			return false;
		}

		// Prepare the request.
		$response = wp_remote_post(
			'https://api.topsms.com.au/functions/v1/refresh',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'refresh_token' => $refresh_token,
					)
				),
				'timeout' => 50,
			)
		);

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// Get the response body and decode it.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check if we received valid data.
		if ( ! isset( $data['session']['access_token'] ) || ! isset( $data['session']['refresh_token'] ) ) {
			return false;
		}

		// Save the new tokens.
		update_option( 'topsms_access_token', $data['session']['access_token'] );
		update_option( 'topsms_refresh_token', $data['session']['refresh_token'] );

		return true;
	}

	/**
	 * Schedule token refresh cron job for every 55 minutes.
	 *
	 * Sets up a recurring cron job to refresh the TopSMS API tokens
	 * if the job is not already scheduled.
	 *
	 * @since    1.0.0
	 */
	public function topsms_schedule_token_refresh() {
		if ( ! wp_next_scheduled( 'topsms_refresh_tokens_hook' ) ) {
			wp_schedule_event( time(), 'every_55_minutes', 'topsms_refresh_tokens_hook' );
		}
	}

	/**
	 * Sends SMS notifications to customers when order status changes.
	 *
	 * @since    1.0.0
	 * @param    int      $order_id      The order ID.
	 * @param    string   $status_from   The previous order status.
	 * @param    string   $status_to     The new order status.
	 * @param    WC_Order $order         The order object.
	 */
	public function topsms_order_status_changed( $order_id, $status_from, $status_to, $order ) {
		// Check if the customer consent is enabled.
		$consent_enabled = $order->get_meta( 'topsms_customer_consent' );

		// Only show the checkbox if this setting is enabled; Return if disabled.
		if ( ! $consent_enabled || 'no' === $consent_enabled ) {
			return;
		}

		global $wpdb;

		// Remove 'wc-' prefix if present in status.
		$status_to = str_replace( 'wc-', '', $status_to );

		// Get access token from options.
		$access_token = get_option( 'topsms_access_token' );
		if ( ! $access_token ) {
			return;  // Access token found.
		}

		$sender           = $this->helper->topsms_fetch_sender_name();
		$is_enabled       = get_option( 'topsms_order_' . $status_to . '_enabled' );
		$message_template = get_option( 'topsms_order_' . $status_to . '_message' );

		// Check if SMS is enabled for this status.
		if ( ! $is_enabled || 'yes' !== $is_enabled ) {
			return;
		}

		// Get customer phone number.
		$phone = $order->get_billing_phone();
		if ( empty( $phone ) ) {
			return; // No phone number available.
		}

		// Check if message template exists.
		if ( empty( $message_template ) ) {
			return; // No message template configured.
		}

		// Check if user has enough sms balance.
		if ( ! $this->helper->check_user_balance() ) {
			return;
		}

		// Replace placeholders.
		$replacements = array(
			'[order_id]'   => $order->get_order_number(),
			'[first_name]' => $order->get_billing_first_name(),
			'[last_name]'  => $order->get_billing_last_name(),
		);
		$message      = str_replace( array_keys( $replacements ), array_values( $replacements ), $message_template );

		// Send SMS.
		$url  = 'https://api.topsms.com.au/functions/v1/sms';
		$body = array(
			'phone_number' => $phone,
			'from'         => $sender,
			'message'      => $message,
			'link'         => '',
		);

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 50,
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Determine API status.
		if ( is_wp_error( $response ) ) {
			$api_status = 'Failed';
		} elseif ( isset( $data['messageStatuses'][0]['statusText'] ) ) {
			$api_status = $data['messageStatuses'][0]['statusText'];
		} else {
			$api_status = 'Pending';
		}

		// Log to topsms_logs table.
		$table_name = $wpdb->prefix . 'topsms_logs';
		$wpdb->insert(
			$table_name,
			array(
				'order_id'      => $order_id,
				'order_status'  => $status_to,
				'phone'         => $phone,
				'creation_date' => current_time( 'mysql' ),
				'status'        => $api_status,
			),
			array(
				'%d',
				'%s',
				'%s',
				'%s',
				'%s',
			)
		);

		// Check balance after sending the sms.
		$balance = isset( $data['remainingBalance'] ) ? $data['remainingBalance'] : '';
		if ( $balance ) {
			$this->helper->topsms_low_balance_alert( (int) $balance );
		}
	}

	/**
	 * Render the bulk page.
	 *
	 * @since    2.0.0
	 */
	public function topsms_display_bulk_sms_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Get campaign ID from url params if exists.
		$campaign_id = isset( $_GET['campaign_id'] ) ? intval( $_GET['campaign_id'] ) : 0;

		// Get campaign data if id is provided, and the status is draft.
		$campaign_data = null;
		if ( $campaign_id > 0 ) {
			global $wpdb;
			$table_name = $wpdb->prefix . 'topsms_campaigns';
			$cache_key  = 'topsms_campaign_' . $campaign_id;

			// Get cache data if exists.
			$campaign = wp_cache_get( $cache_key, 'topsms_campaigns' );

			// Do an sql query if not cached.
			if ( false === $campaign ) {
				global $wpdb;
				$table_name = $wpdb->prefix . 'topsms_campaigns';
				$campaign   = $wpdb->get_row(
					$wpdb->prepare(
						"SELECT * FROM {$table_name} WHERE id = %d AND status = %s",
						$campaign_id,
						'draft'
					)
				);

				// Cache for 1 hr.
				wp_cache_set( $cache_key, $campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
			}

			$data          = json_decode( $campaign->data, true );
			$campaign_data = array(
				'id'                => $campaign->id,
				'campaign_name'     => $campaign->job_name,
				'action'            => $campaign->action,
				'campaign_datetime' => $campaign->campaign_datetime,
				'status'            => $campaign->status,
				'list'              => isset( $data['list'] ) ? $data['list'] : '',
				'sender'            => isset( $data['sender'] ) ? $data['sender'] : '',
				'message'           => isset( $data['message'] ) ? $data['message'] : '',
				'url'               => isset( $data['url'] ) ? $data['url'] : '',
			);
		}

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsNonce',
			array(
				'restUrl'      => esc_url_raw( rest_url() ),
				'nonce'        => wp_create_nonce( 'wp_rest' ),
				'pluginUrl'    => TOPSMS_MANAGER_PLUGIN_URL,
				'campaignData' => $campaign_data, // Pass the campaign data if exist.
			)
		);

		// Container for React app.
		printf(
			'<div class="wrap">
                <div id="topsms-admin-bulksms" class="topsms-app"></div>
            </div>'
		);
	}

	/**
	 * Render the contacts list page.
	 *
	 * @since    2.0.0
	 */
	public function topsms_display_contacts_list_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Handle filter deletion.
		if ( isset( $_GET['action'] ) && 'delete_filter' === $_GET['action'] && isset( $_GET['filter_id'] ) ) {
			$filter_id = sanitize_text_field( wp_unslash( $_GET['filter_id'] ) );

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ), 'delete_filter_' . $filter_id ) ) ) {
				$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );
				unset( $saved_filters[ $filter_id ] );
				update_option( 'topsms_contacts_list_saved_filters', $saved_filters );

				// Clear cache when filters change.
				wp_cache_delete( 'topsms_contacts_list_cities' );
				wp_cache_delete( 'topsms_contacts_list_states' );

				// Remove the previous filter args  and add message for displaying notice.
				$redirect_url = remove_query_arg( array( 'action', 'filter_id', '_wpnonce' ) );
				if ( $result ) {
					$redirect_url = add_query_arg( 'message', 'filter_deleted', $redirect_url );
				} else {
					$redirect_url = add_query_arg( 'message', 'delete_filter_failed', $redirect_url );
				}

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		// Initialise the contacts list table.
		$table = new Topsms_Contacts_List_Admin( $this->helper );
		$table->prepare_items();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Contacts List</h1>
			<hr class="wp-header-end">

			<?php
			// Display success/error messages from the action.
			if ( isset( $_GET['message'] ) ) {
				if ( 'filter_deleted' === $_GET['message'] ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Filter deleted successfully.', 'topsms' ) . '</p></div>';
				} elseif ( 'delete_filter_failed' === $_GET['message'] ) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to delete filter. Please try again later.', 'topsms' ) . '</p></div>';
				}
			}
			?>
			
			<form method="get" id="topsms-contacts-filter">
				<input type="hidden" name="page" value="topsms-contacts-list">
				<?php
				$table->views();
				$table->search_box( 'Search Contacts', 'contact' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * AJAX handler to save contacts list filter.
	 *
	 * @since    2.0.0
	 */
	public function topsms_save_contacts_list_filter() {
		// Check nonce.
		check_ajax_referer( 'topsmsAdmin', 'nonce' );

		// Only allow users with admin cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		// Get all filters value.
		$filter_name             = isset( $_POST['filter_name'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_name'] ) ) : '';
		$filter_state            = isset( $_POST['filter_state'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_state'] ) ) : '';
		$filter_city             = isset( $_POST['filter_city'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_city'] ) ) : '';
		$filter_postcode         = isset( $_POST['filter_postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_postcode'] ) ) : '';
		$filter_search           = isset( $_POST['filter_search'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_search'] ) ) : '';
		$filter_orders_condition = isset( $_POST['filter_orders_condition'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_orders_condition'] ) ) : '';
		$filter_orders_value     = isset( $_POST['filter_orders_value'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_orders_value'] ) ) : '';
		$filter_orders_value2    = isset( $_POST['filter_orders_value2'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_orders_value2'] ) ) : '';
		$filter_spent_condition  = isset( $_POST['filter_spent_condition'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_spent_condition'] ) ) : '';
		$filter_spent_value      = isset( $_POST['filter_spent_value'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_spent_value'] ) ) : '';
		$filter_spent_value2     = isset( $_POST['filter_spent_value2'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_spent_value2'] ) ) : '';
		$filter_status           = isset( $_POST['filter_status'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_status'] ) ) : '';

		// Save the filter to options.
		$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );

		// Check for duplicate filter name.
		foreach ( $saved_filters as $filter ) {
			if ( strcasecmp( $filter['name'], $filter_name ) === 0 ) {
				wp_send_json_error( 'A filter with this name already exists. Please choose a different name.' );
				return;
			}
		}

		$filter_id = 'filter_' . time();

		$saved_filters[ $filter_id ] = array(
			'name'             => $filter_name,
			'state'            => $filter_state,
			'city'             => $filter_city,
			'postcode'         => $filter_postcode,
			'search'           => $filter_search,
			'orders_condition' => $filter_orders_condition,
			'orders_value'     => $filter_orders_value,
			'orders_value2'    => $filter_orders_value2,
			'spent_condition'  => $filter_spent_condition,
			'spent_value'      => $filter_spent_value,
			'spent_value2'     => $filter_spent_value2,
			'status'           => $filter_status,
		);

		update_option( 'topsms_contacts_list_saved_filters', $saved_filters );
		wp_send_json_success();
	}

	public function topsms_delete_contacts_list_filter() {
		// Check nonce.
		check_ajax_referer( 'topsmsAdmin', 'nonce' );

		// Only allow users with admin cap.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Permission denied' );
		}

		// Get the filter id.
		$filter_id = isset( $_POST['filter_id'] ) ? sanitize_text_field( wp_unslash( $_POST['filter_id'] ) ) : '';
		if ( empty( $filter_id ) ) {
			wp_send_json_error( 'Filter ID is missing' );
			return;
		}

		// Check if filter exists. If exists, delete the filter.
		$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );
		if ( ! isset( $saved_filters[ $filter_id ] ) ) {
			wp_send_json_error( 'Filter not found' );
			return;
		}
		unset( $saved_filters[ $filter_id ] );

		update_option( 'topsms_contacts_list_saved_filters', $saved_filters );
		wp_send_json_success();
	}

	/**
	 * Render the campaigns page.
	 *
	 * @since    2.0.0
	 */
	public function topsms_display_campaigns_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->topsms_check_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Handle cancel campaign.
		if ( isset( $_GET['action'] ) && 'cancel_campaign' === $_GET['action'] && isset( $_GET['campaign_id'] ) ) {
			$campaign_id = intval( $_GET['campaign_id'] );

			if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'cancel_campaign_' . $campaign_id ) ) {

				// Cancel campaign by calling the API.
				$result = $this->topsms_cancel_campaign( $campaign_id );

				// Remove the previous filter args  and add message for displaying notice.
				$redirect_url = remove_query_arg( array( 'action', 'campaign_id', '_wpnonce' ) );
				if ( $result ) {
					$redirect_url = add_query_arg( 'message', 'campaign_cancelled', $redirect_url );
				} else {
					$redirect_url = add_query_arg( 'message', 'cancel_campaign_failed', $redirect_url );
				}

				wp_safe_redirect( $redirect_url );
				exit;
			}
		}

		// Initialise the campaigns table.
		$table = new Topsms_Campaigns_Admin();
		$table->prepare_items();

		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Campaigns</h1>
			<hr class="wp-header-end">

			<?php
			// Display success/error messages from the action.
			if ( isset( $_GET['message'] ) ) {
				if ( 'campaign_cancelled' === $_GET['message'] ) {
					echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Campaign cancelled successfully.', 'topsms' ) . '</p></div>';
				} elseif ( 'cancel_campaign_failed' === $_GET['message'] ) {
					echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Failed to cancel campaign. Please try again later.', 'topsms' ) . '</p></div>';
				}
			}
			?>
			
			<form method="get" id="topsms-campaigns-filter">
				<input type="hidden" name="page" value="topsms-campaigns">
				<?php
				$table->views();
				$table->search_box( 'Search Campaigns', 'campaign' );
				$table->display();
				?>
			</form>
		</div>
		<?php
	}

	public function topsms_handle_unsubscribe() {
		// Check if phone  exists in url params.
		if ( ! isset( $_GET['phone'] ) || empty( $_GET['phone'] ) ) {
			return;
		}
		// Get phone from url params.
		$phone   = sanitize_text_field( wp_unslash( $_GET['phone'] ) );
		$user_id = null;

		// Check if user is logged in.
		if ( is_user_logged_in() ) {
			$user_id = get_current_user_id();

			// Verify phone matches user's billing phone.
			$billing_phone      = get_user_meta( $user_id, 'billing_phone', true );
			$normalised_billing = preg_replace( '/[^0-9]/', '', $billing_phone );
			$normalised_input   = preg_replace( '/[^0-9]/', '', $phone );
			if ( $normalised_billing !== $normalised_input ) {
				return;
			}
		} else {
			// User not logged in, find user by phone.
			global $wpdb;
			$normalised_phone = preg_replace( '/[^0-9]/', '', $phone );

			$user_id = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id FROM {$wpdb->usermeta} 
                WHERE meta_key = 'billing_phone' 
                AND REPLACE(REPLACE(REPLACE(meta_value, ' ', ''), '-', ''), '+', '') LIKE %s 
                LIMIT 1",
					'%' . $wpdb->esc_like( $normalised_phone )
				)
			);

			if ( ! $user_id ) {
				return; // No user found.
			}

			$user_id = intval( $user_id );
		}

		// Update user meta to unsubscribe.
		update_user_meta( $user_id, 'topsms_customer_consent', 'no' );

		// Get and update the meta in the last order.
		if ( function_exists( 'wc_get_orders' ) ) {
			$orders = wc_get_orders(
				array(
					'customer_id' => $user_id,
					'limit'       => 1,
					'orderby'     => 'date',
					'order'       => 'DESC',
					'return'      => 'objects',
				)
			);
			if ( ! empty( $orders ) ) {
				$order = $orders[0];
				$order->update_meta_data( 'topsms_customer_consent', 'no' );
				$order->save();
			}
		}

		wc_add_notice( 'You have been successfully unsubscribed from SMS notifications.', 'success' );
	}

	/**
	 * Cancel a scheduled campaign via TopSms API.
	 *
	 * @param int $campaign_id The campaign ID.
	 * @return bool True on success, false on failure.
	 */
	private function topsms_cancel_campaign( $campaign_id ) {
		if ( ! $campaign_id ) {
			return false;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

        // Get cache data if exists.
        $cache_key = 'topsms_campaign_' . $campaign_id;
        $campaign  = wp_cache_get( $cache_key, 'topsms_campaigns' );

        // Do an sql query if not cached.
		if ( false === $campaign ) {
            $campaign = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$table_name} WHERE id = %d",
                    $campaign_id
                )
            );

            // Cache for 1 hr.
            if ( $campaign ) {
                wp_cache_set( $cache_key, $campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
            }
        }
		if ( ! $campaign ) {
			return false;
		}

		// Only allow for scheduled campaigns (status is scheduled).
		if ( 'scheduled' !== $campaign->status ) {
			return false;
		}

		// Check the current time is within 30 minutes of scheduled time.
		if ( ! empty( $campaign->campaign_datetime ) ) {
			// Check the time difference.
			$scheduled_time = strtotime( $campaign->campaign_datetime );
			$current_time   = current_time( 'timestamp' );
			$time_diff      = $scheduled_time - $current_time;

			// Don't allow if within 30 minutes/negative time difference.
			if ( $time_diff <= 0 ) {
				return false;
			}
			if ( $time_diff <= 1800 ) {
				return false;
			}
		}

		// Get access token for API request.
		$access_token = get_option( 'topsms_access_token' );
		if ( ! $access_token ) {
			return false;
		}

		// Current datetime in UTC.
		$scheduled_datetime_utc = gmdate( 'Y-m-d\TH:i:s\Z' );

		// Webhook url for campaign status.
		$website_url = get_home_url();
		$webhook_url = $website_url . '/wp-json/topsms/v2/bulksms/campaign-status';

		// Cancel campaign.
		$url  = 'https://api.topsms.com.au/functions/v1/schedule';
		$body = array(
			'action'            => 'cancel',
			'scheduledDateTime' => $scheduled_datetime_utc,
			'jobName'           => $campaign->job_name,
			'token'             => $access_token,
			'smsPayload'        => array(
				'cost' => $campaign->cost,
			),
			'webhook_url'       => $webhook_url,
			'webhook_token'     => $campaign->webhook_token,
		);

		$response = wp_remote_post(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
				'timeout' => 50,
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Determine response.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( isset( $data['success'] ) && $data['success'] ) {
			// Update campaign to table.
			$result = $wpdb->update(
				$table_name,
				array(
					'status'            => 'cancelled',
					'campaign_datetime' => $scheduled_datetime_utc,
				),
				array( 'id' => $campaign_id ),
				array( '%s', '%s' ),
				array( '%d' )
			);

			// Clear cache for table status counts.
			wp_cache_delete( 'topsms_campaigns_status_counts' );

            // Clear cache for the campaign.
            wp_cache_delete( $cache_key, 'topsms_campaigns' );

			return false !== $result;
		} else {
			$error_message = '';
			if ( isset( $data['error'] ) ) {
				$error_message = $data['error'];
			} elseif ( isset( $data['message'] ) ) {
				$error_message = $data['message'];
			}

			// Update details with error to table.
			$wpdb->update(
				$table_name,
				array( 'details' => $error_message ),
				array( 'id' => $campaign_id ),
				array( '%s' ),
				array( '%d' )
			);

			return false;
		}
	}
}
