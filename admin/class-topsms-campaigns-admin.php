<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://eux.com.au
 * @since      2.0.0
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

	/**
	 * The total number of campaigns.
	 *
	 * @since    2.0.0
	 * @access   private
	 * @var      int    $total_campaigns    The total number of campaigns.
	 */
	private $total_campaigns = 0;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    2.0.0
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => 'campaign',
				'plural'   => 'campaigns',
				'ajax'     => false,
			)
		);
	}

	/**
	 * Get campaigns query with filters and pagination.
	 *
	 * @since 2.0.0
	 * @param int $per_page The number of items per page.
	 * @param int $page_number The current page number.
	 * @return array Array of campaign objects.
	 */
	public function topsms_get_campaigns_query( $per_page = 25, $page_number = 1 ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Get orderby arg.
		$orderby = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['orderby'] ) ) : 'id';
		$order   = ( ! empty( $_REQUEST['order'] ) ) ? sanitize_text_field( wp_unslash( $_REQUEST['order'] ) ) : 'ASC';

		$where_clauses = array();
		$where_values  = array();

		// Search filter.
		if ( ! empty( $_REQUEST['s'] ) ) {
			$search          = '%' . $wpdb->esc_like( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) . '%';
			$where_clauses[] = '(id LIKE %s OR job_name LIKE %s)';
			$where_values[]  = $search;
			$where_values[]  = $search;
		}

		// Status filter.
		if ( ! empty( $_REQUEST['filter_status'] ) ) {
			$where_clauses[] = 'status = %s';
			$where_values[]  = sanitize_text_field( wp_unslash( $_REQUEST['filter_status'] ) );
		}

		$where_sql = '';
		if ( ! empty( $where_clauses ) ) {
			$where_sql = 'WHERE ' . implode( ' AND ', $where_clauses );
		}

		// Count total for pagination.
		$count_sql             = "SELECT COUNT(*) FROM {$table_name} {$where_sql}";
		$this->total_campaigns = $wpdb->get_var( ! empty( $where_values ) ? $wpdb->prepare( $count_sql, $where_values ) : $count_sql );

		// Do an sql query with pagination.
		$offset = ( $page_number - 1 ) * $per_page;
		$sql    = "SELECT * FROM {$table_name} {$where_sql} ORDER BY {$orderby} {$order} LIMIT %d OFFSET %d";

		$query_values = array_merge( $where_values, array( $per_page, $offset ) );
		$results      = $wpdb->get_results( $wpdb->prepare( $sql, $query_values ) );

		return $results;
	}

	/**
	 * Get formatted campaigns data for display.
	 *
	 * @since 2.0.0
	 * @param int $per_page The number of items per page.
	 * @param int $page_number The current page number.
	 * @return array Array of formatted campaign data.
	 */
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

	/**
	 * Get count of campaigns by status.
	 *
	 * @since 2.0.0
	 * @return array Array of status counts.
	 */
	public function topsms_get_status_counts() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'topsms_campaigns';

		// Get cache data if exists.
		$cache_key = 'topsms_campaigns_status_counts';
		$counts    = wp_cache_get( $cache_key );

		// Do an sql query if not cached.
		if ( false === $counts ) {
			$counts = array(
				'all'        => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s', $table_name ) ),
				'scheduled'  => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'scheduled' ) ),
				'processing' => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'processing' ) ),
				'completed'  => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'completed' ) ),
				'failed'     => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'failed' ) ),
				'draft'      => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'draft' ) ),
				'cancelled'  => $wpdb->get_var( $wpdb->prepare( 'SELECT COUNT(*) FROM %1s WHERE status = %s', $table_name, 'cancelled' ) ),
			);
			// Cache for 1 hr.
			wp_cache_set( $cache_key, $counts, '', 3600 );
		}

		return $counts;
	}

	/**
	 * Define table columns.
	 *
	 * @since 2.0.0
	 * @return array Array of column names and labels.
	 */
	public function get_columns() {
		return array(
			'cb'            => '<input type="checkbox" />',
			'id'            => 'ID',
			'campaign_name' => 'Campaign Name',
			'cost'          => 'SMS Count',
			'datetime'      => 'Datetime',
			'status'        => 'Status',
			'actions'       => 'Actions',
		);
	}

	/**
	 * Define sortable columns.
	 *
	 * @since 2.0.0
	 * @return array Array of sortable column configurations.
	 */
	public function get_sortable_columns() {
		return array(
			'id'            => array( 'id', false ),
			'campaign_name' => array( 'job_name', false ),
			'cost'          => array( 'cost', false ),
			'datetime'      => array( 'campaign_datetime', true ),
			'status'        => array( 'status', false ),
		);
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 2.0.0
	 * @param array $item The current item data.
	 * @return string HTML for the checkbox.
	 */
	public function column_cb( $item ) {
		return sprintf( '<input type="checkbox" name="campaigns[]" value="%s" />', $item['id'] );
	}

	/**
	 * Render default column content.
	 *
	 * @since 2.0.0
	 * @param array  $item The current item data.
	 * @param string $column_name The column name.
	 * @return string The column content.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				return '#' . $item[ $column_name ];
			case 'campaign_name':
			case 'cost':
				return $item[ $column_name ];
			case 'datetime':
				if ( ! empty( $item[ $column_name ] ) ) {
                    return $item[ $column_name ];
                }
				return '-';
			default:
				return isset( $item[ $column_name ] ) ? esc_html( $item[ $column_name ] ) : '-';
		}
	}

	/**
	 * Render the campaign name column with edit link.
	 *
	 * @since 2.0.0
	 * @param array $item The current item data.
	 * @return string HTML for the campaign name column.
	 */
	public function column_campaign_name( $item ) {
		// Remove timestamp from the campaign/job name.
		// Remove underscore + everything after it (if exists).
		$campaign_name = $item['campaign_name'];
		$campaign_name = preg_replace( '/_[^_]+$/', '', $campaign_name );

		// Only allow editing for draft campaigns.
		if ( 'draft' === $item['status'] ) {
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
		$edit_url = admin_url( 'admin.php?page=topsms-bulksms&campaign_id=' . $item['id'] );

		return sprintf(
			'<strong><a href="%s">%s</a></strong> %s',
			$edit_url,
			esc_html( $campaign_name ),
			$this->row_actions( array() )
		);
	}

	/**
	 * Render the status column with styled badge.
	 *
	 * @since 2.0.0
	 * @param array $item The current item data.
	 * @return string HTML for the status column.
	 */
	public function column_status( $item ) {
		$status_classes = array(
			'scheduled'  => 'status-processing',
			'processing' => 'status-processing',
			'completed'  => 'status-completed',
			'failed'     => 'status-failed',
			'draft'      => 'status-pending',
			'cancelled'  => 'status-cancelled',
		);

		$status = $item['status'];
		$class  = isset( $status_classes[ $status ] ) ? $status_classes[ $status ] : 'status-completed';

		return sprintf(
			'<mark class="order-status %s"><span>%s</span></mark>',
			esc_attr( $class ),
			esc_html( ucfirst( $status ) )
		);
	}

	/**
	 * Render the actions column with cancel button for scheduled campaigns.
	 *
	 * @since 2.0.0
	 * @param array $item The current item data.
	 * @return string HTML for the actions column.
	 */
	public function column_actions( $item ) {
		// Only allow for scheduled campaigns (status is scheduled).
		if ( 'scheduled' === $item['status'] ) {
			$cancel_url = wp_nonce_url(
				admin_url( 'admin.php?page=topsms-campaigns&action=cancel_campaign&campaign_id=' . $item['id'] ),
				'cancel_campaign_' . $item['id']
			);

			// Check the current time is within 30 minutes of scheduled time.
			$is_disabled = false;
			$title_text  = 'Cancel Campaign';

			// Check the time difference.
			if ( ! empty( $item['datetime'] ) ) {
				$scheduled_time = strtotime( $item['datetime'] );
				$current_time   = current_time( 'timestamp' );
				$time_diff      = $scheduled_time - $current_time;

				// Disable if within 30 minutes/negative time difference.
				if ( $time_diff <= 1800 && $time_diff > 0 ) {
					$is_disabled = true;
					$title_text  = 'Cancel not allowed: Campaign is scheduled to start in less than 30 minutes';
				} elseif ( $time_diff <= 0 ) {
					$is_disabled = true;
					$title_text  = 'Cancel not allowed: Campaign is running';
				}
			}

			if ( $is_disabled ) {
                $actions[] = sprintf(
                    '<a class="button wc-action-button wc-action-button-cancel cancel ivole-order cr-order-dimmed view ivole-order cr-order-dimmed" href="javascript:void(0);" aria-label="%s" title="%s">Cancel</a>',
                    esc_attr( $title_text ),
                    esc_attr( $title_text )
                );
            } else {
                $actions[] = sprintf(
                    '<a class="button wc-action-button wc-action-button-cancel cancel" href="%s" onclick="return confirm(\'Are you sure you want to cancel this campaign?\');" title="%s" aria-label="%s">Cancel</a>',
                    esc_url( $cancel_url ),
                    esc_attr( $title_text ),
                    esc_attr( $title_text )
                );
            }
		}

        
        // Send again button - only show for completed campaigns.
        if ( 'completed' === $item['status'] ) {
        $send_again_url = wp_nonce_url(
                admin_url( 'admin.php?page=topsms-campaigns&action=send_again&campaign_id=' . $item['id'] ),
			'send_again_campaign_' . $item['id']
		);
		$actions[] = sprintf(
                '<a class="button wc-action-button wc-action-button-send-again send-again" href="%s" title="Send Again" aria-label="Send Again">Send Again</a>',
			esc_url( $send_again_url )
		);
        }

        // Delete button.
        $delete_url = wp_nonce_url(
			admin_url( 'admin.php?page=topsms-campaigns&action=delete_campaign&campaign_id=' . $item['id'] ),
			'delete_campaign_' . $item['id']
		);
		$actions[] = sprintf(
			'<a class="button wc-action-button wc-action-button-delete delete" href="%s" onclick="return confirm(\'Are you sure you want to delete this campaign again?\');" title="Delete" aria-label="Delete">Delete</a>',
			esc_url( $delete_url )
		);

        // View report button.
        $view_report_url = wp_nonce_url(
			admin_url( 'admin.php?page=topsms-campaigns&action=view_report&campaign_id=' . $item['id'] ),
			'view_report_' . $item['id']
		);

		$actions[] = sprintf(
			'<a class="button wc-action-button wc-action-button-view view" href="%s" title="View Report" aria-label="View Report">View Report</a>',
			esc_url( $view_report_url )
		);

        // Return all actions joined together.
        if ( ! empty( $actions ) ) {
            return implode( ' ', $actions );
        }

        return '-';
	}

	/**
	 * Prepare items for display in the table.
	 *
	 * @since 2.0.0
	 */
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

	/**
	 * Generate view links for filtering (all campaigns and status filters).
	 *
	 * @since 2.0.0
	 * @return array Array of view links.
	 */
	protected function get_views() {
		$views       = array();
		$counts      = $this->topsms_get_status_counts();
		$current_url = admin_url( 'admin.php?page=topsms-campaigns' );

		// Build base url preserving search.
		$base_url = $current_url;
		if ( ! empty( $_REQUEST['s'] ) ) {
			$base_url = add_query_arg( 's', sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ), $base_url );
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
			'scheduled'  => 'Scheduled',
			'processing' => 'Processing',
			'completed'  => 'Completed',
			'failed'     => 'Failed',
			'draft'      => 'Draft',
			'cancelled'  => 'Cancelled',
		);

		foreach ( $statuses as $status_key => $status_label ) {
			$status_url = add_query_arg( 'filter_status', $status_key, remove_query_arg( 'filter_status', $base_url ) );
			$class      = ( isset( $_REQUEST['filter_status'] ) && sanitize_text_field( wp_unslash( $_REQUEST['filter_status'] ) ) === $status_key ) ? 'current' : '';

			$views[ $status_key ] = sprintf(
				'<a href="%s" class="%s">%s <span class="count">(%d)</span></a>',
				esc_url( $status_url ),
				esc_attr( $class ),
				esc_html( $status_label ),
				(int) $counts[ $status_key ]
			);
		}

		return $views;
	}
}
