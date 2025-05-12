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
class Topsms_Rest_Api_Admin {

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

	}

    /**
     * Send otp to the given phone number
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_send_otp(WP_REST_Request $request) {
        $body_params = $request->get_json_params();
        // error_log("body params: " . print_r($body_params, true));

        // Get phone number from the request
        $phone_number = $body_params['phoneNumber'];
        if (empty($phone_number)) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => 'Phone number is required')
            ), 400);
        }
        
        // Format the phone number (remove all non-digits)
        $formatted_number = preg_replace('/[^0-9]/', '', $phone_number);
        
        // Remove leading 61 if present
        if (substr($formatted_number, 0, 2) === '61') {
            $formatted_number = substr($formatted_number, 2);
        }
        error_log("phone number" . print_r($formatted_number, true));
        
        // Make api request to Topsms
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
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $response->get_error_message())
            ), 500);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        // Check HTTP status code
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => isset($data['message']) ? $data['message'] : 'Failed to send OTP')
            ), wp_remote_retrieve_response_code($response));
        }
        
        // Check the status field in the response data
        if (isset($data['status']) && $data['status'] === 'success') {
            // wp_send_json_success format: { "success": true, "data": ... }
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $data
            ), 200);
        } else {
            // If status is not success or doesn't exist, send error
            $error_message = isset($data['message']) ? $data['message'] : 'Failed to send OTP';
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $error_message)
            ), 400);
        }
    }

    /**
     * Verify otp according to the phone and registers the user in topsms
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_verify_otp(WP_REST_Request $request) {
        // Get payload from the request
        $body_params = $request->get_json_params();
        $payload = $body_params['payload'];
        // error_log("payload:" . print_r($payload, true));

        if (empty($payload)) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => 'Verification data is required')
            ), 400);
        }

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
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $response->get_error_message())
            ), 500);
        }
        
        // Get and decode the response body
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        // error_log("OTP Verification Response: " . print_r($data, true));
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        // Check if any error
        if (isset($data['status']) && $data['status'] === 'error' && isset($data['error'])) {
            $error_message = $data['error'];
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $error_message)
            ), 400);
        }
        // Check for the mailchimp nested error 
        else if (isset($data['mailchimp']['data']['errors']) && is_array($data['mailchimp']['data']['errors']) && !empty($data['mailchimp']['data']['errors'])) {
            // Get the error
            $error = $data['mailchimp']['data']['errors'][0];
            $error_message = isset($error['error_code']) ? $error['error_code'] : 'Unknown error occurred';
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $error_message)
            ), 400);
        } else {
            // Store tokens
            $access_token = isset($data['session']['access_token']) ? $data['session']['access_token'] : '';
            $refresh_token = isset($data['session']['refresh_token']) ? $data['session']['refresh_token'] : '';
            error_log("access token " . print_r($access_token, true));
            error_log("refresh token " . print_r($refresh_token, true));

            if (empty($access_token) || empty($refresh_token)) {
                $error_message = 'Unknown error occurred';
                return new WP_REST_Response(array(
                    'success' => false,
                    'data' => array('message' => $error_message)
                ), 400);
            } else {
                $this->topsms_store_tokens($access_token, $refresh_token);
                $this->topsms_store_registration_data($payload);

                return new WP_REST_Response(array(
                    'success' => true,
                    'data' => $data
                ), 200);
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

    /**
     * Store Topsms registration data  in the options table
     *
     * @param string $data The user registration data
     */
    private function topsms_store_registration_data($data) {
        // Sanitise the data and add timestamp
        $data_ = [
            'phone_number' => isset($data['phone_number']) ? sanitize_text_field($data['phone_number']) : '',
            'otp' => isset($data['otp']) ? sanitize_text_field($data['otp']) : '',
            'email' => isset($data['email']) ? sanitize_email($data['email']) : '',
            'company' => isset($data['company']) ? sanitize_text_field($data['company']) : '',
            'address' => isset($data['address']) ? sanitize_text_field($data['address']) : '',
            'first_name' => isset($data['first_name']) ? sanitize_text_field($data['first_name']) : '',
            'last_name' => isset($data['last_name']) ? sanitize_text_field($data['last_name']) : '',
            'city' => isset($data['city']) ? sanitize_text_field($data['city']) : '',
            'state' => isset($data['state']) ? sanitize_text_field($data['state']) : '',
            'postcode' => isset($data['postcode']) ? sanitize_text_field($data['postcode']) : '',
            'abn' => isset($data['abn']) ? sanitize_text_field($data['abn']) : '',
            'sender' => isset($data['sender']) ? sanitize_text_field($data['sender']) : '',
            'connected_at' => current_time('mysql') // Connected timestamp
        ];

        // Store the entire registration data
        update_option('topsms_registration_data', $data_);

        // Store sender separately
        update_option('topsms_sender', $data_['sender']);
    }

    /**
     * Get topsms automations woocommerce status settings, including enabled option and sms template
     * from the options table
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_get_automations_status_settings(WP_REST_Request $request) {
        // Get status key from the url params
        $status_key = $request->get_param('status_key');
        if (empty($status_key)) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => 'Status key is required')
            ), 400);
        }

        // Get enabled setting for this status
        $enabled_option_name = 'topsms_order_' . $status_key . '_enabled';
        $enabled = get_option($enabled_option_name);
        // Set default to true (enabled)
        if (false === $enabled) {
            $enabled = true; 
        }
        
        // Get sms template for this status
        $message_option_name = 'topsms_order_' . $status_key . '_message';
        $template = get_option($message_option_name);
        // Set default to empty string
        if (false === $template) {
            $template = ''; 
        }

        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'status_key' => $status_key,
                'enabled' => (bool) $enabled,
                'template' => $template
            ]
        ], 200);
    }
    
    /**
     * Save topsms automation woocommerce status enabled option 
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_save_automations_status_enabled(WP_REST_Request $request) {
        // Get status key and enabled option
        $body_params = $request->get_json_params();
        error_log("save status enabled:" . print_r($body_params, true));

        if (!isset($body_params['status_key']) || !isset($body_params['enabled'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => 'Missing required parameters: status_key and enabled.')
            ), 400);
        }
        
        $status_key = sanitize_text_field($body_params['status_key']);
        $enabled = (bool) $body_params['enabled'];
        
        // Update the enabled setting 
        $enabled_option_name = 'topsms_order_' . $status_key . '_enabled';
        update_option($enabled_option_name, $enabled);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'message' => 'Status settings saved successfully',
                'status_key' => $status_key,
                'enabled' => $enabled
            ]
        ], 200);
    }

    /**
     * Save topsms automation woocommerce status sms template
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_save_automations_status_template(WP_REST_Request $request) {
        // Get status key and enabled option
        $body_params = $request->get_json_params();
        error_log("save status template:" . print_r($body_params, true));

        if (!isset($body_params['status_key']) || !isset($body_params['template'])) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => 'Missing required parameters: status_key and template.')
            ), 400);
        }
        
        $status_key = sanitize_text_field($body_params['status_key']);
        $template = sanitize_text_field($body_params['template']);
        
        // Update the template
        $message_option_name = 'topsms_order_' . $status_key . '_message';
        update_option($message_option_name, $template);
    
        
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'message' => 'Status settings saved successfully',
                'status_key' => $status_key,
                'template' => $template
            ]
        ], 200);
    }

    /**
     * Get topsms general setting from the options table
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_get_settings(WP_REST_Request $request) {
        // Get key from request
        $key = $request->get_param('key');
        
        if (empty($key)) {
            return new WP_REST_Response([
                'success' => false,
                'data' => [
                    'message' => 'Setting key is required'
                ]
            ], 400);
        }
        
        // Get option name
        $option_name = 'topsms_settings_' . $key;
        $settings = get_option($option_name, true);
        
        // Set default value if not found
        if (false === $settings) {
            $enabled = true;
        } else {
            $enabled = $settings;
            
            // // If the value is a string 'true' or 'false', convert it to boolean
            // if ($settings === 'true') {
            //     $enabled = true;
            // } else if ($settings === 'false') {
            //     $enabled = false;
            // } 
            // else if (is_bool($settings)) {
            //     $enabled = $settings;
            // }
        }
        
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'key' => $key,
                'enabled' => $enabled,
            ]
        ], 200);
    }

    /**
     * Save topsms general settings 
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_save_settings(WP_REST_Request $request) {
        // Get data from request
        $body_params = $request->get_json_params();
        error_log("save setting:" . print_r($body_params, true));
        
        // Validate required parameters
        if (!isset($body_params['key']) || !isset($body_params['enabled'])) {
            return new WP_REST_Response([
                'success' => false,
                'data' => [
                    'message' => 'Missing required parameters: key and enabled'
                ]
            ], 400);
        }
        
        $key = sanitize_text_field($body_params['key']);
        $enabled = (bool) $body_params['enabled'];
        
        // Get option name and update the settings to options
        $option_name = 'topsms_settings_' . $key;
        update_option($option_name, $enabled);
        
        return new WP_REST_Response([
            'success' => true,
            'data' => [
                'message' => 'Setting saved successfully',
                'key' => $key,
                'enabled' => $enabled
            ]
        ], 200);
    }

    /**
     * Get the user data, identified by the topsms access token in the options
     * 
     * @param WP_REST_Request $request The request object
     * @return WP_REST_Response The response
     */
    public function topsms_get_user_data(WP_REST_Request $request) {
        $access_token = get_option('topsms_access_token');
        error_log("topsms access token" . print_r($access_token, true));
        
        // Make api request to Topsms
        $response = wp_remote_post('https://api.topsms.com.au/functions/v1/user', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $access_token
            ],
        ]);
        
        // Check for connection errors
        if (is_wp_error($response)) {
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $response->get_error_message())
            ), 500);
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        // Check the status field in the response data
        if (isset($data['status']) && $data['status'] === 'success') {
            // wp_send_json_success format: { "success": true, "data": ... }
            return new WP_REST_Response(array(
                'success' => true,
                'data' => $data
            ), 200);
        } else {
            // If status is not success or doesn't exist, send error
            $error_message = isset($data['message']) ? $data['message'] : 'Failed to fetch user data';
            return new WP_REST_Response(array(
                'success' => false,
                'data' => array('message' => $error_message)
            ), 400);
        }
    }
}