<?php
/**
 * Fired during plugin activation
 *
 * @link       https://eux.com.au
 * @since      1.0.0
 *
 * @package   Topsms
 * @subpackage Topsms/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Topsms
 * @subpackage Topsms/includes
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::topsms_notifications_endpoint();
		flush_rewrite_rules();

		// Default messages for each status.
		$processing_msg = 'Hello [first_name], your order #[order_id] has been shipped and is on its way! Expected delivery within 3-5 business days. If you have any questions, feel free to contact us.';
		$completed_msg  = 'Hello [first_name], your order #[order_id] has been successfully delivered. We hope you enjoy your purchase! Thank you for shopping with us.';
		$failed_msg     = 'Hello [first_name], unfortunately, your order #[order_id] could not be processed due to a payment issue. Please try again or contact us for help.';
		$refunded_msg   = 'Hello [first_name], your order #[order_id] has been refunded. The amount should reflect in your account shortly. Let us know if you have any questions.';
		$pending_msg    = 'Hello [first_name], your order #[order_id] is awaiting payment. Please complete the payment to process your order. Contact us if you need assistance.';
		$cancelled_msg  = 'Hello [first_name], your order #[order_id] has been cancelled. If this was a mistake or you need help placing a new order, feel free to reach out.';
		$onhold_msg     = "Hello [first_name], your order #[order_id] is currently on hold. We'll notify you as soon as it's updated. Contact us if you need more information.";
		$draft_msg      = '';

		// Options for storing wc order data for topsms.
		// Processing.
		add_option( 'topsms_order_processing_enabled', 'no' );
		add_option( 'topsms_order_processing_message', $processing_msg );

		// Completed.
		add_option( 'topsms_order_completed_enabled', 'no' );
		add_option( 'topsms_order_completed_message', $completed_msg );

		// Failed.
		add_option( 'topsms_order_failed_enabled', 'no' );
		add_option( 'topsms_order_failed_message', $failed_msg );

		// Refunded.
		add_option( 'topsms_order_refunded_enabled', 'no' );
		add_option( 'topsms_order_refunded_message', $refunded_msg );

		// Pending payment.
		add_option( 'topsms_order_pending_enabled', 'no' );
		add_option( 'topsms_order_pending_message', $pending_msg );

		// Cancelled.
		add_option( 'topsms_order_cancelled_enabled', 'no' );
		add_option( 'topsms_order_cancelled_message', $cancelled_msg );

		// Onhold.
		add_option( 'topsms_order_on-hold_enabled', 'no' );
		add_option( 'topsms_order_on-hold_message', $onhold_msg );

		// Draft.
		add_option( 'topsms_order_draft_enabled', 'no' );
		add_option( 'topsms_order_draft_message', $draft_msg );

		// Options for storing general topsms settings data.
		add_option( 'topsms_settings_low_balance_alert', 'no' );
		add_option( 'topsms_settings_customer_consent', 'yes' );
		add_option( 'topsms_settings_sms_surcharge', 'no' );
		add_option( 'topsms_settings_sms_surcharge_amount', '' );

		set_transient( 'topsms_activation_redirect', true, 30 );
		set_transient( 'topsms_send_sms', true );

		global $wpdb;

		// Get WordPress database prefix.
		$table_name = $wpdb->prefix . 'topsms_logs';

		// SQL to create the table.
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            order_status VARCHAR(50) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// Check if we need to run dbDelta().
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Execute the query using dbDelta for proper table creation.
		dbDelta( $sql );
	}

	/**
	 * Register endpoint for the new tab
	 *
	 * @since    1.0.0
	 */
	public static function topsms_notifications_endpoint() {
		add_rewrite_endpoint( 'sms-notifications', EP_ROOT | EP_PAGES );
	}
}
