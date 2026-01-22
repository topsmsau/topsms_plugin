<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/public
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Topsms
 * @subpackage Topsms/public
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Utm_Tracker_Public {

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
	 * Cookie name.
	 *
	 * @since    2.0.21
	 * @access   private
	 * @var      string    $cookie_name    Cookie name.
	 */
	private $cookie_name;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
        $this->cookie_name = 'topsms_utm_data';
	}

    /**
	 * Track TopSMS UTM parameters and save to user session cookie when someone visits the website.
	 *
	 * @since  2.0.21
	 */
	public function topsms_capture_utm_parameters() {
        // Verify nonce.
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'topsmsPublic')) {
            wp_send_json_error(array('message' => 'Invalid nonce'));
            return;
        }

        // Check if utm data exists.
        if (!isset($_POST['utm_data']) || !is_array($_POST['utm_data'])) {
            wp_send_json_error(array('message' => 'No UTM data provided'));
            return;
        }

        $utm_data = $_POST['utm_data'];

        // Capture utm_campaign (job name/campaign name) and utm_id (campaign uid).
        $cookie_data = array(
            'utm_campaign' => sanitize_text_field($utm_data['utm_campaign'] ?? ''),
            'utm_id' => sanitize_text_field($utm_data['utm_id'] ?? ''),
            'captured_at' => current_time('mysql'),
        );
        
        // If user is logged in, store the user id.
        if (is_user_logged_in()) {
            $cookie_data['user_id'] = get_current_user_id();
        }
        
        // Save to user session cookie.
        $result = $this->topsms_set_utm_cookie($cookie_data);
    
        if ($result) {
            wp_send_json_success(array(
                'message' => 'UTM data saved successfully',
                'data' => $cookie_data
            ));
        } else {
            wp_send_json_error(array('message' => 'Failed to set cookie'));
        }
    }

    /**
     * Set UTM data to user session cookie.
     *
     * @since    2.0.21
     * @param array $utm_data UTM parameters
     */
    private function topsms_set_utm_cookie($utm_data) {
        $cookie_value = json_encode($utm_data);
    
        // Set cookie expiration duration.
        if (is_user_logged_in()) {
            // Logged-in users: Set expiration to 1 year (will be cleared when user is logged out).
            $expiry = time() + YEAR_IN_SECONDS;
        } else {
            // Guests: 24 hours
            $expiry = time() + DAY_IN_SECONDS;
        }
        
        // Set cookie.
        $result = setcookie(
            $this->cookie_name,
            $cookie_value,
            $expiry,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        
        // Set in $_COOKIE for immediate availability.
        if ($result) {
            $_COOKIE[$this->cookie_name] = $cookie_value;
        } 
        
        return $result;
    }

    /**
     * Clear UTM session cookie.
     *
     * @since    2.0.21
     */
    public function topsms_clear_utm_cookie() {
        // Clear cookie.
        setcookie(
            $this->cookie_name,
            '',
            time() - 3600, // Set expiration to 1hr in the past.
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    
        // remove from $_COOKIE array.
        unset($_COOKIE[$this->cookie_name]);
    }

    /**
     * Get UTM data from user session cookie.
     * 
     * @return array|null UTM data or null if not found.
     */
    private function topsms_get_utm_cookie() {
        // If topsms utm cookie is found.
        if (isset($_COOKIE[$this->cookie_name])) {
            // Get utm data from session cookie.
            $utm_data = json_decode(stripslashes($_COOKIE[$this->cookie_name]), true);
            
            if (is_array($utm_data)) {
                // If user is logged in, check if the cookie belongs to them.
                // Exit if the current user id and the stored user id (in the cookie) don't match.
                if (is_user_logged_in()) {
                    $current_user_id = get_current_user_id();
                    if (isset($utm_data['user_id']) && $utm_data['user_id'] != $current_user_id) {
                        return null;
                    }
                }

                // Return utm data if current user is a guest/logged-in and the user id match.
                return $utm_data;
            }
        }

        // Return null if topsms utm cookie is not found.
        return null;
    }

    public function topsms_save_utm_to_order($order_id) {
        // Get utm data from cookie.
        $utm_data = $this->topsms_get_utm_cookie();
        
        // Return if no utm found.
        if (!$utm_data) {
            return; 
        }

        // Return if no utm id found.
        if (!isset($utm_data['utm_id'])) {
            return;
        }
        
        // Get order by order id.
        $order = wc_get_order($order_id);
        if (!$order) {
            return;
        }
        
        // Save individual utm id (campaign id) for easy access (when reporting).
        $order->update_meta_data('_topsms_utm_id', $utm_data['utm_id']);
        $order->update_meta_data('_topsms_utm_data', json_encode($utm_data));

        $order->save();
    }
}