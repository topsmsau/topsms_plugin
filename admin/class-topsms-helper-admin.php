<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link  https://eux.com.au
 * @since 2.0.0
 *
 * @package    Topsms
 * @subpackage Topsms/admin
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
class Topsms_Helper_Admin {

	const LOW_BALANCE_THRESHOLD = 50;
	const SMS_LOWEST_BUFFER     = 2;

	/**
	 * The ID of this plugin.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  2.0.0
	 * @access private
	 * @var    string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 2.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version     The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Sends SMS alert notifications to customer when low SMS balance.
	 *
	 * @since 1.0.1
	 * @param int $balance Current account balance.
	 */
	public function topsms_low_balance_alert( $balance ) {
		// Get low balance alert option.
		$low_balance_option = get_option( 'topsms_settings_low_balance_alert', 'no' );
		if ( 'no' === $low_balance_option || ! $this->check_user_balance() ) {
			return;
		} else {
			// Check if the transient exists.
			// If transient doesn't exist (has expired or was never set), set it to true.
			$send_sms_transient = get_transient( 'topsms_send_sms' );
			if ( false === $send_sms_transient ) {
				set_transient( 'topsms_send_sms', true );
			}

			// If low balance and of transient of send_sms is true, get user phone number and send sms (call Topsms api).
			if ( $balance < self::LOW_BALANCE_THRESHOLD ) {
				if ( get_transient( 'topsms_send_sms' ) ) {
					$registration_data = get_option( 'topsms_registration_data', array() );
					if ( ! empty( $registration_data ) ) {
						$access_token = get_option( 'topsms_access_token' );
						$user_phone   = isset( $registration_data['phone_number'] ) ? $registration_data['phone_number'] : '';
						$user_company = isset( $registration_data['company'] ) ? $registration_data['company'] : '';
						$sender       = $this->topsms_fetch_sender_name();
						$message      = 'Alert: Your SMS balance is running low (under 50) on ' . $user_company . '. Please top up soon to avoid interruption to order notifications.';

						// Send SMS.
						$url  = 'https://api.topsms.com.au/functions/v1/sms';
						$body = array(
							'phone_number' => $user_phone,
							'from'         => $sender,
							'message'      => $message,
							'link'         => '',
						);

						$response = wp_remote_post(
							$url,
							array(
								'headers' => array(
									'Authorization' => 'Bearer ' . $access_token,
									'Content-Type'  => 'application/json',
								),
								'body'    => wp_json_encode( $body ),
								'timeout' => 50,
							)
						);

							$body = wp_remote_retrieve_body( $response );
							$data = json_decode( $body, true );

							// Determine API status.
						if ( is_wp_error( $response ) ) {
								$api_status = 'Failed';
						} elseif ( isset( $data['messageStatuses'][0]['statusText'] ) ) {
							$api_status = $data['messageStatuses'][0]['statusText'];
						} else {
							$api_status = 'Pending';
						}

						// Set transient to false for 24 hours.
						set_transient( 'topsms_send_sms', false, DAY_IN_SECONDS );
					}
				}
			} else {
				delete_transient( 'topsms_send_sms' );
			}
		}
	}

	/**
	 * Fetch the registered sender name from the remote Topsms API.
	 *
	 * @since  1.0.1
	 * @return $sender Sender name of the SMS.
	 */
	public function topsms_fetch_sender_name() {
		$access_token = get_option( 'topsms_access_token' );
		$sender       = '';

		// Get sender name from the remote api.
		$response = wp_remote_get(
			'https://api.topsms.com.au/functions/v1/user',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return $sender;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check the status field in the response data.
		if ( isset( $data['status'] ) && 'success' === $data['status'] ) {
			$sender = isset( $data['data']['sender'] ) ? $data['data']['sender'] : '';
		}

		// Update the option if sender name exists.
		if ( ! empty( $sender ) ) {
			update_option( 'topsms_sender', $sender );
		}

		return $sender;
	}

	/**
	 * Check user SMS balance to ensure there's enough buffer before sending SMS.
	 *
	 * @since  1.0.8
	 * @return boolean    True if enough balance, false otherwise.
	 */
	public function topsms_check_user_balance() {
		$access_token = get_option( 'topsms_access_token' );

		// Make api request to Topsms.
		$response = wp_remote_get(
			'https://api.topsms.com.au/functions/v1/user',
			array(
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $access_token,
				),
				'timeout' => 50,
			)
		);

		// Check for connection errors.
		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// Check the balance data in the response data. If there's enough buffer to send sms, return true.
		if ( isset( $data['data']['balance'] ) && $data['data']['balance'] >= self::SMS_LOWEST_BUFFER ) {
			return true;
		}

		return false;
	}

