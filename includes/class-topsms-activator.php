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

		// Activation ransients.
		set_transient( 'topsms_activation_redirect', true, 30 );
		set_transient( 'topsms_send_sms', true );

		// Setup options and database.
		self::topsms_setup();
	}

	/**
	 * Setup function from both activation and update.
	 *
	 * @since    2.0.4
	 */
	private static function topsms_setup() {
		// Default messages for each status.
		$defaults = array(
			'processing' => "Hi [first_name], your order #[order_id] is confirmed and being prepared. You'll get another SMS once it's on the way.",
			'completed'  => 'Hello [first_name], your order #[order_id] has been successfully delivered. We hope you enjoy your purchase! Thank you for shopping with us.',
			'failed'     => 'Hello [first_name], unfortunately, your order #[order_id] could not be processed due to a payment issue. Please try again or contact us for help.',
			'refunded'   => 'Hello [first_name], your order #[order_id] has been refunded. The amount should reflect in your account shortly. Let us know if you have any questions.',
			'pending'    => 'Hello [first_name], your order #[order_id] is awaiting payment. Please complete the payment to process your order. Contact us if you need assistance.',
			'cancelled'  => 'Hello [first_name], your order #[order_id] has been cancelled. If this was a mistake or you need help placing a new order, feel free to reach out.',
			'on-hold'    => "Hello [first_name], your order #[order_id] is currently on hold. We'll notify you as soon as it's updated. Contact us if you need more information.",
			'draft'      => '',
		);

		// Migrate legacy single-template options to shipping/pickup variants without wiping custom copy.
		foreach ( $defaults as $status => $default_message ) {
			$enabled_key  = 'topsms_order_' . $status . '_enabled';
			$legacy_key   = 'topsms_order_' . $status . '_message';
			$shipping_key = 'topsms_order_' . $status . '_shipping_message';
			$pickup_key   = 'topsms_order_' . $status . '_pickup_message';

			self::topsms_add_option_if_missing( $enabled_key, 'no' );

			$legacy_message = get_option( $legacy_key, false );
			$seed_message   = ( false !== $legacy_message && '' !== $legacy_message ) ? $legacy_message : $default_message;

			self::topsms_add_option_if_missing( $shipping_key, $seed_message );
			self::topsms_add_option_if_missing( $pickup_key, $seed_message );
		}

		// Options for storing general topsms settings data.
		self::topsms_add_option_if_missing( 'topsms_settings_low_balance_alert', 'no' );
		self::topsms_add_option_if_missing( 'topsms_settings_customer_consent', 'yes' );
		self::topsms_add_option_if_missing( 'topsms_settings_sms_surcharge', 'no' );
		self::topsms_add_option_if_missing( 'topsms_settings_sms_surcharge_amount', '' );
		self::topsms_add_option_if_missing( 'topsms_sender', '' );

		// Options for bulksms — never wipe existing saved segments on update.
		self::topsms_add_option_if_missing( 'topsms_contacts_list_saved_filters', array() );

		global $wpdb;

		// Get WordPress database prefix.
		$table_name = $wpdb->prefix . 'topsms_logs';

		// SQL to create the logs table.
		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            order_status VARCHAR(50) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// SQL to create the campaigns table.
		$campaigns_table = $wpdb->prefix . 'topsms_campaigns';
		$campaigns_sql   = "CREATE TABLE IF NOT EXISTS $campaigns_table (
            id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            job_name varchar(255) DEFAULT NULL,
            campaign_uid varchar(255) DEFAULT NULL,
            data longtext DEFAULT NULL,
            action varchar(20) NOT NULL DEFAULT 'instant',
            status varchar(20) NOT NULL DEFAULT 'draft',
            campaign_datetime datetime DEFAULT NULL,
            cost int(10) UNSIGNED DEFAULT NULL,
            details text DEFAULT NULL,
            webhook_token varchar(255) DEFAULT NULL,
            created_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY job_name (job_name),
            KEY status (status),
            KEY campaign_datetime (campaign_datetime)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

		// Check if we need to run dbDelta().
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Execute the query using dbDelta for proper table creation.
		dbDelta( $sql );
		dbDelta( $campaigns_sql );

		// Add db version to options.
		update_option( 'topsms_db_version', TOPSMS_DB_VERSION );
	}

	/**
	 * Add an option only when it does not already exist.
	 *
	 * @since 2.0.20
	 * @param string $name    Option name.
	 * @param mixed  $default Default value.
	 */
	private static function topsms_add_option_if_missing( $name, $default ) {
		if ( false === get_option( $name, false ) ) {
			add_option( $name, $default );
		}
	}

	/**
	 * Register endpoint for the new tab.
	 *
	 * @since    1.0.0
	 */
	public static function topsms_notifications_endpoint() {
		add_rewrite_endpoint( 'sms-notifications', EP_ROOT | EP_PAGES );
	}

	/**
	 * Plugin update function.
	 *
	 * @since    2.0.4
	 */
	public static function update() {
		// Setup options and database.
		self::topsms_setup();
	}
}
