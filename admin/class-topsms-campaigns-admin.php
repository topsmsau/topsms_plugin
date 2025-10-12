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
 * Campaign Table for TopSMS plugin.
 *
 * @package    Topsms
 * @subpackage Topsms/admin
 * @author     EUX <samee@eux.com.au>
 */
class Topsms_Campaigns_Admin extends WP_List_Table {

	private $total_campaigns = 0;

	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'campaign',
				'plural'   => 'campaigns',
				'ajax'     => false,
			)
		);
	}

	public function topsms_get_campaigns_query( $per_page = 25, $page_number = 1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Get orderby arg.
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( $_REQUEST['orderby'] ) : 'id';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( $_REQUEST['order'] ) : 'ASC';

		$where_clauses = array();
		$where_values  = array();

		// Search filter.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search          = '%' . $wpdb->esc_like( sanitize_text_field( $_REQUEST['s'] ) ) . '%';
			$where_clauses[] = '(id LIKE %s OR job_name LIKE %s)';
			$where_values[]  = $search;
			$where_values[]  = $search;
		}

		// Status filter.
		if ( ! empty( $_REQUEST['filter_status'] ) ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = sanitize_text_field( $_REQUEST['filter_status'] );
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
		}

		// Count total for pagination.
		$count_sql            = "SELECT COUNT(*) FROM {$table_name} {$where_sql}";
		$this->total_campaigns = $wpdb->get_var( ! empty( $where_values ) ? $wpdb->prepare( $count_sql, $where_values ) : $count_sql );

		// Do an sql query with pagination.
		$offset = ( $page_number - 1 ) * $per_page;
		$sql    = "SELECT * FROM {$table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";
		
		$query_values = array_merge( $where_values, array( $per_page, $offset ) );
		$results      = $wpdb->get_results( $wpdb->prepare( $sql, $query_values ) );

		return $results;
	}

	public function topsms_get_campaigns_data( $per_page = 25, $page_number = 1 ) {
		$campaigns_raw = $this->topsms_get_campaigns_query( $per_page, $page_number );

		// If there's any campaigns.
		if ( empty( $campaigns_raw ) ) {
			return array();
		}

		$campaigns = array();
		foreach ( $campaigns_raw as $campaign ) {
			$campaigns[] = array(
				'id'            => $campaign->id,
				'campaign_name' => $campaign->job_name ? $campaign->job_name : '',
				'cost'          => $campaign->cost ? $campaign->cost : 0,
				'datetime'      => $campaign->campaign_datetime ? $campaign->campaign_datetime : '',
				'status'        => $campaign->status ? $campaign->status : '',
			);
		}

		return $campaigns;
	}

	public function topsms_get_status_counts() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Get cache data if exists.
		$cache_key = 'topsms_campaigns_status_counts';
		$counts    = wp_cache_get( $cache_key );

		// Do an sql query if not cached.
		if ( false === $counts ) {
			$counts = array(
				'all'       => $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ),
				'scheduled' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'scheduled' ) ),
                'processing' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'processing' ) ),
				'completed' => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'completed' ) ),
				'failed'    => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'failed' ) ),
				'draft'     => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'draft' ) ),
                'cancelled'     => $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table_name} WHERE status = %s", 'cancelled' ) ),
			);
			// Cache for 1 hr.
			wp_cache_set( $cache_key, $counts, '', 3600 );
		}

		return $counts;
	}

	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'id'            => 'ID',
			'campaign_name' => 'Campaign Name',
			'cost'          => 'SMS Count',
			'datetime'      => 'Datetime',
			'status'        => 'Status',
            'actions' => 'Actions'
		);
	}

	public function get_sortable_columns() {
		return array(
			'id'            => array( 'id', false ),
			'campaign_name' => array( 'job_name', false ),
			'cost'          => array( 'cost', false ),
			'datetime'      => array( 'campaign_datetime', true ),
			'status'        => array( 'status', false ),
		);
	}

	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="campaigns[]" value="%s" />', $item['id'] );
	}

	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
                return '#' . $item[ $column_name ];
			case 'campaign_name':
			case 'cost':
				return $item[ $column_name ];
			case 'datetime':
				if ( ! empty( $item[ $column_name ] ) ) {
					return date( 'Y-m-d H:i:s', strtotime( $item[ $column_name ] ) );
				}
				return '-';
			default:
				return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '-';
		}
	}

	public function column_campaign_name( $item ) {
        // Remove timestamp from the campaign/job name.
        // Remove underscore + everything after it (if exists).
        $campaign_name = $item['campaign_name'];
        $campaign_name = preg_replace('/_[^_]+$/', '', $campaign_name);

        // Only allow editing for draft campaigns
        if ( $item['status'] === 'draft' ) {
            $edit_url = admin_url( 'admin.php?page=topsms-bulksms&campaign_id=' . $item['id'] );
            
            return sprintf(
                '<strong><a href="%s">%s</a></strong> %s',
                $edit_url,
                esc_html( $campaign_name ),
                $this->row_actions( array() )
            );
        } else {
            return sprintf(
                '<strong>%s</strong>',
			    esc_html( $campaign_name )
            );
        }
        $edit_url = $edit_url = admin_url( 'admin.php?page=topsms-bulksms&campaign_id=' . $item['id'] );

        return sprintf(
            '<strong><a href="%s">%s</a></strong> %s',
            $edit_url,
            esc_html( $campaign_name ),
            $this->row_actions( array() )
        );
	}

	public function column_status( $item ) {
		$status_classes = array(
			'scheduled' => 'status-processing',
            'processing' => 'status-processing',
			'completed' => 'status-completed',
			'failed'    => 'status-failed',
			'draft'     => 'status-pending',
            'cancelled' => 'status-cancelled',
		);

		$status = $item['status'];
		$class  = isset( $status_classes[ $status ] ) ? $status_classes[ $status ] : 'status-completed';

		return sprintf(
			'<mark class="order-status %s"><span>%s</span></mark>',
			esc_attr( $class ),
			esc_html( ucfirst( $status ) )
		);
	}

    public function column_actions( $item ) {
        // Only allow for scheduled campaigns (status is scheduled).
        if ( $item['status'] === 'scheduled' ) {
            $cancel_url = wp_nonce_url(
                admin_url( 'admin.php?page=topsms-campaigns&action=cancel_campaign&campaign_id=' . $item['id'] ),
                'cancel_campaign_' . $item['id']
            );
            
            // Check the current time is within 30 minutes of scheduled time.
            $is_disabled = false;
            $title_text = 'Cancel Campaign';
            
            // Check the time difference.
            if ( ! empty( $item['datetime'] ) ) {
                $scheduled_time = strtotime( $item['datetime'] );
                $current_time = current_time( 'timestamp' );
                $time_diff = $scheduled_time - $current_time;
                
                // Disable if within 30 minutes/negative time difference.
                if ( $time_diff <= 1800 && $time_diff > 0 ) {
                    $is_disabled = true;
                    $title_text = 'Cancel not allowed: Campaign is scheduled to start in less than 30 minutes';
                } elseif ( $time_diff <= 0 ) {
                    $is_disabled = true;
                    $title_text = 'Cancel not allowed: Campaign is running';
                }
            }
            
            if ( $is_disabled ) {
                return sprintf(
                    '<a class="button wc-action-button wc-action-button-cancel cancel ivole-order cr-order-dimmed view ivole-order cr-order-dimmed" href="javascript:void(0);" aria-label="%s">%s</a>',
                    esc_attr( $title_text ),
                    esc_html( $title_text )
                );
            } else {
                return sprintf(
                    '<a class="button wc-action-button wc-action-button-cancel cancel" href="%s" onclick="return confirm(\'Are you sure you want to cancel this campaign?\');" title="%s" aria-label="%s"></a>',
                    esc_url( $cancel_url ),
                    esc_attr( $title_text ),
                    esc_attr( $title_text )
                );
            }
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
		$this->items = $this->topsms_get_campaigns_data( $per_page, $current_page );

		// Set pagination args.
		$this->set_pagination_args(
			array(
				'total_items' => $this->total_campaigns,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->total_campaigns / $per_page ),
			)
		);
	}

	protected function get_views() {
		$views       = array();
		$counts      = $this->topsms_get_status_counts();
		$current_url = admin_url( 'admin.php?page=topsms-campaigns' );

		// Build base url preserving search.
		$base_url = $current_url;
		if ( ! empty( $_REQUEST['s'] ) ) {
			$base_url = add_query_arg( 's', urlencode( $_REQUEST['s'] ), $base_url );
		}

		// All campaigns view.
		$all_url = remove_query_arg( 'filter_status', $base_url );
		$class   = empty( $_REQUEST['filter_status'] ) ? 'current' : '';

		$views['all'] = sprintf(
			'<a href="%s" class="%s">All Campaigns <span class="count">(%d)</span></a>',
			$all_url,
			$class,
			$counts['all']
		);

		// Status views.
		$statuses = array(
			'scheduled' => 'Scheduled',
            'processing' => 'Processing',
			'completed' => 'Completed',
			'failed'    => 'Failed',
			'draft'     => 'Draft',
            'cancelled' => 'Cancelled',
		);

		foreach ( $statuses as $status_key => $status_label ) {
			$status_url = add_query_arg( 'filter_status', $status_key, remove_query_arg( 'filter_status', $base_url ) );
			$class      = ( isset( $_REQUEST['filter_status'] ) && $_REQUEST['filter_status'] === $status_key ) ? 'current' : '';

			$views[ $status_key ] = sprintf(
				'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
				$status_url,
				$class,
				$status_label,
				$counts[ $status_key ]
			);
		}

		return $views;
	}
}