	/**
	 * Build a SQL query for retrieving contacts with various filters and options.
	 *
	 * @since 2.0.0
	 * @param array  $filters            Array of filter conditions. Default to empty array.
	 * @param string $select_clause      Custom SELECT clause. Default to null (selects all customer fields).
	 * @param string $orderby            The column to order by. Default to 'display_name'.
	 * @param string $order              The sort order (ASC/DESC). Default to 'ASC'.
	 * @param bool   $include_pagination Whether to include LIMIT and OFFSET. Optional and default to false.
	 * @param int    $per_page           The number of items per page. Default to 25.
	 * @param int    $page_number        The current page number. Default to 1.
	 *
	 * @return string The constructed SQL query string.
	 */
	public function topsms_build_contacts_query_( $filters = array(), $select_clause = null, $orderby = 'display_name', $order = 'ASC', $include_pagination = false, $per_page = 25, $page_number = 1 ) {
		global $wpdb;

		// Get subscribed status from the user meta (fallback to order meta if not set).
		$status = "(
            COALESCE(
                (SELECT um.meta_value 
                FROM {$wpdb->usermeta} um 
                WHERE um.user_id = cl.user_id 
                AND um.meta_key = 'topsms_customer_consent' 
                LIMIT 1),
                ";

		// Check if wc_orders_meta table exists for fallback.
		$orders_meta_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_orders_meta'" );
		if ( $orders_meta_table_exists ) {
			$status .= "COALESCE(
                            (SELECT om.meta_value
                            FROM {$wpdb->prefix}wc_orders_meta om
                            INNER JOIN {$wpdb->prefix}wc_order_stats os ON om.order_id = os.order_id AND om.meta_key = 'topsms_customer_consent'
                            WHERE os.customer_id = cl.customer_id
                            ORDER BY os.date_created DESC
                            LIMIT 1),
                            (SELECT pm.meta_value
                            FROM {$wpdb->prefix}wc_order_stats os
                            INNER JOIN {$wpdb->postmeta} pm ON os.order_id = pm.post_id AND pm.meta_key = 'topsms_customer_consent'
                            WHERE os.customer_id = cl.customer_id
                            ORDER BY os.date_created DESC
                            LIMIT 1)
                        )";
		} else {
			// Fallback to WP postmeta table.
			$status .= "(SELECT pm.meta_value
                        FROM {$wpdb->prefix}wc_order_stats os
                        INNER JOIN {$wpdb->postmeta} pm ON os.order_id = pm.post_id AND pm.meta_key = 'topsms_customer_consent'
                        WHERE os.customer_id = cl.customer_id
                        ORDER BY os.date_created DESC
                        LIMIT 1)";
		}

