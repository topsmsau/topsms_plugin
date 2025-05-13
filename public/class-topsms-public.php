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

class Topsms_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Adjustment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Adjustment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/topsms-public.css', array(), time(), 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Price_Adjustment_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Price_Adjustment_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/topsms-public.js', array( 'jquery' ), time(), false );

	}



    // Add custom checkbox to checkout page after the terms and conditions to get customer consent
    public function add_topsms_customer_consent_checkout_checkbox( ){
        // Check if the customer consent is enabled
        $consent_enabled = get_option('topsms_settings_customer_consent', true);
        
        // Only show the checkbox if this setting is enabled; Return if disabled
        if (!$consent_enabled) {
            return; 
        }

        $id = get_current_user_id();
        $is_subscribed = get_user_meta($id, 'topsms_customer_consent', 'yes');
        if($is_subscribed == 'yes') {
            $is_subscribed = true;
        }
        else if($is_subscribed == 'no') {
            $is_subscribed = false;
        }
        else {
            $is_subscribed = true;
        }

        echo '<div id="topsms-customer-consent">';
            woocommerce_form_field( 'topsms_customer_consent', array(
                'type'      => 'checkbox',
                'class'     => array('input-checkbox'),
                'label'     => __('Receive SMS Notifications', 'topsms'),
            ),  $is_subscribed );
        echo '</div>';
    }

    // Save checkbox value to user and order meta
    public function save_topsms_customer_consent_checkout_checkbox($order_id) {
        // Check if the customer consent is enabled
        $consent_enabled = get_option('topsms_settings_customer_consent', true);
        
        // Only show the checkbox if this setting is enabled; Return if disabled
        if (!$consent_enabled) {
            return; 
        }

        if (isset($_POST['topsms_customer_consent']) && !empty($_POST['topsms_customer_consent'])) {
            // Update value to order data
            update_post_meta($order_id, 'topsms_customer_consent', 'yes');

            // Update value to user data
            $user_id = get_current_user_id();
		    update_user_meta($user_id, 'topsms_customer_consent', 'yes');
        } else {
            // Update value to order data
            update_post_meta($order_id, 'topsms_customer_consent', 'no');

            // Update value to user data
            $user_id = get_current_user_id();
		    update_user_meta($user_id, 'topsms_customer_consent', 'no');
        }
    }

    public function add_topsms_surcharge_to_cart($cart) {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }
        error_log(print_r($_POST, true));
        
        // Check if the surcharge is enabled in settings
        $surcharge_enabled = get_option('topsms_settings_sms_surcharge');
        
        // Check if customer has consented (ticked the checkbox)
        $customer_consented = false;
        
        // Check if it's in the POST data (during checkout updates)
        if (isset($_POST['topsms_customer_consent'])) {
            $customer_consented = true;
        } 
        // Check in parsed post_data for AJAX requests
        elseif (isset($_POST['post_data'])) {
            parse_str($_POST['post_data'], $parsed_post_data);
            
            // Check params in the post_data
            if (strpos($_POST['post_data'], 'topsms_customer_consent') !== false) {
                $customer_consented = true;
            } else {
                $customer_consented = false;
                
                // Also clear session
                if (WC()->session) {
                    WC()->session->set('topsms_customer_consent', false);
                }
            }
        }

        // Check the session as a fallback
        elseif (WC()->session && WC()->session->get('topsms_customer_consent')) {
            $customer_consented = true;
        }

        // Only apply if surcharge enabled and if customer consented/ticked the consent checkbox
        $should_apply_surcharge = $surcharge_enabled === 'yes' && $customer_consented;
        
        // Check if the SMS fee already exists or unticked customer consent, remove if so
        foreach ($cart->get_fees() as $fee_key => $fee) {
            if ($fee->name === 'SMS Surcharge') {
                if (!$should_apply_surcharge) {
                    // Remove the fee already exists
                    $cart->remove_fee($fee_key);
                }
            }
        }
        
        // Add the surcharge 
        if ($should_apply_surcharge) {
            // Get the surcharge amount, convert to float if string
            $surcharge_amount = get_option('topsms_settings_sms_surcharge_amount');
            if (is_string($surcharge_amount)) {
                $surcharge_amount = floatval($surcharge_amount);
            }
            
            // Add the surcharge to cart
            if ($surcharge_amount > 0) {
                $cart->add_fee(__('SMS Surcharge', 'topsms'), $surcharge_amount, true);
            }
        }
    }

    public function topsms_update_customer_consent() {
        if (isset($_POST['consent'])) {
            $consent = $_POST['consent'] === '1' || $_POST['consent'] === 1;
            WC()->session->set('topsms_customer_consent', $consent);
        }
        wp_die();
    }   
}
