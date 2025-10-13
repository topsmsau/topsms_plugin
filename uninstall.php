<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * When populating this file, consider the following flow
 * of control:
 *
 * - This method should be static
 * - Check if the $_REQUEST content actually is the plugin name
 * - Run an admin referrer check to make sure it goes through authentication
 * - Verify the output of $_GET makes sense
 * - Repeat with other user roles. Best directly by using the links/query string parameters.
 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
 *
 * This file may be updated more in future version of the Boilerplate; however, this is the
 * general skeleton and outline for how the file should work.
 *
 * For more information, see the following discussion:
 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
 *
 * @link       http://eux.com.au
 * @since      1.0.0
 *
 * @package    Topsms
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete automation settings options.
delete_option( 'topsms_order_processing_enabled' );
delete_option( 'topsms_order_processing_message' );
delete_option( 'topsms_order_completed_enabled' );
delete_option( 'topsms_order_completed_message' );
delete_option( 'topsms_order_failed_enabled' );
delete_option( 'topsms_order_failed_message' );
delete_option( 'topsms_order_refunded_enabled' );
delete_option( 'topsms_order_refunded_message' );
delete_option( 'topsms_order_pending_enabled' );
delete_option( 'topsms_order_pending_message' );
delete_option( 'topsms_order_cancelled_enabled' );
delete_option( 'topsms_order_cancelled_message' );
delete_option( 'topsms_order_on-hold_enabled' );
delete_option( 'topsms_order_on-hold_message' );
delete_option( 'topsms_order_draft_enabled' );
delete_option( 'topsms_order_draft_message' );

// Delete general settings options.
delete_option( 'topsms_settings_low_balance_alert' );
delete_option( 'topsms_settings_customer_consent' );
delete_option( 'topsms_settings_sms_surcharge' );
delete_option( 'topsms_settings_sms_surcharge_amount' );

// Delete registration/connection options.
delete_option( 'topsms_access_token' );
delete_option( 'topsms_refresh_token' );
delete_option( 'topsms_registration_data' );
delete_option( 'topsms_sender' );

// Delete bulk sms options.
delete_option( 'topsms_contacts_list_saved_filters' );

// Clear cache and delete transients.
wp_cache_delete( 'topsms_contacts_list_cities' );
wp_cache_delete( 'topsms_contacts_list_states' );
delete_transient( 'topsms_activation_redirect' );
delete_transient( 'topsms_contacts_lists' );


// Delete logs table.
global $wpdb;
$table_name = $wpdb->prefix . 'topsms_logs';
$wpdb->query(
	$wpdb->prepare(
		'DROP TABLE IF EXISTS %1s',
		$table_name
	)
);
