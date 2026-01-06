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
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 * @param object $helper The helper instance.
	 */
	public function __construct( $plugin_name, $version, $helper ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->helper      = $helper;
	}

	/**
	 * Send otp to the given phone number.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_send_otp( WP_REST_Request $request ) {
		$body_params = $request->get_json_params();

		// Get phone number from the request.
		$phone_number = $body_params['phoneNumber'];
		if ( empty( $phone_number ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Phone number is required',
					),
				),
				400
			);
		}

		// Format the phone number (remove all non-digits).
		$formatted_number = preg_replace( '/[^0-9]/', '', $phone_number );

		// Remove leading 61 if present.
		if ( substr( $formatted_number, 0, 2 ) === '61' ) {
			$formatted_number = substr( $formatted_number, 2 );
		}

		// Make api request to Topsms.
		$response = wp_remote_post(
			'https://api.topsms.com.au/functions/v1/send-otp',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode(
					array(
						'phone_number' => $formatted_number,
					)
				),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $response->get_error_message(),
					),
				),
				500
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check HTTP status code.
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => isset( $data['message'] ) ? $data['message'] : 'Failed to send OTP',
					),
				),
				wp_remote_retrieve_response_code( $response )
			);
		}

		// Check the status field in the response data.
		if ( isset( $data['status'] ) && 'success' === $data['status'] ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => $data,
				),
				200
			);
		} else {
			// If status is not success or doesn't exist, send error.
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Failed to send OTP';
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $error_message,
					),
				),
				500
			);
		}
	}

	/**
	 * Verify otp according to the phone and registers the user in topsms.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_verify_otp( WP_REST_Request $request ) {
		// Get payload from the request.
		$body_params = $request->get_json_params();
		$payload     = $body_params['payload'];

		if ( empty( $payload ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Verification data is required',
					),
				),
				400
			);
		}

		// Format the phone number (remove all non-digits).
		$formatted_number = preg_replace( '/[^0-9]/', '', $payload['phone_number'] );

		// Remove leading 61 if present.
		if ( substr( $formatted_number, 0, 2 ) === '61' ) {
			$formatted_number = substr( $formatted_number, 2 );
		}

		// Update the payload with formatted number.
		$payload['phone_number'] = $formatted_number;

		// Get the home/website url.
		$website_url        = get_home_url();
		$payload['website'] = $website_url;

		// Make api request to topsms.
		$response = wp_remote_post(
			'https://api.topsms.com.au/functions/v1/verify',
			array(
				'headers' => array(
					'Content-Type' => 'application/json',
				),
				'body'    => wp_json_encode( $payload ),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $response->get_error_message(),
					),
				),
				500
			);
		}

		// Get and decode the response body.
		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check if any error.
		if ( isset( $data['status'] ) && 'error' === $data['status'] && isset( $data['error'] ) ) {
			$error_message = $data['error'];
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $error_message,
					),
				),
				500
			);
		} elseif ( isset( $data['mailchimp']['data']['errors'] ) && is_array( $data['mailchimp']['data']['errors'] ) && ! empty( $data['mailchimp']['data']['errors'] ) ) {
			// Check for the mailchimp nested error.
			$error         = $data['mailchimp']['data']['errors'][0];
			$error_message = isset( $error['error_code'] ) ? $error['error_code'] : 'Unknown error occurred';
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $error_message,
					),
				),
				500
			);
		} else {
			// Store tokens.
			$access_token  = isset( $data['session']['access_token'] ) ? $data['session']['access_token'] : '';
			$refresh_token = isset( $data['session']['refresh_token'] ) ? $data['session']['refresh_token'] : '';

			if ( empty( $access_token ) || empty( $refresh_token ) ) {
				$error_message = 'Unknown error occurred';
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => $error_message,
						),
					),
					500
				);
			} else {
				$this->topsms_store_tokens( $access_token, $refresh_token );
				$this->topsms_store_registration_data( $payload );

				return new WP_REST_Response(
					array(
						'success' => true,
						'data'    => $data,
					),
					200
				);
			}
		}
	}

	/**
	 * Store Topsms API tokens (refresh token and access token) in the options table.
	 *
	 * @since    1.0.0
	 * @param string $access_token The access token to store.
	 * @param string $refresh_token The refresh token to store.
	 */
	private function topsms_store_tokens( $access_token, $refresh_token ) {
		// Store tokens in WordPress options table.
		$access_updated  = update_option( 'topsms_access_token', $access_token );
		$refresh_updated = update_option( 'topsms_refresh_token', $refresh_token );
	}

	/**
	 * Store Topsms registration data  in the options table
	 *
	 * @since    1.0.0
	 * @param string $data The user registration data.
	 */
	private function topsms_store_registration_data( $data ) {
		// Sanitise the data and add timestamp.
		$data_ = array(
			'phone_number' => isset( $data['phone_number'] ) ? $data['phone_number'] : '',
			'otp'          => isset( $data['otp'] ) ? $data['otp'] : '',
			'email'        => isset( $data['email'] ) ? $data['email'] : '',
			'company'      => isset( $data['company'] ) ? $data['company'] : '',
			'address'      => isset( $data['address'] ) ? $data['address'] : '',
			'first_name'   => isset( $data['first_name'] ) ? $data['first_name'] : '',
			'last_name'    => isset( $data['last_name'] ) ? $data['last_name'] : '',
			'city'         => isset( $data['city'] ) ? $data['city'] : '',
			'state'        => isset( $data['state'] ) ? $data['state'] : '',
			'postcode'     => isset( $data['postcode'] ) ? $data['postcode'] : '',
			'abn'          => isset( $data['abn'] ) ? $data['abn'] : '',
			'sender'       => isset( $data['sender'] ) ? $data['sender'] : '',
			'connected_at' => current_time( 'mysql' ), // Connected timestamp.
		);

		// Store the entire registration data.
		update_option( 'topsms_registration_data', $data_ );

		// Store sender separately.
		update_option( 'topsms_sender', $data_['sender'] );
	}

	/**
	 * Get topsms automations woocommerce status settings, including enabled option and sms template.
	 * from the options table
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_get_automations_status_settings( WP_REST_Request $request ) {
		// Get status key and delivery type from the url params.
		$status_key = $request->get_param( 'status_key' );
        $delivery_type = $request->get_param('delivery_type');
		if ( empty( $status_key ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Status key is required',
					),
				),
				400
			);
		}

		// Get enabled setting for this status.
		$enabled_option_name = 'topsms_order_' . $status_key . '_enabled';
		$enabled             = get_option( $enabled_option_name );
		// Set default to no (disabled).
		if ( false === $enabled ) {
			$enabled = 'no';
		}

        // If delivery_type is not provided, only return the enabled status.
        if ( empty( $delivery_type ) ) {
            return new WP_REST_Response(
                array(
                    'success' => true,
                    'data'    => array(
                        'status_key' => $status_key,
                        'enabled'    => 'yes' === $enabled,
                    ),
                ),
                200
            );
        }

        // Validate delivery_type if provided.
        if ( ! in_array( $delivery_type, array( 'shipping', 'pickup' ) ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'data'    => array(
                        'message' => 'Valid delivery type (shipping or pickup) is required.',
                    ),
                ),
                400
            );
        }

		// Get sms template for this status and delivery type.
		$message_option_name = 'topsms_order_' . $status_key . '_' . $delivery_type . '_message';
		$template            = get_option( $message_option_name );
		// Set default to empty string.
		if ( false === $template ) {
			$template = '';
		}

		// Get copy SMS settings.
		$copy_enabled_option = 'topsms_order_' . $status_key . '_' . $delivery_type . '_copy_sms_enabled';
		$copy_numbers_option = 'topsms_order_' . $status_key . '_' . $delivery_type . '_copy_sms_numbers';
		$copy_sms_enabled    = get_option( $copy_enabled_option );
		$copy_sms_numbers    = get_option( $copy_numbers_option );

		// Set defaults.
		if ( false === $copy_sms_enabled ) {
			$copy_sms_enabled = 'no';
		}
		if ( false === $copy_sms_numbers ) {
			$copy_sms_numbers = '';
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'status_key'        => $status_key,
                    'delivery_type'     => $delivery_type,
					'enabled'           => 'yes' === $enabled,
					'template'          => $template,
					'copy_sms_enabled'  => 'yes' === $copy_sms_enabled,
					'copy_sms_numbers'  => $copy_sms_numbers,
				),
			),
			200
		);
	}

	/**
	 * Save topsms automation woocommerce status enabled option.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_save_automations_status_enabled( WP_REST_Request $request ) {
		// Get status key and enabled option.
		$body_params = $request->get_json_params();

		if ( ! isset( $body_params['status_key'] ) || ! isset( $body_params['enabled'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameters: status_key and enabled.',
					),
				),
				400
			);
		}

		$status_key = sanitize_text_field( $body_params['status_key'] );
		// Convert boolean to "yes"/"no" string.
		$enabled = filter_var( $body_params['enabled'], FILTER_VALIDATE_BOOLEAN ) ? 'yes' : 'no';

		// Update the enabled setting.
		$enabled_option_name = 'topsms_order_' . $status_key . '_enabled';
		update_option( $enabled_option_name, $enabled );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'message'    => 'Status settings saved successfully',
					'status_key' => $status_key,
					'enabled'    => 'yes' === $enabled,
				),
			),
			200
		);
	}

	/**
	 * Save topsms automation woocommerce status sms template.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_save_automations_status_template( WP_REST_Request $request ) {
		// Get status key and enabled option.
		$body_params = $request->get_json_params();

		if ( ! isset( $body_params['status_key'] ) || ! isset( $body_params['template'] ) || ! isset( $body_params['delivery_type'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameters: status_key and template, and delivery_type.',
					),
				),
				400
			);
		}

		$status_key       = sanitize_text_field( $body_params['status_key'] );
        $delivery_type    = sanitize_text_field( $body_params['delivery_type'] );
		$template         = $body_params['template'];
		$copy_sms_enabled = isset( $body_params['copy_sms_enabled'] ) ? (bool) $body_params['copy_sms_enabled'] : false;
		$copy_sms_numbers = isset( $body_params['copy_sms_numbers'] ) ? sanitize_text_field( $body_params['copy_sms_numbers'] ) : '';

        // Validate delivery type.
        if ( ! in_array( $delivery_type, array( 'shipping', 'pickup' ) ) ) {
            return new WP_REST_Response(
                array(
                    'success' => false,
                    'data'    => array(
                        'message' => 'Invalid delivery type. Must be either "shipping" or "pickup".',
                    ),
                ),
                400
            );
        }
		
		// Update the template.
		$message_option_name = 'topsms_order_' . $status_key . '_' . $delivery_type . '_message';
		update_option( $message_option_name, $template );

		// Update copy SMS settings.
		$copy_enabled_option = 'topsms_order_' . $status_key . '_' . $delivery_type . '_copy_sms_enabled';
		$copy_numbers_option = 'topsms_order_' . $status_key . '_' . $delivery_type . '_copy_sms_numbers';
		update_option( $copy_enabled_option, $copy_sms_enabled ? 'yes' : 'no' );
		update_option( $copy_numbers_option, $copy_sms_numbers );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'message'           => 'Status settings saved successfully',
					'status_key'        => $status_key,
                    'delivery_type'     => $delivery_type,
					'template'          => $template,
					'copy_sms_enabled'  => $copy_sms_enabled,
					'copy_sms_numbers'  => $copy_sms_numbers,
				),
			),
			200
		);
	}

	/**
	 * Get topsms general setting from the options table.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_get_settings( WP_REST_Request $request ) {
		// Get key from request.
		$key = $request->get_param( 'key' );

		if ( empty( $key ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameter: key.',
					),
				),
				400
			);
		}

		// Get option name.
		if ( 'sender' === $key ) {
			$option_name = 'topsms_' . $key;
		} else {
			$option_name = 'topsms_settings_' . $key;
		}
		$settings = get_option( $option_name, 'no' );

		// If option doesn't exist in database.
		if ( false === $settings ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'key'     => $key,
						'enabled' => no, // Default for new settings.
						'value'   => '',
					),
				),
				200
			);
		}

		// For toggle settings.
		if ( 'yes' === $settings || 'no' === $settings ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'key'     => $key,
						'enabled' => 'yes' === $settings,
						'value'   => '',
					),
				),
				200
			);
		}

		// For surcharge amount / sender name.
		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'key'     => $key,
					'enabled' => 'no',
					'value'   => $settings,
				),
			),
			200
		);
	}

	/**
	 * Save topsms general settings.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_save_settings( WP_REST_Request $request ) {
		// Get data from request.
		$body_params = $request->get_json_params();

		// Validate required parameters.
		if ( ! isset( $body_params['key'] ) || ! isset( $body_params['enabled'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameters: key and enabled',
					),
				),
				400
			);
		}

		$key = sanitize_text_field( $body_params['key'] );
		// Convert boolean to "yes"/"no" string.
		$enabled = filter_var( $body_params['enabled'], FILTER_VALIDATE_BOOLEAN ) ? 'yes' : 'no';

		// Get option name and update the settings to options.
		$option_name = 'topsms_settings_' . $key;
		update_option( $option_name, $enabled );

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'message' => 'Setting saved successfully',
					'key'     => $key,
					'enabled' => 'yes' === $enabled,
				),
			),
			200
		);
	}

	/**
	 * Save general input settings to the options.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_save_settings_( WP_REST_Request $request ) {
		// Get data from request.
		$body_params = $request->get_json_params();

		// Validate required parameters.
		if ( ! isset( $body_params['key'] ) || ! isset( $body_params['value'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameters: key and value',
					),
				),
				400
			);
		}

		$key   = sanitize_text_field( $body_params['key'] );
		$value = $body_params['value'];

		// Get option name based on key.
		if ( 'sender' === $key ) {
			$option_name = 'topsms_' . $key;

			// Update sender name in Topsms api.
			return $this->topsms_update_api_sender_name( $value, $key, $option_name );
		} else {
			$option_name = 'topsms_settings_' . $key;

			// Update the option in the database.
			update_option( $option_name, $value );

			// Return success response.
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'message' => 'Setting saved successfully',
						'key'     => $key,
						'value'   => $value,
					),
				),
				200
			);
		}
	}

	/**
	 * Update sender name in TopSMS API.
	 *
	 * @since    1.0.0
	 * @param string $sender The sender name to update.
	 * @param string $key The option key.
	 * @param string $option_name The option name in the database.
	 * @return WP_REST_Response The response.
	 */
	private function topsms_update_api_sender_name( $sender, $key, $option_name ) {
		// Get access token for API request.
		$access_token = get_option( 'topsms_access_token' );

		if ( ! $access_token ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Access token not found',
						'key'     => $key,
						'value'   => $sender,
					),
				),
				400
			);
		}

		// Make a put request to the Topsms to update the sender name.
		$response = wp_remote_request(
			'https://api.topsms.com.au/functions/v1/user',
			array(
				'method'  => 'PUT',
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'body'    => wp_json_encode(
					array(
						'sender' => $sender,
					)
				),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $response->get_error_message(),
						'key'     => $key,
						'value'   => $sender,
					),
				),
				500
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check the status field in the response data.
		if ( isset( $data['status'] ) && 'success' === $data['status'] ) {
			// Update in the options.
			update_option( $option_name, $sender );

			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'message'      => 'Setting saved successfully',
						'key'          => $key,
						'value'        => $sender,
						'api_response' => $data,
					),
				),
				200
			);
		} else {
			// If API update failed, still return success since we saved locally.
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Failed to update sender on API';

			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Error saving sender name: ' . $error_message,
						'key'     => $key,
						'value'   => $sender,
					),
				),
				500
			);
		}
	}

	/**
	 * Get the user data, identified by the topsms access token in the options.
	 *
	 * @since    1.0.0
	 * @return WP_REST_Response The response.
	 */
	public function topsms_get_user_data() {
		$access_token = get_option( 'topsms_access_token' );

		if ( ! $access_token ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Access token not found',
					),
				),
				401
			);
		}

		// Make api request to Topsms.
		$response = wp_remote_get(
			'https://api.topsms.com.au/functions/v1/user',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $response->get_error_message(),
					),
				),
				500
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check the status field in the response data.
		if ( isset( $data['status'] ) && 'success' === $data['status'] ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => $data,
				),
				200
			);
		} else {
			// If status is not success or doesn't exist, send error.
			$error_message = isset( $data['message'] ) ? $data['message'] : 'Failed to fetch user data';
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $error_message,
					),
				),
				500
			);
		}
	}

	/**
	 * REST API callback for fetching SMS logs with filtering and pagination.
	 *
	 * @since    1.0.0
	 * @param WP_REST_Request $request The REST request object.
	 * @return array Response data.
	 */
	public function topsms_get_analytics_logs( WP_REST_Request $request ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_logs';

		// Get parameters from request.
		$page         = $request->get_param( 'page' ) ? $request->get_param( 'page' ) : 1;
		$per_page     = $request->get_param( 'per_page' ) ? $request->get_param( 'per_page' ) : 10;
		$after_date   = $request->get_param( 'after' );
		$before_date  = $request->get_param( 'before' );
		$status       = $request->get_param( 'status' );
		$order_status = $request->get_param( 'order_status' );

		// Generate a cache key based on query parameters.
		$cache_key = 'topsms_logs_' . md5(
			serialize(
				array(
					$page,
					$per_page,
					$after_date,
					$before_date,
					$status,
					$order_status,
				)
			)
		);

		// Try to get cached results first.
		$response = wp_cache_get( $cache_key, 'topsms_analytics' );
		if ( $response ) {
			return $response;
		}

		// Calculate offset.
		$offset = ( $page - 1 ) * $per_page;

		// Build the complete SQL for count query with all conditions.
		if ( ! empty( $after_date ) && ! empty( $before_date ) && ! empty( $status ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s AND order_status = %s',
					$table_name,
					$after_date,
					$before_date,
					$status,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$before_date,
					$status,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s AND order_status = %s GROUP BY status',
					$table_name,
					$after_date,
					$before_date,
					$status,
					$order_status
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $before_date ) && ! empty( $status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s',
					$table_name,
					$after_date,
					$before_date,
					$status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$before_date,
					$status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND status = %s GROUP BY status',
					$table_name,
					$after_date,
					$before_date,
					$status
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $before_date ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND order_status = %s',
					$table_name,
					$after_date,
					$before_date,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$before_date,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND creation_date <= %s AND order_status = %s GROUP BY status',
					$table_name,
					$after_date,
					$before_date,
					$order_status
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $status ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND status = %s AND order_status = %s',
					$table_name,
					$after_date,
					$status,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND status = %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$status,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND status = %s AND order_status = %s GROUP BY status',
					$table_name,
					$after_date,
					$status,
					$order_status
				)
			);
		} elseif ( ! empty( $before_date ) && ! empty( $status ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date <= %s AND status = %s AND order_status = %s',
					$table_name,
					$before_date,
					$status,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date <= %s AND status = %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$before_date,
					$status,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date <= %s AND status = %s AND order_status = %s GROUP BY status',
					$table_name,
					$before_date,
					$status,
					$order_status
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $before_date ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND creation_date <= %s',
					$table_name,
					$after_date,
					$before_date
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND creation_date <= %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$before_date,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND creation_date <= %s GROUP BY status',
					$table_name,
					$after_date,
					$before_date
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND status = %s',
					$table_name,
					$after_date,
					$status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND status = %s GROUP BY status',
					$table_name,
					$after_date,
					$status
				)
			);
		} elseif ( ! empty( $after_date ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s AND order_status = %s',
					$table_name,
					$after_date,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s AND order_status = %s GROUP BY status',
					$table_name,
					$after_date,
					$order_status
				)
			);
		} elseif ( ! empty( $before_date ) && ! empty( $status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date <= %s AND status = %s',
					$table_name,
					$before_date,
					$status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date <= %s AND status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$before_date,
					$status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date <= %s AND status = %s GROUP BY status',
					$table_name,
					$before_date,
					$status
				)
			);
		} elseif ( ! empty( $before_date ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date <= %s AND order_status = %s',
					$table_name,
					$before_date,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date <= %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$before_date,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date <= %s AND order_status = %s GROUP BY status',
					$table_name,
					$before_date,
					$order_status
				)
			);
		} elseif ( ! empty( $status ) && ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE status = %s AND order_status = %s',
					$table_name,
					$status,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE status = %s AND order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$status,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE status = %s AND order_status = %s GROUP BY status',
					$table_name,
					$status,
					$order_status
				)
			);
		} elseif ( ! empty( $after_date ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date >= %s',
					$table_name,
					$after_date
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date >= %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$after_date,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date >= %s GROUP BY status',
					$table_name,
					$after_date
				)
			);
		} elseif ( ! empty( $before_date ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE creation_date <= %s',
					$table_name,
					$before_date
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE creation_date <= %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$before_date,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE creation_date <= %s GROUP BY status',
					$table_name,
					$before_date
				)
			);
		} elseif ( ! empty( $status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE status = %s',
					$table_name,
					$status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE status = %s GROUP BY status',
					$table_name,
					$status
				)
			);
		} elseif ( ! empty( $order_status ) ) {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s WHERE order_status = %s',
					$table_name,
					$order_status
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s WHERE order_status = %s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$order_status,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s WHERE order_status = %s GROUP BY status',
					$table_name,
					$order_status
				)
			);
		} else {
			$total_items = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*) FROM %1s',
					$table_name
				)
			);

			// Get logs with pagination.
			$logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT * FROM %1s ORDER BY creation_date DESC LIMIT %d OFFSET %d',
					$table_name,
					$per_page,
					$offset
				)
			);

			// Get status counts.
			$status_counts = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT status, COUNT(*) as count FROM %1s GROUP BY status',
					$table_name
				)
			);
		}

		// Calculate total pages.
		$total_pages = ceil( $total_items / $per_page );

		// Format status counts.
		$formatted_status_counts = array();
		$statuses                = array( 'delivered', 'pending', 'failed', 'sent' );

		foreach ( $statuses as $s ) {
			$count = 0;
			foreach ( $status_counts as $sc ) {
				if ( $sc->status === $s ) {
					$count = intval( $sc->count );
					break;
				}
			}
			$formatted_status_counts[] = array(
				'status'     => $s,
				'count'      => $count,
				'percentage' => $total_items > 0 ? round( ( $count / $total_items ) * 100 ) : 0,
			);
		}

		// Add pagination headers.
		$response = array(
			'logs'          => $logs,
			'total'         => intval( $total_items ),
			'pages'         => intval( $total_pages ),
			'page'          => intval( $page ),
			'per_page'      => intval( $per_page ),
			'status_counts' => $formatted_status_counts,
		);

		// Set the cache - cache for 5 minutes.
		wp_cache_set( $cache_key, $response, 'topsms_analytics', 300 );

		return $response;
	}

	/**
	 * Send a single test SMS via TopSMS API.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_send_test_sms( WP_REST_Request $request ) {
		// Get payload from the request.
		$body_params = $request->get_json_params();
		$payload     = $body_params['payload'];

		if ( empty( $payload ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'SMS data is required',
					),
				),
				400
			);
		}

		// Validate required fields: phone_number, sender and message.
		$required_fields = array( 'phone_number', 'sender', 'message' );
		foreach ( $required_fields as $field ) {
			if ( empty( $payload[ $field ] ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => $field . ' is required',
						),
					),
					400
				);
			}
		}

		// Format the phone number (remove all non-digits).
		$phone = $payload['phone_number'];
		$phone = preg_replace( '/[^0-9]/', '', $phone );

		// Remove leading 61 if present.
		if ( substr( $phone, 0, 2 ) === '61' ) {
			$phone = substr( $phone, 2 );
		}

		// Get data from payload.
		$sender   = isset( $payload['sender'] ) ? sanitize_text_field( $payload['sender'] ) : '';
		$message_ = isset( $payload['message'] ) ? $payload['message'] : '';
		$url      = isset( $payload['url'] ) ? esc_url_raw( $payload['url'] ) : '';

		// Generate the unsub link.
		$unsub_link = 'unsub.au/abcdef';

		// Get cache data if exists.
		$cache_key       = 'topsms_sample_customer';
		$sample_customer = wp_cache_get( $cache_key, 'topsms_customers' );

		// Do an sql query if not cached.
		if ( false === $sample_customer ) {
			// Get sample customer data from db (only get the first one).
			global $wpdb;
			$filters         = array();
			$sql             = $this->helper->topsms_build_contacts_query_( $filters, null, 'display_name', 'ASC', true, 1, 1 );
			$sample_customer = $wpdb->get_row( $sql );

			// Cache for 1 hour.
			if ( $sample_customer ) {
				wp_cache_set( $cache_key, $sample_customer, 'topsms_customers', HOUR_IN_SECONDS );
			}
		}

		// Replace tag with the data.
		if ( $sample_customer ) {
			$replacements = array(
				'[first_name]'  => $sample_customer->first_name ? $sample_customer->first_name : 'John',
				'[last_name]'   => $sample_customer->last_name ? $sample_customer->last_name : 'Doe',
				'[mobile]'      => $sample_customer->phone ? $sample_customer->phone : $phone,
				'[city]'        => $sample_customer->city ? $sample_customer->city : 'Sydney',
				'[state]'       => $sample_customer->state ? $sample_customer->state : 'NSW',
				'[postcode]'    => $sample_customer->postcode ? $sample_customer->postcode : '2000',
				'[orders]'      => $sample_customer->order_count ? $sample_customer->order_count : 20,
				'[total_spent]' => $sample_customer->total_spent ? number_format( $sample_customer->total_spent, 2 ) : '1500.00',
				'[unsubscribe]' => $unsub_link,
			);
		} else {
			// Fallback if no customers found.
			$replacements = array(
				'[first_name]'  => 'John',
				'[last_name]'   => 'Doe',
				'[mobile]'      => $phone,
				'[city]'        => 'Sydney',
				'[state]'       => 'NSW',
				'[postcode]'    => '2000',
				'[orders]'      => 20,
				'[total_spent]' => 1500.00,
				'[unsubscribe]' => $unsub_link,
			);
		}
		$message = str_replace( array_keys( $replacements ), array_values( $replacements ), $message_ );

		// Send sms.
		$status = $this->topsms_send_sms( $phone, $sender, $message, $url );

		// Check for errors.
		if ( 'Delivered' !== $status ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Failed to send SMS',
					),
				),
				500
			);
		} else {
			// SMS delivered.
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'message' => 'SMS sent successfully',
					),
				),
				200
			);
		}
	}

	/**
	 * Send a single SMS via TopSMS API.
	 *
	 * @since    2.0.0
	 * @param string $phone Customer phone number.
	 * @param string $sender The sender name of the message.
	 * @param string $message The message content to be sent.
	 * @param string $link Optional URL/link to be included in the SMS.
	 * @return string $api_status The API response.
	 */
	private function topsms_send_sms( $phone, $sender, $message, $link ) {
		// Check if required fields: phone, sender and message exist.
		if ( ! $phone || ! $sender || ! $message ) {
			return;
		}

		$access_token = get_option( 'topsms_access_token' );
		if ( ! $access_token ) {
			return;  // Access token not found.
		}

		// Check if user has enough sms balance.
		if ( ! $this->helper->check_user_balance() ) {
			return;
		}

		// Send SMS.
		$url  = 'https://api.topsms.com.au/functions/v1/sms';
		$body = array(
			'phone_number' => $phone,
			'from'         => $sender,
			'message'      => $message,
			'link'         => $link,
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

		// Check balance after sending the sms.
		$balance = isset( $data['remainingBalance'] ) ? $data['remainingBalance'] : '';
		if ( $balance ) {
			$this->helper->topsms_low_balance_alert( (int) $balance );
		}

		return $api_status;
	}

	/**
	 * Get user saved segments/filters from the contact lists.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_get_saved_filters( WP_REST_Request $request ) {
		// Get all filters.
		$filters = $this->get_contacts_lists();

		$transient_key = 'topsms_contacts_lists';

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'filters'       => $filters,
					'transient_key' => $transient_key,
				),
			),
			200
		);
	}

	/**
	 * Get the selected contact list data based on the specified filter ID.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_get_list( WP_REST_Request $request ) {
		// Get filter id from the url params.
		$filter_id = $request->get_param( 'filter_id' );
		if ( empty( $filter_id ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Filter ID is required',
					),
				),
				400
			);
		}

		// Get lists from transient.
		// If transient not found, do an sql query and get list data.
		$lists = get_transient( 'topsms_contacts_lists' );
		if ( false === $lists ) {
			$lists = $this->get_contacts_lists();
		}

		// Check if the specified filter exists.
		$list = $lists[ $filter_id ];
		if ( ! isset( $list ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'List not found',
					),
				),
				404
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'count' => $list['count'],
				),
			),
			200
		);
	}

	/**
	 * Get the saved contacts lists from transient.
	 * If transient not found, get contacts lists by the all saved filters.
	 *
	 * @since    2.0.0
	 * @return array $lists The contacts lists with all information.
	 */
	private function get_contacts_lists() {
		// Try to get lists from transient.
		$lists = get_transient( 'topsms_contacts_lists' );

		// If transient exists, return it.
		if ( false !== $lists ) {
			return $lists;
		}

		// Transient doesn't exist, do an sql query to get the contacts list and save to transient.
		global $wpdb;

		$lists   = array();
		$filters = array();

		// Add "All Contacts" as the first list (only subscribed users - if not set, default to subscribed).
		$all_contacts_filter = array();

		// Get the contacts by filter.
		$sql          = $this->helper->topsms_build_contacts_query_( $all_contacts_filter, null, false );
		$all_contacts = $wpdb->get_results( $sql, ARRAY_A ); // Store as array.

		// Filter contacts: include those with status yes (default to unsubscribed) and have phone.
		// Also make sure no duplicated phone.
		$all_contacts = $this->filter_contacts( $all_contacts );
		$all_count    = count( $all_contacts );

		// For transient data.
		$lists['all_contacts'] = array(
			'filter_id'   => 'all_contacts',
			'filter_name' => 'All Subscribed Contacts',
			'count'       => $all_count,
			'contacts'    => array_values( $all_contacts ),
		);

		// For return data.
		$filters['all_contacts'] = array(
			'id'    => 'all_contacts',
			'name'  => 'All Subscribed Contacts',
			'count' => $all_count,
		);

		// Get saved filters from options.
		$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );

		// Extract contacts data by filters.
		foreach ( $saved_filters as $filter_id => $filter ) {
			// Skip filters if status filter is unsubscribed (don't send to unsubscribed contacts).
			if ( isset( $filter['status'] ) && 'no' === $filter['status'] ) {
				// For transient data.
				$lists[ $filter_id ] = array(
					'filter_id'   => $filter_id,
					'filter_name' => $filter['name'],
					'count'       => 0,
					'contacts'    => array(),
				);

				// For return data.
				$filters[ $filter_id ] = array(
					'id'    => $filter_id,
					'name'  => $filter['name'],
					'count' => 0,
				);
				continue;
			}

			// Get the contacts by filter.
			$sql      = $this->helper->topsms_build_contacts_query_( $filter, null, false );
			$contacts = $wpdb->get_results( $sql, ARRAY_A ); // Store as array.

			// Filter contacts: include those with status yes (default to unsubscribed) and have phone.
			// Also make sure no duplicated phone.
			$contacts = $this->filter_contacts( $contacts );
			$count    = count( $contacts );

			// For transient data.
			$lists[ $filter_id ] = array(
				'filter_id'   => $filter_id,
				'filter_name' => $filter['name'],
				'count'       => $count,
				'contacts'    => array_values( $contacts ),
			);

			// For return data.
			$filters[ $filter_id ] = array(
				'id'    => $filter_id,
				'name'  => $filter['name'],
				'count' => $count,
			);
		}

		// Store all contacts lists data in transient.
		$transient_key = 'topsms_contacts_lists';
		set_transient( $transient_key, $lists );

		return $filters;
	}

	/**
	 * Clear the stored contact list from transient.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_clear_list_transient( WP_REST_Request $request ) {
		// Check if transient exists.
		$lists = get_transient( 'topsms_contacts_lists' );
		if ( false === $lists ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Transient not found',
					),
				),
				404
			);
		}

		// Clear the transient.
		$deleted = delete_transient( 'topsms_contacts_lists' );
		if ( ! $deleted ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Failed to delete transient',
					),
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success' => true,
				'data'    => array(
					'message' => 'Transient cleared successfully',
				),
			),
			200
		);
	}

	/**
	 * Schedule bulk sms campaign via TopSms API.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_schedule_campaign( WP_REST_Request $request ) {
		// Get payload from the request.
		$body_params = $request->get_json_params();

		if ( ! isset( $body_params['is_scheduled'] ) || ! isset( $body_params['campaign_data'] ) || ! isset( $body_params['cost'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Missing required parameters: is_scheduled,  campaign_data and cost.',
					),
				),
				400
			);
		}

		$is_scheduled  = isset( $body_params['is_scheduled'] ) ? (bool) $body_params['is_scheduled'] : false;
		$datetime      = isset( $body_params['datetime'] ) ? $body_params['datetime'] : '';
		$campaign_data = isset( $body_params['campaign_data'] ) ? $body_params['campaign_data'] : array();
		$cost          = isset( $body_params['cost'] ) ? $body_params['cost'] : 0;
		$campaign_id   = isset( $body_params['campaign_id'] ) ? $body_params['campaign_id'] : '';

		// Validate campaign cost (Total sms count should be at least 1).
		if ( $cost < 1 ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Invalid cost. Campaign cost should be at least 1.',
					),
				),
				400
			);
		}

		// Validate datetime for scheduled campaign.
		if ( $is_scheduled && empty( $datetime ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'DateTime is required for scheduled campaigns',
					),
				),
				400
			);
		}

		// Validate datetime format and value (if schedule enabled).
		if ( $is_scheduled ) {
			// Validate datetime format.
			$parsed_date = strtotime( $datetime );
			if ( false === $parsed_date ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => 'Invalid datetime format',
						),
					),
					400
				);
			}

			// Check if is in the future datetime.
			if ( $parsed_date <= current_time( 'timestamp' ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => 'Scheduled datetime must be in the future',
						),
					),
					400
				);
			}
		}

		// Validate required form fields: campaign_name, list, sender, message.
		$required_fields = array( 'campaign_name', 'list', 'sender', 'message' );
		foreach ( $required_fields as $field ) {
			if ( empty( $campaign_data[ $field ] ) ) {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => $field . ' is required',
						),
					),
					400
				);
			}
		}

		$campaign_name = isset( $campaign_data['campaign_name'] ) ? sanitize_text_field( $campaign_data['campaign_name'] ) : '';
		$list_id       = isset( $campaign_data['list'] ) ? sanitize_text_field( $campaign_data['list'] ) : '';
		$sender        = isset( $campaign_data['sender'] ) ? sanitize_text_field( $campaign_data['sender'] ) : '';
		$message       = isset( $campaign_data['message'] ) ? $campaign_data['message'] : '';
		$link          = isset( $campaign_data['url'] ) ? esc_url_raw( $campaign_data['url'] ) : '';

		// Get access token for API request.
		$access_token = get_option( 'topsms_access_token' );

		if ( ! $access_token ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Access token not found',
					),
				),
				400
			);
		}

		// If enabled schedule, convert datetime to UTC.
		if ( $is_scheduled && ! empty( $datetime ) ) {
			// Create datetime in local timezone.
			$wp_timezone = wp_timezone_string();
			$dt          = new DateTime( $datetime, new DateTimeZone( $wp_timezone ) );
			$dt->setTimezone( new DateTimeZone( 'UTC' ) );
			$scheduled_datetime_utc = $dt->format( 'Y-m-d\TH:i:s\Z' );

			$scheduled_datetime_local = $datetime;
		} else {
			$scheduled_datetime_utc = gmdate( 'Y-m-d\TH:i:s\Z' );

			$scheduled_datetime_local = current_time( 'Y-m-d H:i:s' );
		}

		// Get list from transient based on selected list id.
		$lists = get_transient( 'topsms_contacts_lists' );
		if ( false === $lists ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'Transient key not found',
					),
				),
				404
			);
		}

		// Check if the selected list exists.
		$list = $lists[ $list_id ];
		if ( ! isset( $list ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => 'List not found',
					),
				),
				404
			);
		}

		// Get contacts data (phone numbers and shortcodes).
		$contacts_data = $this->get_contacts_data( $list, $link );
		$phone_numbers = $contacts_data['phone_numbers'];
		$shortcodes    = $contacts_data['shortcodes'];

		// Generate unique job name.
		// Campaign name concat with current timestamp.
		$job_name = $campaign_name . '_' . time();

		$action = $is_scheduled ? 'schedule' : 'instant';

		// Webhook url for campaign status.
		$website_url = get_home_url();
		$webhook_url = $website_url . '/wp-json/topsms/v2/bulksms/campaign-status';

		// Webhook token for campaign status.
		$webhook_token = hash_hmac( 'sha256', $job_name, SECURE_AUTH_KEY );

		// Send campaign.
		$url  = 'https://api.topsms.com.au/functions/v1/schedule';
		$body = array(
			'action'            => $action,
			'scheduledDateTime' => $scheduled_datetime_utc,
			'jobName'           => $job_name,
			'token'             => $access_token,
			'smsPayload'        => array(
				'phoneNumbers' => $phone_numbers,
				'message'      => $message,
				'shortcodes'   => $shortcodes,
				'link'         => $link,
				'sender'       => $sender,
				'cost'         => $cost,
			),
			'webhook_url'       => $webhook_url,
			'webhook_token'     => $webhook_token,
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
			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message' => $response->get_error_message(),
					),
				),
				500
			);
		}

		$campaign_uid = isset( $data['campaign_uid'] ) ? $data['campaign_uid'] : '';

		if ( isset( $data['success'] ) && $data['success'] ) {
			$status = $is_scheduled ? 'scheduled' : 'processing';

			// Save campaign to table.
			$campaign_data = array(
				'campaign_id'       => $campaign_id,
				'job_name'          => $job_name,
				'campaign_uid'      => $campaign_uid,
				'list'              => $list_id,
				'message'           => $message,
				'url'               => $link,
				'sender'            => $sender,
				'action'            => $action,
				'status'            => $status,
				'campaign_datetime' => $scheduled_datetime_local,
				'cost'              => $cost,
				'webhook_token'     => $webhook_token,
			);
			$this->save_campaigns_to_db( $campaign_data );

			$message = $is_scheduled ? 'SMS scheduled successfully on ' . $scheduled_datetime_local : 'SMS sent successfully';
			return new WP_REST_Response(
				array(
					'success' => true,
					'data'    => array(
						'message'      => $message,
						'jobName'      => $job_name,
						'campaign_uid' => isset( $data['campaign_uid'] ) ? $data['campaign_uid'] : '',
					),
				),
				200
			);
		} else {
			$status = 'scheduled';

			$message = '';
			if ( isset( $data['error'] ) ) {
				$message = $data['error'];
			} elseif ( isset( $data['message'] ) ) {
				$message = $data['message'];
			}

			// Save campaign to table.
			$campaign_data = array(
				'campaign_id'       => $campaign_id,
				'job_name'          => $job_name,
				'campaign_uid'      => $campaign_uid,
				'list'              => $list_id,
				'message'           => $message,
				'url'               => $link,
				'sender'            => $sender,
				'action'            => $action,
				'status'            => $status,
				'campaign_datetime' => $scheduled_datetime_local,
				'cost'              => $cost,
				'details'           => $message,
				'webhook_token'     => $webhook_token,
			);
			$this->save_campaigns_to_db( $campaign_data );

			return new WP_REST_Response(
				array(
					'success' => false,
					'data'    => array(
						'message'      => $message,
						'jobName'      => $job_name,
						'campaign_uid' => isset( $data['campaign_uid'] ) ? $data['campaign_uid'] : '',

					),
				),
				500
			);
		}
	}

	/**
	 * Resend a completed campaign instantly.
	 *
	 * @since    2.0.20
	 * @param string $list_id The contact list ID.
	 * @param string $sender The sender name.
	 * @param string $message The SMS message.
	 * @param string $link The URL link.
	 * @param string $campaign_name The campaign name.
	 * @param int    $cost The campaign cost.
	 * @return array Array with 'success' boolean, 'message' string, and optional 'job_name'.
	 */
	public function topsms_resend_campaign( $list_id, $sender, $message, $link, $campaign_name, $cost ) {

		error_log(123);
		// Get access token for API request.
		$access_token = get_option( 'topsms_access_token' );
		if ( ! $access_token ) {
			return array(
				'success' => false,
				'message' => 'Access token not found',
			);
		}

		// Validate cost.
		if ( $cost < 1 ) {
			return array(
				'success' => false,
				'message' => 'Invalid cost. Campaign cost should be at least 1.',
			);
		}

		// Get list from transient or fetch fresh.
		$lists = get_transient( 'topsms_contacts_lists' );
		if ( false === $lists ) {
			$lists = $this->get_contacts_lists();
		}

		// Check if the selected list exists.
		if ( ! isset( $lists[ $list_id ] ) ) {
			return array(
				'success' => false,
				'message' => 'List not found',
			);
		}

		$list = $lists[ $list_id ];

		// Get contacts data (phone numbers and shortcodes).
		$contacts_data = $this->get_contacts_data( $list, $link );
		if ( empty( $contacts_data ) ) {
			return array(
				'success' => false,
				'message' => 'No contacts found in list',
			);
		}

		$phone_numbers = $contacts_data['phone_numbers'];
		$shortcodes    = $contacts_data['shortcodes'];

		// Generate unique job name.
		$job_name = $campaign_name . '_' . time();

		// Current datetime in UTC (instant send).
		$scheduled_datetime_utc   = gmdate( 'Y-m-d\TH:i:s\Z' );
		$scheduled_datetime_local = current_time( 'Y-m-d H:i:s' );

		// Webhook url for campaign status.
		$website_url   = get_home_url();
		$webhook_url   = $website_url . '/wp-json/topsms/v2/bulksms/campaign-status';
		$webhook_token = hash_hmac( 'sha256', $job_name, SECURE_AUTH_KEY );

		// Send campaign instantly.
		$url  = 'https://api.topsms.com.au/functions/v1/schedule';
		$body = array(
			'action'            => 'instant',
			'scheduledDateTime' => $scheduled_datetime_utc,
			'jobName'           => $job_name,
			'token'             => $access_token,
			'smsPayload'        => array(
				'phoneNumbers' => $phone_numbers,
				'message'      => $message,
				'shortcodes'   => $shortcodes,
				'link'         => $link,
				'sender'       => $sender,
				'cost'         => $cost,
			),
			'webhook_url'       => $webhook_url,
			'webhook_token'     => $webhook_token,
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

		$response_body = wp_remote_retrieve_body( $response );
		$response_data = json_decode( $response_body, true );

		// Check for errors.
		if ( is_wp_error( $response ) ) {
			return array(
				'success' => false,
				'message' => $response->get_error_message(),
			);
		}

		$campaign_uid = isset( $response_data['campaign_uid'] ) ? $response_data['campaign_uid'] : '';

		if ( isset( $response_data['success'] ) && $response_data['success'] ) {
			// Save new campaign to database.
			$campaign_data = array(
				'campaign_id'       => '', // New campaign.
				'job_name'          => $job_name,
				'campaign_uid'      => $campaign_uid,
				'list'              => $list_id,
				'message'           => $message,
				'url'               => $link,
				'sender'            => $sender,
				'action'            => 'instant',
				'status'            => 'processing',
				'campaign_datetime' => $scheduled_datetime_local,
				'cost'              => $cost,
				'webhook_token'     => $webhook_token,
			);

			$this->save_campaigns_to_db( $campaign_data );

			return array(
				'success'  => true,
				'message'  => 'Campaign sent successfully',
				'job_name' => $job_name,
			);
		} else {
			$error_message = '';
			if ( isset( $response_data['error'] ) ) {
				$error_message = $response_data['error'];
			} elseif ( isset( $response_data['message'] ) ) {
				$error_message = $response_data['message'];
			}

			return array(
				'success' => false,
				'message' => $error_message ? $error_message : 'Failed to send campaign',
			);
		}
	}

	/**
	 * Get contacts data from list by extracting phone numbers and creating shortcodes.
	 *
	 * @since    2.0.0
	 * @param array  $list The contacts list.
	 * @param string $url Optional URL/link to replace the '[url]' shortcode.
	 * @return array Array of phone numbers and shortcodes.
	 */
	private function get_contacts_data( $list, $url ) {
		$contacts = $list['contacts'];

		// Check if there's contact in the list.
		// If no contacts, return empty array.
		if ( ! isset( $contacts ) || empty( $contacts ) ) {
			return array();
		}

		$phone_numbers = array();
		$shortcodes    = array();

		foreach ( $contacts as $contact ) {
			// Skip if phone number is missing.
			if ( empty( $contact['phone'] ) ) {
				continue;
			}

			// Add phone to phone numbers array.
			$phone           = sanitize_text_field( $contact['phone'] );
			$phone_numbers[] = $phone;

			// Build shortcode data for each contact.
			$shortcode    = array(
				'first_name'  => isset( $contact['first_name'] ) ? sanitize_text_field( $contact['first_name'] ) : '',
				'last_name'   => isset( $contact['last_name'] ) ? sanitize_text_field( $contact['last_name'] ) : '',
				'mobile'      => $phone,
				'city'        => isset( $contact['city'] ) ? sanitize_text_field( $contact['city'] ) : '',
				'state'       => isset( $contact['state'] ) ? sanitize_text_field( $contact['state'] ) : '',
				'postcode'    => isset( $contact['postcode'] ) ? sanitize_text_field( $contact['postcode'] ) : '',
				'orders'      => isset( $contact['order_count'] ) ? absint( $contact['order_count'] ) : 0,
				'total_spent' => isset( $contact['total_spent'] ) ? number_format( $contact['total_spent'], 2 ) : 0,
				// 'url'         => $url,
				'unsubscribe' => get_home_url() . '?phone=' . $phone,
			);
			$shortcodes[] = $shortcode;
		}

		// If no phone numbers, return empty array.
		if ( empty( $phone_numbers ) ) {
			return array();
		}

		return array(
			'phone_numbers' => $phone_numbers,
			'shortcodes'    => $shortcodes,
		);
	}

	/**
	 * Save the specified campaign data to database.
	 *
	 * @since    2.0.0
	 * @param array $campaign_data The contacts list.
	 * @return int|false The campaign ID if success; false if otherwise.
	 */
	private function save_campaigns_to_db( $campaign_data ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Convert id to integer if exists.
		$campaign_id = isset( $campaign_data['campaign_id'] ) ? intval( $campaign_data['campaign_id'] ) : 0;

		$data = array(
			'list'    => isset( $campaign_data['list'] ) ? $campaign_data['list'] : '',
			'message' => isset( $campaign_data['message'] ) ? $campaign_data['message'] : '',
			'url'     => isset( $campaign_data['url'] ) ? $campaign_data['url'] : '',
			'sender'  => isset( $campaign_data['sender'] ) ? $campaign_data['sender'] : '',
		);

		// Fields to update/insert.
		$db_data   = array(
			'job_name'          => $campaign_data['job_name'] ?? 'Untitled Campaign',
			'campaign_uid'      => $campaign_data['campaign_uid'] ?? '',
			'data'              => json_encode( $data ),
			'action'            => $campaign_data['action'] ?? '',
			'status'            => $campaign_data['status'] ?? 'draft',
			'campaign_datetime' => $campaign_data['campaign_datetime'] ?? null,
			'cost'              => $campaign_data['cost'] ?? 0,
			'details'           => $campaign_data['details'] ?? '',
			'webhook_token'     => $campaign_data['webhook_token'] ?? null,
		);
		$db_format = array(
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'%d',
			'%s',
			'%s',
		);

		// Check if campaign exists.
		$existing_campaign = null;
		if ( $campaign_id > 0 ) {
			// Get cache data if exists.
			$cache_key         = 'topsms_campaign_' . $campaign_id;
			$existing_campaign = wp_cache_get( $cache_key, 'topsms_campaigns' );

			// Do an sql query if not cached.
			if ( false === $existing_campaign ) {
				$existing_campaign = $wpdb->get_row(
					$wpdb->prepare( 'SELECT * FROM %1s WHERE id = %d', $table_name, $campaign_id )
				);
				// Cache for 1 hr.
				if ( $existing_campaign ) {
					wp_cache_set( $cache_key, $existing_campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
				}
			}
		}

		if ( $existing_campaign ) {
			// Update existing campaign.
			$result = $wpdb->update(
				$table_name,
				$db_data,
				array( 'id' => $existing_campaign->id ),
				$db_format,
				array( '%d' )
			);

			// Clear cache for table status counts.
			wp_cache_delete( 'topsms_campaigns_status_counts' );

			// Clear cache for the campaign.
			$cache_key = 'topsms_campaign_' . $existing_campaign->id;
			wp_cache_delete( $cache_key, 'topsms_campaigns' );

			if ( false !== $result ) {
				return $campaign_id;
			}
		} else {
			// Insert new campaign.
			$result = $wpdb->insert(
				$table_name,
				$db_data,
				$db_format
			);

			// Clear cache for table status counts.
			wp_cache_delete( 'topsms_campaigns_status_counts' );

			if ( $result ) {
				return $wpdb->insert_id;
			} else {
				return $wpdb->insert_id;
			}
		}
		return false;
	}

	/**
	 * Save draft campaign to database.
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_save_campaign_as_draft( WP_REST_Request $request ) {
		// Get payload from the request.
		$body_params = $request->get_json_params();

		// Check if fields exist.
		$is_scheduled  = isset( $body_params['is_scheduled'] ) ? (bool) $body_params['is_scheduled'] : false;
		$datetime      = isset( $body_params['datetime'] ) ? $body_params['datetime'] : '';
		$campaign_id   = isset( $body_params['campaign_id'] ) ? intval( $body_params['campaign_id'] ) : 0;
		$campaign_data = isset( $body_params['campaign_data'] ) ? $body_params['campaign_data'] : array();

		// Campaign datetime: if schedule enabled, save datetime; otherwise, leave null.
		$campaign_datetime = '';
		if ( $is_scheduled && ! empty( $datetime ) ) {
			$campaign_datetime = $datetime;
		}

		$action = $is_scheduled ? 'schedule' : 'instant';

		if ( $campaign_data ) {
			// Extract campaign data: campaign name, list, sender, message and url if exist.
			$campaign_name = isset( $campaign_data['campaign_name'] ) ? sanitize_text_field( $campaign_data['campaign_name'] ) : '';
			$list          = isset( $campaign_data['list'] ) ? sanitize_text_field( $campaign_data['list'] ) : '';
			$sender        = isset( $campaign_data['sender'] ) ? sanitize_text_field( $campaign_data['sender'] ) : '';
			$message       = isset( $campaign_data['message'] ) ? $campaign_data['message'] : '';
			$url           = isset( $campaign_data['url'] ) ? esc_url_raw( $campaign_data['url'] ) : '';
		}

		$data            = array();
		$data['list']    = ! empty( $list ) ? $list : '';
		$data['sender']  = ! empty( $sender ) ? $sender : '';
		$data['message'] = ! empty( $message ) ? $message : '';
		$data['url']     = ! empty( $url ) ? $url : '';

		$db_data = array(
			'job_name' => ! empty( $campaign_name ) ? $campaign_name : 'Untitled Campaign',
			'data'     => json_encode( $data ),
			'action'   => $action,
			'status'   => 'draft',
		);

		$db_format = array(
			'%s',
			'%s',
			'%s',
			'%s',
		);

		// Add campaign_datetime only not empty.
		if ( ! empty( $campaign_datetime ) ) {
			$db_data['campaign_datetime'] = $campaign_datetime;
			$db_format[]                  = '%s';
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Check if campaign exists by id.
		$existing_campaign = null;
		if ( $campaign_id > 0 ) {
			// Get cache data if exists.
			$cache_key         = 'topsms_campaign_' . $campaign_id;
			$existing_campaign = wp_cache_get( $cache_key, 'topsms_campaigns' );

			// Do an sql query if not cached.
			if ( false === $existing_campaign ) {
				$existing_campaign = $wpdb->get_row(
					$wpdb->prepare(
						'SELECT * FROM %1s WHERE id = %d',
						$table_name,
						$campaign_id
					)
				);
				// Cache for 1 hr.
				if ( $existing_campaign ) {
					wp_cache_set( $cache_key, $existing_campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
				}
			}
		}

		if ( $existing_campaign ) {
			// Update campaign if exists.
			$result = $wpdb->update(
				$table_name,
				$db_data,
				array( 'id' => $existing_campaign->id ),
				$db_format,
				array( '%d' )
			);

			// Clear cache for table status counts.
			wp_cache_delete( 'topsms_campaigns_status_counts' );

			// Clear cache for the campaign.
			$cache_key = 'topsms_campaign_' . $existing_campaign->id;
			wp_cache_delete( $cache_key, 'topsms_campaigns' );

			if ( false !== $result ) {
				return new WP_REST_Response(
					array(
						'success' => true,
						'data'    => array(
							'message'     => 'Campaign draft saved successfully',
							'campaign_id' => $existing_campaign->id,
						),
					),
					200
				);
			} else {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => 'Failed to save campaign draft',
						),
					),
					500
				);
			}
		} else {
			// Insert campaign if doesn't exist.
			$result = $wpdb->insert(
				$table_name,
				$db_data,
				$db_format
			);

			// Clear cache for table status counts.
			wp_cache_delete( 'topsms_campaigns_status_counts' );

			if ( $result ) {
				return new WP_REST_Response(
					array(
						'success' => true,
						'data'    => array(
							'message'     => 'Campaign draft saved successfully',
							'campaign_id' => $wpdb->insert_id,
						),
					),
					200
				);
			} else {
				return new WP_REST_Response(
					array(
						'success' => false,
						'data'    => array(
							'message' => 'Failed to save campaign draft',
						),
					),
					500
				);
			}
		}
	}

	/**
	 * Update campaign status from webhook
	 *
	 * @since    2.0.0
	 * @param WP_REST_Request $request The request object.
	 * @return WP_REST_Response The response.
	 */
	public function topsms_scheduled_campaign_status( WP_REST_Request $request ) {
		// Get token from the header.
		$auth_header = $request->get_header( 'authorization' );
		if ( empty( $auth_header ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Authorization header missing',
				),
				401
			);
		}

		// Extract token from the bearer token.
		$webhook_token = str_replace( 'Bearer ', '', $auth_header );

		// Get campaign uid and status from the request.
		$body_params = $request->get_json_params();
		if ( ! isset( $body_params['campaign_uid'] ) || ! isset( $body_params['status'] ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Missing required parameters: campaign_uid and status',
				),
				400
			);
		}

		$campaign_uid = sanitize_text_field( $body_params['campaign_uid'] );
		$status       = sanitize_text_field( $body_params['status'] );

		// Get message if provided.
		$message = isset( $body_params['message'] ) ? sanitize_text_field( $body_params['message'] ) : null;

		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Get cache data if exists.
		$cache_key_uid = 'topsms_campaign_uid_' . $campaign_uid;
		$campaign      = wp_cache_get( $cache_key_uid, 'topsms_campaigns' );

		// Do an sql query if not cached.
		if ( false === $campaign ) {
			// Search for campaign by campaign uid.
			$query = $wpdb->prepare(
				'SELECT * FROM %1s WHERE campaign_uid = %s',
				$table_name,
				$campaign_uid
			);

			$campaign = $wpdb->get_row( $query );

			// Cache by both uid and id for 1 hr.
			if ( $campaign ) {
				wp_cache_set( $cache_key_uid, $campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
				$cache_key_id = 'topsms_campaign_' . $campaign->id;
				wp_cache_set( $cache_key_id, $campaign, 'topsms_campaigns', HOUR_IN_SECONDS );
			}
		}

		// Campaign not found.
		if ( ! $campaign ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Campaign not found with uid: ' . $campaign_uid,
				),
				404
			);
		}

		// Verify the webhook token matches the stored token for this campaign.
		if ( ! hash_equals( $campaign->webhook_token, $webhook_token ) ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Invalid webhook token',
				),
				403
			);
		}

		// Update status.
		// If status is success, then update status to completed;
		// If status is failed, then update status to failed.
		$status_ = strtolower( $status );

		if ( 'success' === $status_ ) {
			$status = 'completed';
		} else {
			$status = 'failed';
		}

		// Update status for the campaign.
		$update_data   = array(
			'status' => $status,
		);
		$update_format = array( '%s' );

		// Add any error message to details (if givem).
		if ( null !== $message ) {
			$update_data['details'] = $message;
			$update_format[]        = '%s';
		}

		$result = $wpdb->update(
			$table_name,
			$update_data,
			array( 'campaign_uid' => $campaign_uid ),
			$update_format,
			array( '%s' )
		);

		// Clear cache for table status counts.
		wp_cache_delete( 'topsms_campaigns_status_counts' );

		// Clear cache for the campaign (by both id and uid).
		$cache_key_id = 'topsms_campaign_' . $campaign->id;
		wp_cache_delete( $cache_key_id, 'topsms_campaigns' );
		wp_cache_delete( $cache_key_uid, 'topsms_campaigns' );

		if ( false === $result ) {
			return new WP_REST_Response(
				array(
					'success' => false,
					'message' => 'Failed to update campaign status',
				),
				500
			);
		}

		return new WP_REST_Response(
			array(
				'success'      => true,
				'message'      => 'Campaign status updated successfully',
				'campaign_uid' => $campaign_uid,
				'status'       => $status,
			),
			200
		);
	}

	/**
	 * Filter contacts based on phone number and status.
	 * Ensure no contacts with duplicate phone number,
	 * and priotise contacts with status 'yes' over duplicates.
	 *
	 * @since 2.0.9
	 * @param array $contacts Array of contact data.
	 * @return array Filtered and deduplicated contacts.
	 */
	private function filter_contacts( $contacts ) {
		$existed_phones    = array();
		$filtered_contacts = array();

		foreach ( $contacts as $contact ) {
			// Skip contacts without valid phone.
			if ( empty( $contact['phone'] ) || trim( $contact['phone'] ) === '' ) {
				continue;
			}

			// Normalise phone number.
			$normalised_phone = preg_replace( '/[^0-9]/', '', $contact['phone'] );
			// Skip if normalised phone is empty.
			if ( empty( $normalised_phone ) ) {
				continue;
			}

			$contact_status = $contact['status'] ?? '';
			// Check if phone exists before.
			if ( isset( $existed_phones[ $normalised_phone ] ) ) {
				// If phone exists, replace only if the current contact is subscribed and the previously stored one doesn't.
				// Otherwise keep the existing one and skip this duplicate.
				$stored_index  = $existed_phones[ $normalised_phone ];
				$stored_status = $filtered_contacts[ $stored_index ]['status'] ? $filtered_contacts[ $stored_index ]['status'] : '';
				if ( 'yes' === $contact_status && 'yes' !== $stored_status ) {
					// Replace the contact.
					$filtered_contacts[ $stored_index ] = $contact;
				}
				continue;
			}

			// Only add contacts with 'yes' status.
			if ( 'yes' === $contact_status ) {
				$index                               = count( $filtered_contacts );
				$existed_phones[ $normalised_phone ] = $index;
				$filtered_contacts[]                 = $contact;
			}
		}
		return $filtered_contacts;
	}
}
