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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

        // Check if we're on the setup page
        add_action('current_screen', function($screen) {
            if (is_object($screen) && $screen->base === 'topsms_page_topsms-setup') {
                // Hide admin menu and header
                add_filter('admin_head', array($this, 'hide_admin_ui'));
            }
        });
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
        // wp_enqueue_style('wp-components');
        wp_enqueue_style( 'topsms-admin-style', plugin_dir_url(__FILE__) . 'css/topsms-admin-app.css', array(), time(), 'all');
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
            plugin_dir_url(__FILE__) . 'js/topsms-admin-app.js', 
            array(
                'wp-element', 
                'wp-components', 
                'wp-i18n',
                'wp-data',
                'wp-api-fetch',
                'wp-blocks',
                'wp-block-editor'
            ),
            time(),
            true
        ); 

	}

    public function hide_admin_ui() {
        echo '<style>
            #wpcontent { margin-left: 0 !important; }
            #adminmenumain, #wpadminbar, #wpfooter { display: none !important; }
            #topsms-admin-app { height: 100vh; }
        </style>';
    }

    /**
     * Add an admin menu for Topsms
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __( 'TopSMS', 'topsms' ),
            __( 'TopSMS', 'topsms' ),
            'manage_options',
            'topsms',
            array( $this, 'display_setup_page' ),
            'dashicons-smartphone',
            55
        );

        add_submenu_page(
            'topsms',
            __( 'Setup', 'topsms' ),
            __( 'Setup', 'topsms' ),
            'manage_options',
            'topsms-setup',
            array( $this, 'display_setup_page' )
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

        // Remove the duplicated submenu
        remove_submenu_page( 'topsms', 'topsms'); 
    }

    /**
     * Render the setup page.
     *
     * @since    1.0.0
     */
    public function display_setup_page() {
        $is_connected = $this->check_topsms_connection();
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'isConnected' => $is_connected,
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-setup"></div>';
        echo '</div>';
    }


    public function display_automations_page() {
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-automations"></div>';
        echo '</div>';
    }


    public function display_settings_page() {
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-settings"></div>';
        echo '</div>';
    }


    /**
     * Send otp to the given phone number by calling the topsms api
     *
     * @since    1.0.0
     * @return   array JSON response with status of sending the otp
     *               
     */
    function topsms_send_otp() {
        // Get phone number from the request
        $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
        if (empty($phone_number)) {
            wp_send_json_error(['message' => 'Phone number is required']);
            return;
        }
        
        // Format the phone number (remove all non-digits)
        $formatted_number = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Remove leading 61 if present
        if (substr($formatted_number, 0, 2) === '61') {
            $formatted_number = substr($formatted_number, 2);
        }
        error_log("phone number" . print_r($formatted_number, true));
        
        // Make api request to TopSMS
        $response = wp_remote_post('https://api.topsms.com.au/functions/v1/send-otp', [
            'headers' => [
            'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
            'phone_number' => $formatted_number
            ]),
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        // Check HTTP status code
        if (wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(['message' => isset($data['message']) ? $data['message'] : 'Failed to send OTP']);
            return;
        }
        
        // Check the status field in the response data
        if (isset($data['status']) && $data['status'] === 'success') {
            wp_send_json_success($data);
        } else {
            // If status is not success or doesn't exist, send error
            $error_message = isset($data['message']) ? $data['message'] : 'Failed to send OTP';
            wp_send_json_error(['message' => $error_message]);
        }
    }

    /**
     * Verifies otp and the given data and registers user in topsms
     * 
     * @return array JSON response with verification status
     */
    function topsms_verify_otp() {
        // Get payload from the request
        $payload_json = isset($_POST['payload']) ? $_POST['payload'] : '';
        // error_log("payload1:" . print_r($payload_json, true));
        if (empty($payload_json)) {
            wp_send_json_error(['message' => 'Verification data is required']);
            return;
        }
        
        // Decode the payload
        $payload_json = stripslashes($payload_json);
        $payload = json_decode($payload_json, true);
        // error_log("payload2:" . print_r($payload, true));
        
        // Format the phone number (remove all non-digits)
        $formatted_number = preg_replace('/[^0-9]/', '', $payload['phone_number']);
        
        // Remove leading 61 if present
        if (substr($formatted_number, 0, 2) === '61') {
            $formatted_number = substr($formatted_number, 2);
        }
        
        // Update the payload with formatted number
        $payload['phone_number'] = $formatted_number;
        error_log("Verifying OTP for: " . print_r($payload, true));
        
        // Make api request to topsms
        $response = wp_remote_post('https://api.topsms.com.au/functions/v1/verify', [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode($payload),
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            error_log("OTP Verification Error: " . $response->get_error_message());
            wp_send_json_error(['message' => $response->get_error_message()]);
            return;
        }
        
        // Get and decode the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        
        // Log the response
        error_log("OTP Verification Response: " . print_r($data, true));
        
        // // Check HTTP status code
        // $status_code = wp_remote_retrieve_response_code($response);
        // if ($status_code !== 200) {
        //     $error_message = isset($data['message']) ? $data['message'] : 'Failed to verify OTP (Status: ' . $status_code . ')';
        //     wp_send_json_error(['message' => $error_message]);
        //     return;
        // }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        // Check if any error
        if (isset($data['status']) && $data['status'] === 'error' && isset($data['error'])) {
            $error_message = $data['error'];
            wp_send_json_error(['message' => $error_message]);
        }
        // Check for the mailchimp nested error 
        else if (isset($data['mailchimp']['data']['errors']) && is_array($data['mailchimp']['data']['errors']) && !empty($data['mailchimp']['data']['errors'])) {
            // Get the error
            $error = $data['mailchimp']['data']['errors'][0];
            $error_message = isset($error['error_code']) ? $error['error_code'] : 'Unknown error occurred';
            wp_send_json_error(['message' => $error_message]);
        } else {
            // Store tokens
            $access_token = isset($data['session']['access_token']) ? $data['session']['access_token'] : '';
            $refresh_token = isset($data['session']['refresh_token']) ? $data['session']['refresh_token'] : '';
            error_log("access token " . print_r($access_token, true));
            error_log("refresh token " . print_r($refresh_token, true));

            if (empty($access_token) || empty($refresh_token)) {
                $error_message = 'Unknown error occurred';
                wp_send_json_error(['message' => $error_message]);
            } else {
                $this->topsms_store_tokens($access_token, $refresh_token);
                wp_send_json_success($data);
            }
        }
    }

    /**
     * Store Topsms API tokens (refresh token and access token) in the options table
     *
     * @param string $access_token The access token to store
     * @param string $refresh_token The refresh token to store
     */
    private function topsms_store_tokens($access_token, $refresh_token) {
        // Store tokens in WordPress options table
        $access_updated = update_option('topsms_access_token', $access_token);
        $refresh_updated = update_option('topsms_refresh_token', $refresh_token);
    }

    private function check_topsms_connection() {
        $access_token = get_option('topsms_access_token');
        $refresh_token = get_option('topsms_refresh_token');

        // Check if the token exists
        if (!empty($access_token) || !empty($refresh_token)) {
            return true;
        } else {
            return false;
        }
    }
}
