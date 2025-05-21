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
	const LOW_BALANCE_THRESHOLD = 50;

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

		// Hide the wp admin header and sidebar menu if on setup page.
		add_action(
			'admin_init',
			function () {
				if ( isset( $_GET['page'] ) && 'topsms-setup' === $_GET['page'] ) {
					// Hide admin menu and header.
					add_action( 'admin_head', array( $this, 'hide_admin_ui' ) );
				}
			}
		);
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/topsms-admin.js', array( 'jquery' ), time(), false );
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
	}

	/**
	 * Load files all files and dependencies required.
	 *
	 * @since    1.0.0
	 */
	private function load_dependencies() {
		require_once plugin_dir_path( __DIR__ ) . 'admin/class-topsms-rest-api-admin.php';
		$this->rest_api = new Topsms_Rest_Api_Admin( $this->plugin_name, $this->version );
	}

	/**
	 * Hide WordPress admin UI elements when on the setup page.
	 *
	 * @since    1.0.0
	 */
	public function hide_admin_ui() {
		echo '<style>
            #wpcontent { margin-left: 0 !important; }
            #adminmenumain, #wpadminbar, #wpfooter { display: none !important; }
            #topsms-admin-app { height: 100vh; }
        </style>';
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
	}

	/**
	 * Add an admin menu for Topsms.
	 *
	 * @since 1.0.0
	 */
	public function add_admin_menu() {
		$is_connected = $this->check_topsms_connection();

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
			$is_connected ? array( $this, 'display_automations_page' ) : array( $this, 'display_setup_page' ),
			$icon_data,
			55
		);

		add_submenu_page(
			'topsms',
			__( 'Automation', 'topsms' ),
			__( 'Automation', 'topsms' ),
			'manage_options',
			'topsms-automations',
			array( $this, 'display_automations_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Settings', 'topsms' ),
			__( 'Settings', 'topsms' ),
			'manage_options',
			'topsms-settings',
			array( $this, 'display_settings_page' )
		);

		add_submenu_page(
			'topsms',
			__( 'Analytics', 'topsms' ),
			__( 'Analytics', 'topsms' ),
			'manage_options',
			'topsms-analytics',
			array( $this, 'display_analytics_page' )
		);

		// Remove the duplicated submenu.
		remove_submenu_page( 'topsms', 'topsms' );
	}

	/**
	 * Render the setup page.
	 *
	 * @since    1.0.0
	 */
	public function display_setup_page() {
		$is_connected = $this->check_topsms_connection();

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsData',
			array(
				'restUrl'     => esc_url_raw( rest_url() ),
				'nonce'       => wp_create_nonce( 'wp_rest' ),
				'isConnected' => $is_connected,
			)
		);

		// Container for React app.
		echo '<div class="wrap">';
		echo '<div id="topsms-admin-setup" class="topsms-app"></div>';
		echo '</div>';
	}


	/**
	 * Render the analytics page.
	 *
	 * @since    1.0.0
	 */
	public function display_analytics_page() {

		$is_connected = $this->check_topsms_connection();
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
	public function display_automations_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->check_topsms_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsData',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL,
			)
		);

		// Container for React app.
		echo '<div class="wrap">';
		echo '<div id="topsms-admin-automations" class="topsms-app"></div>';
		echo '</div>';
	}

	/**
	 * Render the general settings page.
	 *
	 * @since    1.0.0
	 */
	public function display_settings_page() {
		// Check if connected, if not, redirect to the setup page.
		$is_connected = $this->check_topsms_connection();
		if ( ! $is_connected ) {
			wp_safe_redirect( admin_url( 'admin.php?page=topsms-setup' ) );
			exit;
		}

		// Pass data to JavaScript.
		wp_localize_script(
			'topsms-admin-app',
			'topsmsData',
			array(
				'restUrl'   => esc_url_raw( rest_url() ),
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL,
			)
		);

		// Container for React app.
		echo '<div class="wrap">';
		echo '<div id="topsms-admin-settings" class="topsms-app"></div>';
		echo '</div>';
	}

	/**
	 * Checks if access token and refresh token exist to determine
	 * if the plugin is connected to the TopSMS API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @return   boolean    True if connected, false otherwise.
	 */
	private function check_topsms_connection() {
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
			$is_connected = $this->check_topsms_connection();

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
				'timeout' => 30,
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
		$consent_enabled = get_post_meta( $order_id, 'topsms_customer_consent', true );

		// Only show the checkbox if this setting is enabled; Return if disabled.
		if ( ! $consent_enabled || 'no' === $consent_enabled ) {
			return;
		}

		global $wpdb;

		// Remove 'wc-' prefix if present in status.
		$status_to = str_replace( 'wc-', '', $status_to );

		// Get configuration from options table.
		$access_token     = get_option( 'topsms_access_token' );
		$sender           = $this->fetch_sender_name();
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
				'timeout' => 45,
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

		$balance = isset( $data['remainingBalance'] ) ? $data['remainingBalance'] : '';
		if ( $balance ) {
			$this->low_balance_alert( (int) $balance );
		}
	}

	/**
	 * Sends SMS alert notifications to customers when low SMS balance.
	 *
	 * @since    1.0.1
	 * @param    int $balance    Current account balance.
	 */
	private function low_balance_alert( $balance ) {
		// Get low balance alert option.
		$low_balance_option = get_option( 'topsms_settings_low_balance_alert', 'no' );
		if ( 'no' === $low_balance_option ) {
			return;
		} else {
			// Check if the transient exists.
			// If transient doesn't exist (has expired or was never set), set it to true.
			$send_sms_transient = get_transient( 'topsms_send_sms' );
			if ( false === $send_sms_transient ) {
				set_transient( 'topsms_send_sms', true );
			}

			// If low balance and of transient of send_sms is true, get user phone number and send sms (call Topsms api).
			if ( $balance < self::LOW_BALANCE_THRESHOLD ) {
				if ( get_transient( 'topsms_send_sms' ) ) {
					$registration_data = get_option( 'topsms_registration_data', array() );
					if ( ! empty( $registration_data ) ) {
						$access_token = get_option( 'topsms_access_token' );
						$user_phone   = isset( $registration_data['phone_number'] ) ? $registration_data['phone_number'] : '';
						$user_company = isset( $registration_data['company'] ) ? $registration_data['company'] : '';
						$sender       = $this->fetch_sender_name();
						$message      = 'Alert: Your SMS balance is running low (under 50) on ' . $user_company . '. Please top up soon to avoid interruption to order notifications.';

						// Send SMS.
						$url  = 'https://api.topsms.com.au/functions/v1/sms';
						$body = array(
							'phone_number' => $user_phone,
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
								'timeout' => 45,
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

						// Set transient to false for 24 hours.
						set_transient( 'topsms_send_sms', false, DAY_IN_SECONDS );
					}
				}
			} else {
				delete_transient( 'topsms_send_sms' );
			}
		}
	}

	/**
	 * Fetch the registered sender name from the remote Topsms API.
	 *
	 * @since    1.0.1
	 * @return   $sender Sender name of the SMS.
	 */
	private function fetch_sender_name() {
		$access_token = get_option( 'topsms_access_token' );
		$sender       = '';

		// Get sender name from the remote api.
		$response = wp_remote_get(
			'https://api.topsms.com.au/functions/v1/user',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return $sender;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check the status field in the response data.
		if ( isset( $data['status'] ) && 'success' === $data['status'] ) {
			$sender = isset( $data['data']['sender'] ) ? $data['data']['sender'] : '';
		}

		return $sender;
	}
}
