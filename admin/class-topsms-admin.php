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

        // Setup submenu
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
    }

    /**
     * Render the setup page.
     *
     * @since    1.0.0
     */
    public function display_setup_page() {
        $current_step = $this->get_current_step();
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'currentStep' => $current_step,
            'setupSteps' => $this->get_setup_steps(),
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-app"></div>';
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
     * Get the setup steps.
     *
     * @since    1.0.0
     * @return   array    Setup steps.
     */
    public function get_setup_steps() {
        return array(
            'registration' => array(
                'name' => __( 'Registration', 'topsms' ),
                'description' => __( 'Register your TopSMS account', 'topsms' ),
            ),
            'verification' => array(
                'name' => __( 'Verification', 'topsms' ),
                'description' => __( 'Verify your phone number', 'topsms' ),
            ),
            'welcome' => array(
                'name' => __( 'Welcome', 'topsms' ),
                'description' => __( 'You\'re all set!', 'topsms' ),
            ),
        );
    }

    /**
     * Get the current setup step.
     *
     * @since    1.0.0
     * @return   string    Current step.
     */
    public function get_current_step() {
        $current_step = get_option( 'topsms_setup_step', 'registration' );
        return $current_step;
    }


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
        
        if (is_wp_error($response)) {
            wp_send_json_error(['message' => $response->get_error_message()]);
            return;
        }
        
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);
        error_log("response data" . print_r($data, true));
        
        if (wp_remote_retrieve_response_code($response) !== 200) {
            wp_send_json_error(['message' => isset($data['message']) ? $data['message'] : 'Failed to send OTP']);
            return;
        }
        
        wp_send_json_success($data);
    }
}
