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
	public function capture_utm_parameters() {
        // Check if url has Topsms utm parameters.
        if (isset($_GET['utm_source']) && 'topsms' === strtolower($_GET['utm_source'])) {
            
            // Capture utm_campaign (job name/campaign name) and utm_id (campaign uid).
            $cookie_data = array(
                'utm_campaign' => sanitize_text_field($_GET['utm_campaign'] ?? ''),
                'utm_id' => sanitize_text_field($_GET['utm_id'] ?? ''),
                'captured_at' => current_time('mysql'),
            );
            
            // If user is logged in, store the user id.
            if (is_user_logged_in()) {
                $cookie_data['user_id'] = get_current_user_id();
            }
            
            // Save to user session cookie.
            $this->set_utm_cookie($cookie_data);
        }
    }

    /**
     * Set UTM data to user session cookie.
     *
     * @since    2.0.21
     * @param array $utm_data UTM parameters
     */
    private function set_utm_cookie($utm_data) {
        $cookie_value = json_encode($utm_data);
    
        // Set cookie expiration duration.
        if (is_user_logged_in()) {
            // Logged-in users: Set expiration to 1 year (will be cleared when user is logged out).
            $expiry = time() + YEAR_IN_SECONDS;
        } else {
            // Guests: Session cookie (expires when browser closes).
            $expiry = 0;
        }
        
        // Set cookie.
        setcookie(
            $this->cookie_name,
            $cookie_value,
            $expiry,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
        
        // Set in $_COOKIE for immediate availabiltiy.
        $_COOKIE[$this->cookie_name] = $cookie_value;
    }

    /**
     * Clear UTM session cookie.
     *
     * @since    2.0.21
     */
    public function clear_utm_cookie() {
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
    private function get_utm_cookie() {
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
                return $utm_data;
            }
        }

        // Return null if topsms utm cookie is not found.
        return null;
    }
}