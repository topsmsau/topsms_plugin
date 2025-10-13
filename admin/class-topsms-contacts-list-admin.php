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

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Include WP list table class.
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Contacts List Table for TopSMS plugin.
 *
 * @package    Topsms
 * @subpackage Topsms/admin
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Contacts_List_Admin extends WP_List_Table {

	private $total_contacts = 0;
	private $helper;

	public function __construct( $helper ) {
		parent::__construct(
			array(
				'singular' => 'contact',
				'plural'   => 'contacts',
				'ajax'     => false,
			)
		);
		$this->helper = $helper;
	}

	public function topsms_get_contacts_list_query( $per_page = 25, $page_number = 1 ) {
		global $wpdb;

		// Get orderby arg.
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'display_name';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';

		// Build filters array from request.
		$filters = array();

		if ( ! empty( $_REQUEST['s'] ) ) {
			$filters['search'] = sanitize_text_field( $_REQUEST['s'] );
		}
		if ( ! empty( $_REQUEST['filter_state'] ) ) {
			$filters['state'] = sanitize_text_field( $_REQUEST['filter_state'] );
		}
		if ( ! empty( $_REQUEST['filter_city'] ) ) {
			$filters['city'] = sanitize_text_field( $_REQUEST['filter_city'] );
		}
		if ( ! empty( $_REQUEST['filter_postcode'] ) ) {
			$filters['postcode'] = sanitize_text_field( $_REQUEST['filter_postcode'] );
		}
		if ( ! empty( $_REQUEST['filter_orders_condition'] ) && isset( $_REQUEST['filter_orders_value'] ) ) {
			$filters['orders_condition'] = sanitize_text_field( $_REQUEST['filter_orders_condition'] );
			$filters['orders_value']     = $_REQUEST['filter_orders_value'];
			if ( isset( $_REQUEST['filter_orders_value2'] ) ) {
				$filters['orders_value2'] = $_REQUEST['filter_orders_value2'];
			}
		}
		if ( ! empty( $_REQUEST['filter_spent_condition'] ) && isset( $_REQUEST['filter_spent_value'] ) ) {
			$filters['spent_condition'] = sanitize_text_field( $_REQUEST['filter_spent_condition'] );
			$filters['spent_value']     = $_REQUEST['filter_spent_value'];
			if ( isset( $_REQUEST['filter_spent_value2'] ) ) {
				$filters['spent_value2'] = $_REQUEST['filter_spent_value2'];
			}
		}
		if ( ! empty( $_REQUEST['filter_status'] ) ) {
			$filters['status'] = sanitize_text_field( $_REQUEST['filter_status'] );
		}

		// Get the query with pagination (true for pagination)
		$sql = $this->helper->topsms_build_contacts_query_( $filters, null, $orderby, $order, true, $per_page, $page_number );

		// Count total for pagination using helper.
		$count_sql            = $this->helper->topsms_build_contacts_query_( $filters, 'COUNT(*)' );
		$this->total_contacts = $wpdb->get_var( $count_sql );

		$results = $wpdb->get_results( $sql );

		return $results;
	}

	public function topsms_get_contacts_list_data( $per_page = 25, $page_number = 1 ) {
		$customers = $this->topsms_get_contacts_list_query( $per_page, $page_number );
		// error_log( 'customers:' . print_r( $customers, true ) );

		// If there's any contact.
		if ( empty( $customers ) ) {
			return array();
		}

		$contacts = array();
		foreach ( $customers as $customer ) {
			$contacts[] = array(
				'id'          => $customer->customer_id,
				'name'        => trim( $customer->display_name ) ? : '',
				'email'       => $customer->email,
				'city'        => $customer->city ? $customer->city : '',
				'state'       => $customer->state ? $customer->state : '',
				'postcode'    => $customer->postcode ? $customer->postcode : '',
				'phone'       => $customer->phone ? $customer->phone : '',
				'orders'      => $customer->order_count ? $customer->order_count : 0,
				'total_spent' => $customer->total_spent ? $customer->total_spent : 0,
				'status'      => $customer->status ? $customer->status : '',
			);
		}

		return $contacts;
	}

	public function topsms_get_contacts_list_unique_states() {
		global $wpdb;

		// Get cache data if exists.
		$cache_key = 'topsms_contacts_list_states';
		$states    = wp_cache_get( $cache_key );

		// Do an sql query if not cached.
		if ( false === $states ) {
			$states = $wpdb->get_col(
				"
                SELECT DISTINCT state 
                FROM {$wpdb->prefix}wc_customer_lookup 
                WHERE state != '' 
                ORDER BY state ASC
            "
			);
			// Cache for 5 mins.
			wp_cache_set( $cache_key, $states, '', 5 * MINUTE_IN_SECONDS );
		}

		return $states;
	}

	public function topsms_get_contacts_list_total_count() {
		global $wpdb;

		// Get cache data if exists.
		$cache_key = 'topsms_contacts_list_total';
		$count     = wp_cache_get( $cache_key );

		// Do an sql query if not cached.
		if ( false === $count ) {
			$count = $wpdb->get_var(
				"
                SELECT COUNT(*)
                FROM {$wpdb->prefix}wc_customer_lookup
            "
			);
			// Cache for 5 mins.
			wp_cache_set( $cache_key, $count, '', 5 * MINUTE_IN_SECONDS );
		}

		return $count;
	}

	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'name'        => 'Name',
			'email'       => 'Email',
			'city'        => 'City',
			'state'       => 'State',
			'postcode'    => 'Postcode',
			'phone'       => 'Phone',
			'orders'      => 'Orders',
			'total_spent' => 'Total Spent',
			'status'      => 'Status',
		);
	}

	public function get_sortable_columns() {
		return array(
			'name'        => array( 'name', false ),
			'email'       => array( 'email', false ),
			'city'        => array( 'city', false ),
			'state'       => array( 'state', false ),
			'postcode'    => array( 'postcode', false ),
			'phone'       => array( 'phone', false ),
			'orders'      => array( 'orders', false ),
			'total_spent' => array( 'total_spent', false ),
			'status'      => array( 'status', true ),
		);
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="contacts[]" value="%s" />', $item['id'] );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'city':
			case 'state':
				return ! empty( $item[ $column_name ] ) ? $item[ $column_name ] : '-';
			case 'postcode':
			case 'phone':
			case 'orders':
				return $item[ $column_name ] > 0 ? $item[ $column_name ] : '-';
			case 'total_spent':
				return wc_price( $item[ $column_name ] );
			// case 'status':
			// if (!empty($item[$column_name])) {
			// $status = $item[$column_name] === 'yes' ? 'Subscribed' : 'Unsubscribed';
			// return $status;
			// } else {
			// '';
			// }
			default:
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '-';
		}
	}

	public function column_name( $item ) {
		if ( $item['id'] ) {
			// For logged-in users.
			$edit_url = admin_url( 'user-edit.php?user_id=' . $item['id'] );
			return sprintf(
				'<strong><a href="%s">%s</a></strong> %s',
				$edit_url,
				esc_html( $item['name'] ),
				$this->row_actions( array() )
			);
		} else {
			// For non logged-in users.
			return sprintf( '%s', esc_html( $item['name'] ) );
		}
	}

	public function column_status( $item ) {
		if ( ! empty( $item['status'] ) ) {
			if ( $item['status'] === 'yes' ) {
				return '<mark class="order-status status-processing"><span>Subscribed</span></mark>';
			} else {
				return '<mark class="order-status status-failed"><span>Unsubscribed</span></mark>';
			}
		} else {
			return '<mark class="order-status status-completed"><span>Subscribed</span></mark>';
		}
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		// Pagination.
		$per_page     = 25;
		$current_page = $this->get_pagenum();

		// Get data with pagination.
		$this->items = $this->topsms_get_contacts_list_data( $per_page, $current_page );

		// Set pagination args.
		$this->set_pagination_args(
			array(
				'total_items' => $this->total_contacts,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->total_contacts / $per_page ),
			)
		);
	}

	protected function get_views() {
		$views       = array();
		$current_url = remove_query_arg( array( 'filter_state', 'filter_city', 'filter_postcode', 'paged', 'filter_orders_condition', 'filter_orders_value', 'filter_orders_value2', 'filter_spent_condition', 'filter_spent_value', 'filter_spent_value2', 'filter_status' ), admin_url( 'admin.php?page=topsms-contacts-list' ) );

		// Preserve search parameter.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$current_url = add_query_arg( 's', urlencode( $_REQUEST['s'] ), $current_url );
		}

		// All contacts view.
		$all_url   = admin_url( 'admin.php?page=topsms-contacts-list' );
		$class     = ( empty( $_REQUEST['filter_state'] ) && empty( $_REQUEST['filter_city'] ) && empty( $_REQUEST['filter_postcode'] ) && empty( $_REQUEST['s'] ) && empty( $_REQUEST['filter_orders_condition'] ) && empty( $_REQUEST['filter_spent_condition'] ) ) ? 'current' : '';
		$all_count = $this->topsms_get_contacts_list_total_count();

		$views['all'] = sprintf(
			'<a href="%s" class="%s">All Contacts <span class="count">(%d)</span></a>',
			$all_url,
			$class,
			$all_count
		);

		// Get saved filters.
		$saved_filters = get_option( 'topsms_contacts_list_saved_filters', array() );
		foreach ( $saved_filters as $filter_id => $filter ) {
			$filter_params = array( 'page' => 'topsms-contacts-list' );

			if ( ! empty( $filter['state'] ) ) {
				$filter_params['filter_state'] = $filter['state'];
			}
			if ( ! empty( $filter['city'] ) ) {
				$filter_params['filter_city'] = $filter['city'];
			}
			if ( ! empty( $filter['postcode'] ) ) {
				$filter_params['filter_postcode'] = $filter['postcode'];
			}
			if ( ! empty( $filter['search'] ) ) {
				$filter_params['s'] = $filter['search'];
			}
			if ( ! empty( $filter['orders_condition'] ) ) {
				$filter_params['filter_orders_condition'] = $filter['orders_condition'];
				$filter_params['filter_orders_value']     = $filter['orders_value'];
				if ( ! empty( $filter['orders_value2'] ) ) {
					$filter_params['filter_orders_value2'] = $filter['orders_value2'];
				}
			}
			if ( ! empty( $filter['spent_condition'] ) ) {
				$filter_params['filter_spent_condition'] = $filter['spent_condition'];
				$filter_params['filter_spent_value']     = $filter['spent_value'];
				if ( ! empty( $filter['spent_value2'] ) ) {
					$filter_params['filter_spent_value2'] = $filter['spent_value2'];
				}
			}
			if ( ! empty( $filter['status'] ) ) {
				$filter_params['filter_status'] = $filter['status'];
			}

			// Add filter_id to params to identify which filter is active.
			$filter_params['active_filter_id'] = $filter_id;
			$filter_url                        = add_query_arg( $filter_params, admin_url( 'admin.php' ) );

            // Get total count for the filter.
            $filters_for_query = array();
            $filters = array( 'state', 'city', 'postcode', 'search', 'orders_condition', 'orders_value', 'orders_value2', 'spent_condition', 'spent_value', 'spent_value2', 'status' );
            foreach ( $filters as $key ) {
                if ( isset( $filter[ $key ] ) && $filter[ $key ] !== '' ) {
                    $filters_for_query[ $key ] = $filter[ $key ];
                }
            }

            global $wpdb;

            $count_sql   = $this->helper->topsms_build_contacts_query_( $filters_for_query, 'COUNT(*)' );
            $filter_count = $wpdb->get_var( $count_sql );

			// Check if the current filter matches.
			$is_current = true;
			if ( ( isset( $_REQUEST['filter_state'] ) ? $_REQUEST['filter_state'] : '' ) !== ( $filter['state'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['filter_city'] ) ? $_REQUEST['filter_city'] : '' ) !== ( $filter['city'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['filter_postcode'] ) ? $_REQUEST['filter_postcode'] : '' ) !== ( $filter['postcode'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['s'] ) ? $_REQUEST['s'] : '' ) !== ( $filter['search'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['filter_orders_condition'] ) ? $_REQUEST['filter_orders_condition'] : '' ) !== ( $filter['orders_condition'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['filter_spent_condition'] ) ? $_REQUEST['filter_spent_condition'] : '' ) !== ( $filter['spent_condition'] ?? '' ) ) {
				$is_current = false;
			}
			if ( ( isset( $_REQUEST['filter_status'] ) ? $_REQUEST['filter_status'] : '' ) !== ( $filter['status'] ?? '' ) ) {
				$is_current = false;
			}

			// Set as current view.
			$class = $is_current ? 'current' : '';

			$views[ $filter_id ] = sprintf(
                '<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
                $filter_url,
                $class,
                $filter['name'],
                $filter_count
            );
		}

		return $views;
	}

	protected function extra_tablenav( $which ) {
		if ( $which === 'top' ) {
			// Get unique states for options.
			$states = $this->topsms_get_contacts_list_unique_states();

			$orders_condition = $_REQUEST['filter_orders_condition'] ?? '';
			$spent_condition  = $_REQUEST['filter_spent_condition'] ?? '';

			$active_filter_id = $_REQUEST['active_filter_id'] ?? '';
			?>
			<div class="topsms-contacts-list-filters-section alignleft actions">
				<!-- State filter -->
				<select name="filter_state" id="filter_state" class="topsms-contacts-list-filter-input">
					<option value="">All States</option>
					<?php foreach ( $states as $state ) : ?>
						<option value="<?php echo esc_attr( $state ); ?>" <?php selected( $_REQUEST['filter_state'] ?? '', $state ); ?>>
							<?php echo esc_html( $state ); ?>
						</option>
					<?php endforeach; ?>
				</select>

				<!-- City filter -->
				<input type="text" name="filter_city" id="filter_city" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_city'] ?? '' ); ?>" placeholder="City" style="width: 100px;">

				<!-- Postcode filter -->
				<input type="text" name="filter_postcode" id="filter_postcode" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_postcode'] ?? '' ); ?>" placeholder="Postcode" style="width: 100px;">
			
				<!-- Orders filter -->
				<select name="filter_orders_condition" id="filter_orders_condition" class="topsms-contacts-list-filter-input">
					<option value="">Orders</option>
					<option value="less_than" <?php selected( $orders_condition, 'less_than' ); ?>>Less than</option>
					<option value="more_than" <?php selected( $orders_condition, 'more_than' ); ?>>Greater than</option>
					<option value="between" <?php selected( $orders_condition, 'between' ); ?>>Between</option>
				</select>
				<span id="filter_orders_inputs" style="<?php echo empty( $orders_condition ) ? 'display:none;' : ''; ?>">
					<input type="number" name="filter_orders_value" id="filter_orders_value" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_orders_value'] ?? '' ); ?>"  placeholder="<?php echo ( $orders_condition === 'between' ) ? 'From' : 'Value'; ?>"  style="width: 80px;" min="0">
					
					<input type="number" name="filter_orders_value2" id="filter_orders_value2" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_orders_value2'] ?? '' ); ?>"  placeholder="To"  style="width: 80px; <?php echo ( $orders_condition === 'between' ) ? '' : 'display:none;'; ?>"  min="0">
				</span>
				
				<!-- Total spent filter -->
				<select name="filter_spent_condition" id="filter_spent_condition" class="topsms-contacts-list-filter-input">
					<option value="">Total Spent</option>
					<option value="less_than" <?php selected( $spent_condition, 'less_than' ); ?>>Less than</option>
					<option value="more_than" <?php selected( $spent_condition, 'more_than' ); ?>>Greater than</option>
					<option value="between" <?php selected( $spent_condition, 'between' ); ?>>Between</option>
				</select>
				<span id="filter_spent_inputs" style="<?php echo empty( $spent_condition ) ? 'display:none;' : ''; ?>">
					<input type="number" name="filter_spent_value" id="filter_spent_value" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_spent_value'] ?? '' ); ?>"  placeholder="<?php echo ( $spent_condition === 'between' ) ? 'From' : 'Value'; ?>"  step="0.01" style="width: 80px;" min="0">

					<input type="number" name="filter_spent_value2" id="filter_spent_value2" class="topsms-contacts-list-filter-input" value="<?php echo esc_attr( $_REQUEST['filter_spent_value2'] ?? '' ); ?>"  placeholder="To"  step="0.01"  style="width: 80px; <?php echo ( $spent_condition === 'between' ) ? '' : 'display:none;'; ?>"  min="0">
				</span>

				<!-- State filter -->
				<select name="filter_status" id="filter_status" class="topsms-contacts-list-filter-input">
					<option value="">Statuses</option>
					<option value="yes" <?php selected( $_REQUEST['filter_status'] ?? '', 'yes' ); ?>>Subscribed</option>
					<option value="no" <?php selected( $_REQUEST['filter_status'] ?? '', 'no' ); ?>>Unsubscribed</option>
				</select>
				
				<input type="submit" name="filter_action" id="post-query-submit" class="button" value="Filter">
				
				<!-- Show save filter and clear filters button when filters are applied -->
				<?php if ( ! empty( $_REQUEST['filter_city'] ) || ! empty( $_REQUEST['filter_state'] ) || ! empty( $_REQUEST['filter_postcode'] ) || ! empty( $_REQUEST['s'] ) || ! empty( $_REQUEST['filter_orders_condition'] ) || ! empty( $_REQUEST['filter_spent_condition'] || ! empty( $_REQUEST['filter_status'] ) ) ) : ?>
					<button type="button" id="save-filter" class="button">Save Filter</button>
					<a href="<?php echo admin_url( 'admin.php?page=topsms-contacts-list' ); ?>" class="button">Clear Filters</a>
					
					<!-- If a filter is clicked, display delete filter button -->
					<?php if ( ! empty( $active_filter_id ) ) : ?>
						<button type="button" id="delete-filter" class="button button-link-delete" data-filter-id="<?php echo esc_attr( $active_filter_id ); ?>">Delete Filter</button>
					<?php endif; ?>
				<?php endif; ?>
			</div>
			<?php
		}
	}
}