		$status .= ')
        )';

		// Get state from the customer lookup table (fallback to usermeta if not set).
		$state = "COALESCE(
            cl.state,
            (SELECT um.meta_value
            FROM {$wpdb->usermeta} um
            WHERE um.user_id = cl.user_id
            AND um.meta_key = 'billing_state'
            AND um.meta_value != ''
            AND um.meta_value IS NOT NULL
            LIMIT 1)
        )";

		// Get city from the customer lookup table (fallback to usermeta if not set).
		$city = "COALESCE(
            cl.city,
            (SELECT um.meta_value
            FROM {$wpdb->usermeta} um
            WHERE um.user_id = cl.user_id
            AND um.meta_key = 'billing_city'
            AND um.meta_value != ''
            AND um.meta_value IS NOT NULL
            LIMIT 1)
        )";

		// Get postcode from the customer lookup table (fallback to usermeta if not set).
		$postcode = "COALESCE(
            cl.postcode,
            (SELECT um.meta_value
            FROM {$wpdb->usermeta} um
            WHERE um.user_id = cl.user_id
            AND um.meta_key = 'billing_postcode'
            AND um.meta_value != ''
            AND um.meta_value IS NOT NULL
            LIMIT 1)
        )";

		// Get billing phone.
		// Check if wc_order_addresses table exists.
		// Check usermeta first, then order_addresses then fallback to postmeta.
		$order_addresses_table_exists = $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}wc_order_addresses'" );
		if ( $order_addresses_table_exists ) {
			$phone_number = "COALESCE(
                (SELECT um.meta_value
                FROM {$wpdb->usermeta} um
                WHERE um.user_id = cl.user_id
                AND um.meta_key = 'billing_phone'
                AND um.meta_value != ''
                AND um.meta_value IS NOT NULL
                LIMIT 1),
                (SELECT oa.phone
                FROM {$wpdb->prefix}wc_order_addresses oa
                INNER JOIN {$wpdb->prefix}wc_order_stats os ON oa.order_id = os.order_id
                WHERE os.customer_id = cl.customer_id
                AND oa.address_type = 'billing'
                AND oa.phone != ''
                AND oa.phone IS NOT NULL
                ORDER BY os.date_created DESC
                LIMIT 1),
                (SELECT pm.meta_value
                FROM {$wpdb->prefix}wc_order_stats os
                INNER JOIN {$wpdb->postmeta} pm ON os.order_id = pm.post_id AND pm.meta_key = '_billing_phone'
                WHERE os.customer_id = cl.customer_id
                AND pm.meta_value != ''
                AND pm.meta_value IS NOT NULL
                ORDER BY os.date_created DESC
                LIMIT 1)
            )";
		} else {
			// Fallback to WP postmeta table.
			$phone_number = "COALESCE(
                (SELECT um.meta_value
                FROM {$wpdb->usermeta} um
                WHERE um.user_id = cl.user_id
                AND um.meta_key = 'billing_phone'
                AND um.meta_value != ''
                AND um.meta_value IS NOT NULL
                LIMIT 1),
                (SELECT pm.meta_value
                FROM {$wpdb->prefix}wc_order_stats os
                INNER JOIN {$wpdb->postmeta} pm ON os.order_id = pm.post_id AND pm.meta_key = '_billing_phone'
                WHERE os.customer_id = cl.customer_id
                AND pm.meta_value != ''
                AND pm.meta_value IS NOT NULL
                ORDER BY os.date_created DESC
                LIMIT 1)
            )";
		}

		// Base query: Get first name, last name, city, state, postcode from WC customer lookup table.
		if ( null === $select_clause ) {
			$sql  = "
                SELECT cl.customer_id, cl.user_id, cl.username, cl.email, cl.first_name, cl.last_name, 
                CONCAT(cl.first_name, ' ', cl.last_name) as display_name
            ";
			$sql .= ", {$state} as state";
			$sql .= ", {$city} as city";
			$sql .= ", {$postcode} as postcode";
			$sql .= ", {$phone_number} as phone";
			$sql .= ", {$status} as status";

			// Get orders_count and total_spent.
			$sql .= ', COALESCE(os.order_count, 0) as order_count, COALESCE(os.total_spent, 0) as total_spent';
		} else {
			$sql = "SELECT {$select_clause}";
		}

		$sql .= " FROM {$wpdb->prefix}wc_customer_lookup cl";
		// Select from those orders where the status is completed or processing.
		// For each order, if the order has parent id, exclude itself and its parent.
		$sql .= " LEFT JOIN (
            SELECT customer_id, 
                COUNT(order_id) as order_count,
                SUM(total_sales) as total_spent
            FROM {$wpdb->prefix}wc_order_stats
            WHERE status IN ('wc-completed', 'wc-processing')
            AND order_id NOT IN (
                SELECT order_id 
                FROM {$wpdb->prefix}wc_order_stats 
                WHERE parent_id > 0
            )
            AND order_id NOT IN (
                SELECT parent_id 
                FROM {$wpdb->prefix}wc_order_stats 
                WHERE parent_id > 0
            )
            GROUP BY customer_id
        ) os ON cl.customer_id = os.customer_id";

		$where = array();

		// Add search query.
		if ( ! empty( $filters['search'] ) ) {
			$search  = esc_sql( $wpdb->esc_like( $filters['search'] ) );
			$where[] = "(LOWER(cl.username) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%' OR LOWER(cl.email) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%' OR LOWER(cl.first_name) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%' OR LOWER(cl.last_name) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%' OR LOWER(CONCAT(cl.first_name, ' ', cl.last_name)) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%' OR LOWER({$phone_number}) COLLATE utf8mb4_unicode_520_ci LIKE '%{$search}%')";
		}

		// Add state filter.
		if ( ! empty( $filters['state'] ) ) {
			$state_filter = esc_sql( $filters['state'] );
			$where[]      = "{$state} COLLATE utf8mb4_unicode_520_ci = '{$state_filter}'";
		}

		// Add city filter.
		if ( ! empty( $filters['city'] ) ) {
			$city_filter = esc_sql( $filters['city'] );
			$where[]     = "{$city} COLLATE utf8mb4_unicode_520_ci LIKE '%{$city_filter}%'";
		}

		// Add postcode filter.
		if ( ! empty( $filters['postcode'] ) ) {
			$postcode_filter = esc_sql( $filters['postcode'] );
			$where[]         = "{$postcode} COLLATE utf8mb4_unicode_520_ci LIKE '%{$postcode_filter}%'";
		}

		// Add orders filter.
		if ( ! empty( $filters['orders_condition'] ) && isset( $filters['orders_value'] ) ) {
			$condition = $filters['orders_condition'];
			$value     = intval( $filters['orders_value'] );

			switch ( $condition ) {
				case 'less_than':
					$where[] = "COALESCE(os.order_count, 0) < {$value}";
					break;
				case 'more_than':
					$where[] = "COALESCE(os.order_count, 0) > {$value}";
					break;
				case 'between':
					if ( isset( $filters['orders_value2'] ) ) {
						$value2  = intval( $filters['orders_value2'] );
						$where[] = "COALESCE(os.order_count, 0) BETWEEN {$value} AND {$value2}";
					}
					break;
			}
		}

		// Add total spent filter.
		if ( ! empty( $filters['spent_condition'] ) && isset( $filters['spent_value'] ) ) {
			$condition = $filters['spent_condition'];
			$value     = floatval( $filters['spent_value'] );

			switch ( $condition ) {
				case 'less_than':
					$where[] = "COALESCE(os.total_spent, 0) < {$value}";
					break;
				case 'more_than':
					$where[] = "COALESCE(os.total_spent, 0) > {$value}";
					break;
				case 'between':
					if ( isset( $filters['spent_value2'] ) ) {
						$value2  = floatval( $filters['spent_value2'] );
						$where[] = "COALESCE(os.total_spent, 0) BETWEEN {$value} AND {$value2}";
					}
					break;
			}
		}

		// Add status filter.
		if ( ! empty( $filters['status'] ) ) {
			$status_filter = esc_sql( $filters['status'] );
			if ( 'yes' === $status_filter ) {
				// Include those with status yes.
				$where[] = "{$status} COLLATE utf8mb4_unicode_520_ci = 'yes'";
			} else {
				// Default to unsubscribe.
				$where[] = "({$status} COLLATE utf8mb4_unicode_520_ci = 'no' OR {$status} IS NULL OR {$status} = '')";
			}
		}

		if ( ! empty( $where ) ) {
			$sql .= ' WHERE ' . implode( ' AND ', $where );
		}

		// Add sorting.
		if ( ! empty( $orderby ) ) {
			$allowed_orderby = array( 'email', 'name', 'city', 'state', 'postcode', 'phone', 'orders', 'total_spent', 'status' );
			if ( in_array( $orderby, $allowed_orderby ) ) {
				if ( 'name' === $orderby ) {
					$sql .= " ORDER BY CONCAT(cl.first_name, ' ', cl.last_name) COLLATE utf8mb4_unicode_520_ci {$order}";
				} elseif ( 'phone' === $orderby ) {
					$sql .= " ORDER BY phone COLLATE utf8mb4_unicode_520_ci {$order}";
				} elseif ( 'city' === $orderby ) {
					$sql .= " ORDER BY city COLLATE utf8mb4_unicode_520_ci {$order}";
				} elseif ( 'state' === $orderby ) {
					$sql .= " ORDER BY state COLLATE utf8mb4_unicode_520_ci {$order}";
				} elseif ( 'postcode' === $orderby ) {
					$sql .= " ORDER BY postcode COLLATE utf8mb4_unicode_520_ci {$order}";
				} elseif ( 'orders' === $orderby ) {
					$sql .= " ORDER BY order_count {$order}";
				} elseif ( 'total_spent' === $orderby ) {
					$sql .= " ORDER BY total_spent {$order}";
				} elseif ( 'status' === $orderby ) {
					$sql .= " ORDER BY status COLLATE utf8mb4_unicode_520_ci {$order}";
				} else {
					$sql .= " ORDER BY cl.{$orderby} {$order}";
				}
			} else {
				$sql .= ' ORDER BY CONCAT(cl.first_name, " ", cl.last_name) COLLATE utf8mb4_unicode_520_ci ASC';
			}
		}

		// Add pagination.
		if ( $include_pagination ) {
			$sql .= " LIMIT {$per_page}";
			$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;
		}

		return $sql;
	}

    /**
	 * Filter contacts based on phone number and status.
	 * Ensure no contacts with duplicate phone number,
	 * and priotise contacts with status 'yes' over duplicates.
	 *
	 * @since 2.0.9
	 * @param array $contacts Array of contact data.
	 * @return array Filtered and deduplicated contacts.
	 */
	public function topsms_filter_contacts( $contacts ) {
		$existed_phones    = array();
		$filtered_contacts = array();

		foreach ( $contacts as $contact ) {
			// Skip contacts without valid phone.
			if ( empty( $contact['phone'] ) || trim( $contact['phone'] ) === '' ) {
				continue;
			}

			// Normalise phone number.
			$normalised_phone = preg_replace( '/[^0-9]/', '', $contact['phone'] );
			// Skip if normalised phone is empty.
			if ( empty( $normalised_phone ) ) {
				continue;
			}

			$contact_status = $contact['status'] ?? '';
			// Check if phone exists before.
			if ( isset( $existed_phones[ $normalised_phone ] ) ) {
				// If phone exists, replace only if the current contact is subscribed and the previously stored one doesn't.
				// Otherwise keep the existing one and skip this duplicate.
				$stored_index  = $existed_phones[ $normalised_phone ];
				$stored_status = $filtered_contacts[ $stored_index ]['status'] ? $filtered_contacts[ $stored_index ]['status'] : '';
				if ( 'yes' === $contact_status && 'yes' !== $stored_status ) {
					// Replace the contact.
					$filtered_contacts[ $stored_index ] = $contact;
				}
				continue;
			}

			// Only add contacts with 'yes' status.
			if ( 'yes' === $contact_status ) {
				$index                               = count( $filtered_contacts );
				$existed_phones[ $normalised_phone ] = $index;
				$filtered_contacts[]                 = $contact;
			}
		}
		return $filtered_contacts;
	}

    /**
	 * Get the saved contacts lists from transient.
	 * If transient not found, get contacts lists by the all saved filters.
	 *
	 * @since    2.0.0
	 * @return array $lists The contacts lists with all information.
	 */
	public function topsms_get_contacts_lists() {
		// Try to get lists from transient.
		$lists = get_transient( 'topsms_contacts_lists' );

		// If transient exists, return it.
		if ( false !== $lists ) {
			return $lists;
		}

		// Transient doesn't exist, do an sql query to get the contacts list and save to transient.
		global $wpdb;

		$lists   = array();
		$filters = array();

		// Add "All Contacts" as the first list (only subscribed users - if not set, default to subscribed).
		$all_contacts_filter = array();

		// Get the contacts by filter.
		$sql          = $this->topsms_build_contacts_query_( $all_contacts_filter, null, false );
		$all_contacts = $wpdb->get_results( $sql, ARRAY_A ); // Store as array.

		// Filter contacts: include those with status yes (default to unsubscribed) and have phone.
		// Also make sure no duplicated phone.
		$all_contacts = $this->topsms_filter_contacts( $all_contacts );
		$all_count    = count( $all_contacts );

		// For transient data.
		$lists['all_contacts'] = array(
			'filter_id'   => 'all_contacts',
			'filter_name' => 'All Subscribed Contacts',
			'count'       => $all_count,
			'contacts'    => array_values( $all_contacts ),
		);

		// For return data.
		$filters['all_contacts'] = array(
			'id'    => 'all_contacts',
			'name'  => 'All Subscribed Contacts',
			'count' => $all_count,
		);

		// Get saved filters from options.
		$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );

		// Extract contacts data by filters.
		foreach ( $saved_filters as $filter_id => $filter ) {
			// Skip filters if status filter is unsubscribed (don't send to unsubscribed contacts).
			if ( isset( $filter['status'] ) && 'no' === $filter['status'] ) {
				// For transient data.
				$lists[ $filter_id ] = array(
					'filter_id'   => $filter_id,
					'filter_name' => $filter['name'],
					'count'       => 0,
					'contacts'    => array(),
				);

				// For return data.
				$filters[ $filter_id ] = array(
					'id'    => $filter_id,
					'name'  => $filter['name'],
					'count' => 0,
				);
				continue;
			}

			// Get the contacts by filter.
			$sql      = $this->topsms_build_contacts_query_( $filter, null, false );
			$contacts = $wpdb->get_results( $sql, ARRAY_A ); // Store as array.

			// Filter contacts: include those with status yes (default to unsubscribed) and have phone.
			// Also make sure no duplicated phone.
			$contacts = $this->topsms_filter_contacts( $contacts );
			$count    = count( $contacts );

			// For transient data.
			$lists[ $filter_id ] = array(
				'filter_id'   => $filter_id,
				'filter_name' => $filter['name'],
				'count'       => $count,
				'contacts'    => array_values( $contacts ),
			);

			// For return data.
			$filters[ $filter_id ] = array(
				'id'    => $filter_id,
				'name'  => $filter['name'],
				'count' => $count,
			);
		}

		// Store all contacts lists data in transient.
		$transient_key = 'topsms_contacts_lists';
		set_transient( $transient_key, $lists );

		return $filters;
	}

    /**
	 * Get the order shipping method by order id.
	 *
	 * @since  2.0.21
     * @param  WC_Order $order         The order object.
     * 
	 * @return string $delivery_type   Delivery type/shipping method of the order.
	 */
    public function topsms_get_delivery_type($order) {
        // Default to shipping.
        $delivery_type = 'shipping';
        $shipping_methods = $order->get_shipping_methods();
	
        if ( ! empty( $shipping_methods ) ) {
            foreach ( $shipping_methods as $shipping_method ) {
                $method_id = $shipping_method->get_method_id();
                
                // Check if it's a pickup method, by checking if there's pickup/local pickup in the id.
                if ( strpos( $method_id, 'local_pickup' ) !== false || strpos( $method_id, 'pickup' ) !== false ) {
                    $delivery_type = 'pickup';
                    break;
                }
            }
        }

        return $delivery_type;
    }
}
