import { __ } from '@wordpress/i18n';
import { Component as ReactComponent } from '@wordpress/element';
import { TableCard } from '@woocommerce/components';

export class SmsTable extends ReactComponent {
  constructor(props) {
    super(props);

    this.handleSort = this.handleSort.bind(this);
    this.onPageChange = this.onPageChange.bind(this);
    this.onPerPageChange = this.onPerPageChange.bind(this);

    const defaultSortColumn = 'creation_date';
    const defaultSortOrder = 'desc';

    this.state = {
      messageData: this.props.messageData ? [...this.props.messageData] : [],
      sortColumn: defaultSortColumn,
      sortOrder: defaultSortOrder,
    };
  }

  componentDidMount() {
    // Sort initial data
    if (this.props.messageData && this.props.messageData.length > 0) {
      const sortedData = this.sort(
        this.props.messageData,
        this.state.sortColumn,
        this.state.sortOrder
      );
      this.setState({ messageData: sortedData });
    }
  }

  componentDidUpdate(prevProps) {
    // Update when new message data is received
    if (
      prevProps.messageData !== this.props.messageData &&
      this.props.messageData
    ) {
      const sortedData = this.sort(
        this.props.messageData,
        this.state.sortColumn,
        this.state.sortOrder
      );
      this.setState({ messageData: sortedData });
    }
  }

  // Switches between ascending and descending sort orders
  changeSortOrder(order) {
    return order === 'asc' ? 'desc' : 'asc';
  }

  // Set header sort options
  setHeaderSortOptions(header) {
    const newHeader = { ...header };
    if (newHeader.key === this.state.sortColumn) {
      newHeader.defaultSort = true;
      newHeader.defaultOrder = this.state.sortOrder;
    } else {
      if (newHeader.defaultSort) delete newHeader.defaultSort;
      if (newHeader.defaultOrder) delete newHeader.defaultOrder;
    }
    return newHeader;
  }

  // Format date for display
  formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString();
  }

  // Order status styling
  getOrderStatusStyle(status) {
    // Order status colors
    const colors = {
      processing: '#5b841b', // Green
      'on-hold': '#94660c', // Orange
      completed: '#4169e1', // Blue
      cancelled: '#a00', // Red
      refunded: '#777', // Gray
      failed: '#a00', // Red
      pending: '#ffba00', // Yellow
      unknown: '#777', // Default gray
    };

    const color = colors[status] || colors.unknown;

    return {
      display: 'inline-block',
      padding: '4px 10px',
      borderRadius: '4px',
      fontWeight: 'bold',
      textTransform: 'capitalize',
      backgroundColor: `${color}15`, // 15% opacity
      color: '#23282d', // Standard dark text
    };
  }

  // SMS status styling
  getSmsStatusStyle(status) {
    // SMS status colors - matching bar chart
    const colors = {
      delivered: '#4CAF50', // Green
      sent: '#2196F3', // Blue
      pending: '#FFC107', // Yellow/amber
      failed: '#F44336', // Red
      unknown: '#9E9E9E', // Gray
    };

    const color = colors[status] || colors.unknown;

    return {
      display: 'inline-block',
      padding: '4px 10px',
      borderRadius: '4px',
      fontWeight: 'bold',
      textTransform: 'capitalize',
      backgroundColor: `${color}15`, // 15% opacity
      color: color,
      border: `1px solid ${color}`,
      boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
    };
  }

  // Sort table data
  sort(data, column, sortOrder) {
    if (!data || !Array.isArray(data)) return [];

    const appliedSortOrder = sortOrder === 'asc' ? 1 : -1;
    return [...data].sort((a, b) => {
      if (a[column] > b[column]) return appliedSortOrder;
      if (a[column] < b[column]) return -1 * appliedSortOrder;
      return 0;
    });
  }

  // Handle sort events
  handleSort(newSortColumn) {
    let { messageData, sortColumn, sortOrder } = this.state;

    if (sortColumn === newSortColumn) {
      messageData.reverse();
      sortOrder = this.changeSortOrder(sortOrder);
    } else {
      sortColumn = newSortColumn;
      messageData = this.sort(messageData, sortColumn, sortOrder);
    }

    this.setState({
      messageData: messageData,
      sortColumn: sortColumn,
      sortOrder: sortOrder,
    });
  }

  // Handle page change events
  onPageChange(newPage) {
    if (this.props.onPageChange) {
      this.props.onPageChange(newPage);
    }
  }

  // Handle per page change events
  onPerPageChange(newPerPage) {
    if (this.props.onPerPageChange) {
      this.props.onPerPageChange(newPerPage);
    }
  }

  render() {
    console.log('Rendering SmsTable with data:', this.state.messageData);

    const { messageData } = this.state;
    const totals = this.props.totals || {
      total_messages: 0,
      delivered_count: 0,
      sent_count: 0,
      pending_count: 0,
      failed_count: 0,
    };

    const pagination = this.props.pagination || {
      totalItems: messageData.length,
      totalPages: 1,
      currentPage: 1,
      perPage: 10,
    };

    // Create the table rows with properly styled elements
    const rows = messageData.map((item) => {
      // Ensure the item has all the expected properties
      const safeItem = {
        id: item.id || 0,
        order_id: item.order_id || 0,
        order_status: item.order_status || 'unknown',
        phone: item.phone || '',
        creation_date: item.creation_date || new Date().toISOString(),
        status: item.status || 'unknown',
      };

      return [
        {
          display: `#${safeItem.order_id}`,
          value: safeItem.order_id,
        },
        {
          display: (
            <span style={this.getOrderStatusStyle(safeItem.order_status)}>
              {safeItem.order_status.charAt(0).toUpperCase() +
                safeItem.order_status.slice(1)}
            </span>
          ),
          value: safeItem.order_status,
        },
        {
          display: safeItem.phone,
          value: safeItem.phone,
        },
        {
          display: this.formatDate(safeItem.creation_date),
          value: safeItem.creation_date,
        },
        {
          display: (
            <span style={this.getSmsStatusStyle(safeItem.status)}>
              {safeItem.status.charAt(0).toUpperCase() +
                safeItem.status.slice(1)}
            </span>
          ),
          value: safeItem.status,
        },
      ];
    });

    // Create table headers with sort options
    const headers = (this.props.headers || []).map((header) =>
      this.setHeaderSortOptions(header)
    );

    // Create summary items
    const summary = [
      {
        key: 'total',
        label: __('Total Messages', 'wc-admin-sms-reports'),
        value: totals.total_messages,
      },
      {
        key: 'delivered',
        label: __('Delivered', 'wc-admin-sms-reports'),
        value: totals.delivered_count,
      },
      {
        key: 'sent',
        label: __('Sent', 'wc-admin-sms-reports'),
        value: totals.sent_count,
      },
      {
        key: 'failed',
        label: __('Failed', 'wc-admin-sms-reports'),
        value: totals.failed_count,
      },
    ];

    return (
      <TableCard
        title={__('SMS Messages', 'wc-admin-sms-reports')}
        rows={rows}
        headers={headers}
        rowsPerPage={pagination.perPage}
        totalRows={pagination.totalItems}
        currentPage={pagination.currentPage}
        summary={summary}
        onSort={this.handleSort}
        onPageChange={this.onPageChange}
        onRowsChange={this.onPerPageChange}
      />
    );
  }
}
