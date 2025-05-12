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



        set_transient('topsms_activation_redirect', true, 30);

        global $wpdb;
    
        // Get WordPress database prefix
        $table_name = $wpdb->prefix . 'topsms_logs';
        
        // SQL to create the table
        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT NOT NULL,
            order_status VARCHAR(50) NOT NULL,
            phone VARCHAR(20) NOT NULL,
            creation_date DATETIME DEFAULT CURRENT_TIMESTAMP,
            status VARCHAR(30) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
        
        // Check if we need to run dbDelta()
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Execute the query using dbDelta for proper table creation
        dbDelta($sql);



        // $order_statuses = array('processing', 'completed', 'on-hold', 'cancelled', 'pending');
        
        // // Array of possible SMS statuses
        // $sms_statuses = array('sent', 'failed', 'delivered', 'pending');
        
        // // Current timestamp
        // $now = current_time('mysql');
        
        // // Insert 10 dummy records
        // for ($i = 1; $i <= 10; $i++) {
        //     // Random order ID between 1000 and 9999
        //     $order_id = rand(1000, 9999);
            
        //     // Random order status
        //     $order_status = $order_statuses[array_rand($order_statuses)];
            
        //     // Random phone number
        //     $phone = '+1' . rand(2000000000, 9999999999);
            
        //     // Random SMS status
        //     $status = $sms_statuses[array_rand($sms_statuses)];
            
        //     // Random date within the last week
        //     $days_ago = rand(0, 7);
        //     $hours_ago = rand(0, 23);
        //     $minutes_ago = rand(0, 59);
        //     $creation_date = date('Y-m-d H:i:s', strtotime("$now - $days_ago days - $hours_ago hours - $minutes_ago minutes"));
            
        //     // Insert the record
        //     $wpdb->insert(
        //         $table_name,
        //         array(
        //             'order_id' => $order_id,
        //             'order_status' => $order_status,
        //             'phone' => $phone,
        //             'creation_date' => $creation_date,
        //             'status' => $status
        //         )
        //     );
        // }
        


        
	}

}
