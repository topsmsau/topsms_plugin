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
        $this->load_dependencies();

        // Check if we're on the setup page
        // add_action('current_screen', function($screen) {
        //      error_log('Current screen base: ' . (is_object($screen) ? $screen->base : 'not an object'));
        //     if (is_object($screen) && $screen->base === 'topsms_page_topsms-setup') {
        //         // Hide admin menu and header
        //         add_filter('admin_head', array($this, 'hide_admin_ui'));
        //     }
        // });

        // Hide the wp admin header and sidebar menu if on setup page
        add_action('admin_init', function() {
            if (isset($_GET['page']) && $_GET['page'] === 'topsms-setup') {
                // Hide admin menu and header
                add_action('admin_head', array($this, 'hide_admin_ui'));
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

        wp_localize_script( 'wp-api', 'wpApiSettings', array( 'root' => esc_url_raw( rest_url() ), 'nonce' => wp_create_nonce( 'wp_rest' ) ) );
        wp_enqueue_script('wp-api');

	}

    private function load_dependencies() {
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-topsms-rest-api-admin.php';
        $this->rest_api = new Topsms_Rest_Api_Admin($this->plugin_name, $this->version);
    }

    public function hide_admin_ui() {
        echo '<style>
            #wpcontent { margin-left: 0 !important; }
            #adminmenumain, #wpadminbar, #wpfooter { display: none !important; }
            #topsms-admin-app { height: 100vh; }
        </style>';
    }

    public function topsms_register_routes() {
        // Sending otp
        register_rest_route('topsms/v1', '/send-otp', array(
            'methods'  => 'POST',
            'callback' => array($this->rest_api, 'topsms_send_otp'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Verifying otp
        register_rest_route('topsms/v1', '/verify-otp', array(
            'methods'  => 'POST',
            'callback' => array($this->rest_api, 'topsms_verify_otp'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Fetching automations status settings
        register_rest_route('topsms/v1', '/automations/status/(?P<status_key>[a-zA-Z0-9_-]+)', array(
            'methods'  => 'GET',
            'callback' => array($this->rest_api, 'topsms_get_automations_status_settings'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Saving automations status enabled setting
        register_rest_route('topsms/v1', '/automations/status/save', array(
            'methods'  => 'POST',
            'callback' => array($this->rest_api, 'topsms_save_automations_status_enabled'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Saving automations status template
        register_rest_route('topsms/v1', '/automations/status/save-template', array(
            'methods'  => 'POST',
            'callback' => array($this->rest_api, 'topsms_save_automations_status_template'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));

        // Fetching settings (general)
        register_rest_route('topsms/v1', '/settings/(?P<key>[a-zA-Z0-9_-]+)', array(
            'methods'  => 'GET',
            'callback' => array($this->rest_api, 'topsms_get_settings'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
));

        // Saving settings (general)
        register_rest_route('topsms/v1', '/settings/save', array(
            'methods'  => 'POST',
            'callback' => array($this->rest_api, 'topsms_save_settings'),
            'permission_callback' => function () {
                return current_user_can('manage_options');
            },
        ));
    }

    /**
     * Add an admin menu for Topsms
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        $is_connected = $this->check_topsms_connection();
        // $is_connected = false;

        add_menu_page(
            __('TopSMS', 'topsms'),
            __('TopSMS', 'topsms'),
            'manage_options',
            $is_connected ? 'topsms' : 'topsms-setup', 
            $is_connected ? array($this, 'display_automations_page') : array($this, 'display_setup_page'),
            'dashicons-chat',
            55
        );

        // add_submenu_page(
        //     'topsms',
        //     __( 'Setup', 'topsms' ),
        //     __( 'Setup', 'topsms' ),
        //     'manage_options',
        //     'topsms-setup',
        //     array( $this, 'display_setup_page' )
        // );

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

        // // If already connected, redirect to automations page
        // if ($is_connected) {
        //     wp_redirect(admin_url('admin.php?page=topsms-automations'));
        //     exit;
        // }
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'isConnected' => $is_connected,
            // 'isConnected' => 'false',
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-setup" class="topsms-app"></div>';
        echo '</div>';
    }


    public function display_automations_page() {
        // Check if connected, if not, redirect to the setup page
        $is_connected = $this->check_topsms_connection();
        if (!$is_connected) {
            wp_redirect(admin_url('admin.php?page=topsms-setup'));
            exit;
        }
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-automations" class="topsms-app"></div>';
        echo '</div>';
    }


    public function display_settings_page() {
        // Check if connected, if not, redirect to the setup page
        $is_connected = $this->check_topsms_connection();
        if (!$is_connected) {
            wp_redirect(admin_url('admin.php?page=topsms-setup'));
            exit;
        }
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
            'pluginUrl' => TOPSMS_MANAGER_PLUGIN_URL
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<div id="topsms-admin-settings" class="topsms-app"></div>';
        echo '</div>';
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
