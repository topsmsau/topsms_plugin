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
        // Options for storing wc order data for topsms
        // Processing
        add_option('topsms_order_processing_enabled', true);
        add_option('topsms_order_processing_message', '');

        // Completed
        add_option('topsms_order_completed_enabled', true);
        add_option('topsms_order_completed_message', '');

        // Failed
        add_option('topsms_order_failed_enabled', true);
        add_option('topsms_order_failed_message', '');

        // Refunded
        add_option('topsms_order_refunded_enabled', true);
        add_option('topsms_order_refunded_message', '');

        // Pending payment
        add_option('topsms_order_pending_payment_enabled', true);
        add_option('topsms_order_pending_payment_message', '');

        // Cancelled
        add_option('topsms_order_cancelled_enabled', true);
        add_option('topsms_order_cancelled_message', '');

        // Onhold
        add_option('topsms_order_onhold_enabled', true);
        add_option('topsms_order_onhold_message', '');

        // Draft
        add_option('topsms_order_draft_enabled', true);
        add_option('topsms_order_draft_message', '');


        // Options for storing general topsms settings data
        add_option('topsms_settings_low_balance_alert', true);
        add_option('topsms_settings_customer_consent', true);
        add_option('topsms_settings_sms_surcharge', true);
	}

}
