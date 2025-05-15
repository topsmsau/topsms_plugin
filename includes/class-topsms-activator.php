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
        // Default messages for each status
        $processing_msg = 'Hello [first_name], your order with ID [id] has been shipped and is on its way! Expected delivery within 3-5 business days. If you have any questions, feel free to contact us.';
        $completed_msg = 'Hello [first_name], your order #[order_id] has been successfully delivered. We hope you enjoy your purchase! Thank you for shopping with us.';
        $failed_msg = 'Hello [first_name], unfortunately, your order #[order_id] could not be processed due to a payment issue. Please try again or contact us for help.';
        $refunded_msg = 'Hello [first_name], your order #[order_id] has been refunded. The amount should reflect in your account shortly. Let us know if you have any questions.';
        $pending_msg = 'Hello [first_name], your order #[order_id] is awaiting payment. Please complete the payment to process your order. Contact us if you need assistance.';
        $cancelled_msg = 'Hello [first_name], your order #[order_id] has been cancelled. If this was a mistake or you need help placing a new order, feel free to reach out.';
        $onhold_msg = 'Hello [first_name], your order #[order_id] is currently on hold. We’ll notify you as soon as it’s updated. Contact us if you need more information.';
        $draft_msg = '';


        // Options for storing wc order data for topsms
        // Processing
        add_option('topsms_order_processing_enabled', 'no');
        add_option('topsms_order_processing_message', $processing_msg);

        // Completed
        add_option('topsms_order_completed_enabled', 'no');
        add_option('topsms_order_completed_message', $completed_msg);

        // Failed
        add_option('topsms_order_failed_enabled', 'no');
        add_option('topsms_order_failed_message', $failed_msg);

        // Refunded
        add_option('topsms_order_refunded_enabled', 'no');
        add_option('topsms_order_refunded_message', $refunded_msg);

        // Pending payment
        add_option('topsms_order_pending_payment_enabled', 'no');
        add_option('topsms_order_pending_payment_message', $pending_msg);

        // Cancelled
        add_option('topsms_order_cancelled_enabled', 'no');
        add_option('topsms_order_cancelled_message', $cancelled_msg);

        // Onhold
        add_option('topsms_order_onhold_enabled', 'no');
        add_option('topsms_order_onhold_message', $onhold_msg);

        // Draft
        add_option('topsms_order_draft_enabled', 'no');
        add_option('topsms_order_draft_message', $draft_msg);


        // Options for storing general topsms settings data
        add_option('topsms_settings_low_balance_alert', 'no');
        add_option('topsms_settings_customer_consent', 'no');
        add_option('topsms_settings_sms_surcharge', 'no');
        add_option('topsms_settings_sms_surcharge_amount', '');

        
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
