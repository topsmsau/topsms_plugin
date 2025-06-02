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
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
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

	/**
	 * Add SMS consent checkbox to the WooCommerce checkout page.
	 *
	 * @since    1.0.0
	 */
	public function topsms_add_customer_consent_checkout_checkbox() {
		// Check if the customer consent is enabled.
		$consent_enabled = get_option( 'topsms_settings_customer_consent', 'yes' );

		// Only show the checkbox if this setting is enabled; Return if disabled.
		if ( 'no' === $consent_enabled ) {
			return;
		}

		$id            = get_current_user_id();
		$is_subscribed = get_user_meta( $id, 'topsms_customer_consent', 'yes' );
		if ( 'yes' === $is_subscribed ) {
			$is_subscribed = true;
		} elseif ( 'no' === $is_subscribed ) {
			$is_subscribed = false;
		} else {
			$is_subscribed = true;
		}

		printf( '<div id="topsms-customer-consent">' );
			woocommerce_form_field(
				'topsms_customer_consent',
				array(
					'type'  => 'checkbox',
					'class' => array( 'input-checkbox' ),
					'label' => __( 'Receive SMS Notifications', 'topsms' ),
				),
				$is_subscribed
			);

			wp_nonce_field( 'topsms_consent_action', 'topsms_consent_nonce' );
		printf( '</div>' );
	}

	/**
	 * Save customer SMS consent from the checkout to order and user meta.
	 *
	 * @since    1.0.0
	 * @param    int $order_id    The ID of the order being processed.
	 */
	public function topsms_save_customer_consent_checkout_checkbox( $order_id ) {
		// Check if the customer consent is enabled.
		$consent_enabled = get_option( 'topsms_settings_customer_consent', 'yes' );

		// Only show the checkbox if this setting is enabled; Return if disabled.
		if ( ! $consent_enabled ) {
			return;
		}

		// Verify nonce for security.
		if ( ! isset( $_POST['topsms_consent_nonce'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['topsms_consent_nonce'] ) ), 'topsms_consent_action' ) ) {
			return;
		}

		// Get the order object.
		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		if ( isset( $_POST['topsms_customer_consent'] ) && ! empty( $_POST['topsms_customer_consent'] ) ) {
			// Update value to order data.
			$order->update_meta_data( 'topsms_customer_consent', 'yes' );
			$order->save();

			// Update value to user data.
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'topsms_customer_consent', 'yes' );
		} else {
			// Update value to order data.
			$order->update_meta_data( 'topsms_customer_consent', 'no' );
			$order->save();

			// Update value to user data.
			$user_id = get_current_user_id();
			update_user_meta( $user_id, 'topsms_customer_consent', 'no' );
		}
	}

	/**
	 * Add SMS surcharge to the cart if customer has consented
	 * and the SMS surcharge option in the general settings is enabled.
	 *
	 * @since    1.0.0
	 * @param    WC_Cart $cart    The WooCommerce cart object.
	 */
	public function topsms_add_surcharge_to_cart( $cart ) {
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Check if the surcharge is enabled in settings.
		$surcharge_enabled = get_option( 'topsms_settings_sms_surcharge' );

		// Check if customer has consented (ticked the checkbox).
		$customer_consented = false;

		// Check if it's in the POST data (during checkout updates).
		if ( isset( $_POST['topsms_customer_consent'] ) ) {
			// Verify nonce if available during checkout.
			if ( ! isset( $_POST['topsms_consent_nonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['topsms_consent_nonce'] ) ), 'topsms_consent_action' ) ) {
				$customer_consented = false;
			} else {
				$customer_consented = true;
			}
		} elseif ( isset( $_POST['post_data'] ) ) {
			// Check in parsed post_data for AJAX requests.
			// Properly unslash and sanitize post_data.
			$post_data = sanitize_text_field( wp_unslash( $_POST['post_data'] ) );
			parse_str( $post_data, $parsed_post_data );

			// Check if nonce exists and is valid in parsed data.
			$valid_nonce = isset( $parsed_post_data['topsms_consent_nonce'] ) &&
				wp_verify_nonce( sanitize_text_field( $parsed_post_data['topsms_consent_nonce'] ), 'topsms_consent_action' );

			// Check params in the post_data.
			if ( isset( $parsed_post_data['topsms_customer_consent'] ) && $valid_nonce ) {
				$customer_consented = true;
			} else {
				$customer_consented = false;

				// Also clear session.
				if ( WC()->session ) {
					WC()->session->set( 'topsms_customer_consent', false );
				}
			}
		} elseif ( WC()->session && WC()->session->get( 'topsms_customer_consent' ) ) {
			// Check the session as a fallback.
			$customer_consented = true;
		}

		// Only apply if surcharge enabled and if customer consented/ticked the consent checkbox.
		$should_apply_surcharge = 'yes' === $surcharge_enabled && $customer_consented;

		// Check if the SMS fee already exists or unticked customer consent, remove if so.
		foreach ( $cart->get_fees() as $fee_key => $fee ) {
			if ( 'SMS Surcharge' === $fee->name ) {
				if ( ! $should_apply_surcharge ) {
					// Remove the fee already exists.
					$cart->remove_fee( $fee_key );
				}
			}
		}

		// Add the surcharge.
		if ( $should_apply_surcharge ) {
			// Get the surcharge amount, convert to float if string.
			$surcharge_amount = get_option( 'topsms_settings_sms_surcharge_amount' );
			if ( is_string( $surcharge_amount ) ) {
				$surcharge_amount = floatval( $surcharge_amount );
			}

			// Add the surcharge to cart.
			if ( $surcharge_amount > 0 ) {
				$cart->add_fee( __( 'SMS Surcharge', 'topsms' ), $surcharge_amount, true );
			}
		}
	}

	/**
	 * Update customer's SMS notification consent status in the WooCommerce session.
	 *
	 * @since    1.0.0
	 */
	public function topsms_update_customer_consent() {
		// Verify nonce for security.
		if ( ! isset( $_POST['security'] ) ||
			! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['security'] ) ), 'topsms_consent_action' ) ) {
			wp_send_json_error( 'Security check failed' );
			wp_die();
		}

		if ( isset( $_POST['consent'] ) ) {
			// Properly sanitize and unslash the consent value.
			$consent_value = sanitize_text_field( wp_unslash( $_POST['consent'] ) );
			$consent       = ( '1' === $consent_value || 1 === (int) $consent_value );
			WC()->session->set( 'topsms_customer_consent', $consent );
		}
		wp_die();
	}

	/**
	 * Adds a new tab to the WooCommerce My Account page menu.
	 *
	 * @since    1.0.0
	 * @param    array $menu_items    The existing menu items.
	 * @return   array                   Modified menu items with SMS notifications tab.
	 */
	public function topsms_add_sms_notifications_tab( $menu_items ) {
		// Create a new array to hold the reordered items.
		$new_menu_items = array();

		// Find the logout item position.
		$logout_position = array_search( 'customer-logout', array_keys( $menu_items ), true );

		if ( false !== $logout_position ) {
			// Insert items before logout.
			$counter = 0;
			foreach ( $menu_items as $endpoint => $label ) {
				if ( $counter === $logout_position ) {
					$new_menu_items['sms-notifications'] = 'SMS Notifications';
				}

				$new_menu_items[ $endpoint ] = $label;
				++$counter;
			}
		} else {
			// If logout item not found, just add it to the end.
			$menu_items['sms-notifications'] = 'SMS Notifications';
			$new_menu_items                  = $menu_items;
		}

		return $new_menu_items;
	}

	/**
	 * Register endpoint for the new tab
	 *
	 * @since    1.0.0
	 */
	public function topsms_sms_notifications_endpoint() {
		add_rewrite_endpoint( 'sms-notifications', EP_ROOT | EP_PAGES );
	}

	/**
	 * Register My Account endpoint for SMS notifications.
	 *
	 * @since    1.0.0
	 */
	public function topsms_sms_notifications_content() {
		$user_id = get_current_user_id();
		$message = '';

		// Process form submission.
		if ( isset( $_SERVER['REQUEST_METHOD'] ) && 'POST' === $_SERVER['REQUEST_METHOD'] && isset( $_POST['topsms_sms_preference'] ) ) {
			// Verify nonce.
			if ( ! isset( $_POST['topsms_account_nonce'] ) ||
				! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['topsms_account_nonce'] ) ), 'topsms_account_action' ) ) {
				$message = '<div class="woocommerce-error">Security check failed. Please try again.</div>';
			} else {
				$is_enabled = isset( $_POST['topsms_customer_consent'] ) ? 'yes' : 'no';
				update_user_meta( $user_id, 'topsms_customer_consent', $is_enabled );
				$message = '<div class="woocommerce-message">SMS notification preferences updated.</div>';
			}

			// This prevents form resubmission on refresh.
			$script = '
                if (window.history.replaceState) {
                    window.history.replaceState(null, null, window.location.href);
                }
            ';
			wp_add_inline_script( 'topsms-admin-app', $script );
		}

		// Get current setting.
		$is_enabled = get_user_meta( $user_id, 'topsms_customer_consent', true );

		// Display success message if any.
		$message = '<div class="woocommerce-message">SMS notification preferences updated.</div>';
		printf( '%s', wp_kses_post( $message ) );

		// Display the form.
		?>
		<form method="post">
			<h3>SMS Notification</h3>

			<div>
				<div class="sms-notification-option">
					<input type="checkbox" name="topsms_customer_consent" id="topsms_customer_consent" value="1" <?php checked( $is_enabled, 'yes' ); ?>>
					<label for="topsms_customer_consent">
						Receive SMS Notifications 
					</label>
				</div>

				<p class="sms-notification-note">*The message might go to your spam folder. Please add the sender to the whitelist.</p>
			</div>
			
			<?php
			// Add nonce field for account page form.
			wp_nonce_field( 'topsms_account_action', 'topsms_account_nonce' );
			?>
			
			<p>
				<button type="submit" name="topsms_sms_preference" class="woocommerce-Button button sms-notification-save-btn">
					Save Preferences
				</button>
			</p>
		</form>
		<?php
	}
}
