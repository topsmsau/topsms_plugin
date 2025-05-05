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

        // Register block editor styles
        wp_register_style(
            'topsms-blocks-editor-style',
            plugin_dir_url( __FILE__ ) . 'css/topsms-admin.css',
            array( 'wp-edit-blocks' ),
            $this->version
        );

        // Register Tailwind CSS for the editor
        wp_register_style(
            'topsms-tailwind-style',
            plugin_dir_url( __FILE__ ) . 'js/blocks/tailwind.css',
            array(),
            $this->version
        );

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

        // Register the block editor script
        wp_register_script(
            'topsms-blocks-editor',
            plugin_dir_url( __FILE__ ) . 'js/blocks/index.js',
            array( 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n', 'wp-api-fetch' ),
            $this->version
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
    }

    /**
     * Render the setup page.
     *
     * @since    1.0.0
     */
    public function display_setup_page() {
        $current_step = $this->get_current_step();
        
        // Register and enqueue scripts/styles directly here
        wp_register_script(
            'topsms-admin-app',
            plugin_dir_url(__FILE__) . 'js/admin-app.js',
            array('wp-element', 'wp-components'),
            time(),
            true
        );
        
        wp_register_style(
            'topsms-tailwind-style',
            plugin_dir_url(dirname(dirname(__FILE__))) . 'src/css/tailwind.css',
            array(),
            time()
        );
        
        wp_enqueue_script('topsms-admin-app');
        wp_enqueue_style('topsms-tailwind-style');
        
        // Pass data to JavaScript
        wp_localize_script('topsms-admin-app', 'topsmsData', array(
            'currentStep' => $current_step,
            'setupSteps' => $this->get_setup_steps(),
            'restUrl' => esc_url_raw(rest_url()),
            'nonce' => wp_create_nonce('wp_rest'),
        ));
        
        // Container for React app
        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('TopSMS Setup', 'topsms') . '</h1>';
        echo '<div id="topsms-admin-app"></div>';
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
}
