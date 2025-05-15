import { __ } from '@wordpress/i18n';
import { Component as ReactComponent } from '@wordpress/element';
import { TableCard } from '@woocommerce/components';

import './SmsTable.scss';

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
            processing: '#c6e1c6', // Green
            'on-hold': '#f8dda7', // Orange
            completed: '#c8d7e1', // Blue
            cancelled: '#e5e5e5', // Grey
            refunded: '#e5e5e5', // Grey
            failed: '#eba3a3', // Red
            pending: '#e5e5e5', // Grey
            unknown: '#e5e5e5', // Grey
        };

        const fontColors = {
            processing: '#2c4700', // Green
            'on-hold': '#573b00', // Orange
            completed: '#003d66', // Blue
            cancelled: '#454545', // Grey
            refunded: '#454545', // Grey
            failed: '#570000', // Red
            pending: '#454545', // Grey
            unknown: '#454545', // Grey
        }

        const color = colors[status] || colors.unknown;
        const fontColor = fontColors[status] || fontColors.unknown;

        return {
            display: 'inline-block',
            padding: '0 1em',
            borderRadius: '4px',
            //   fontWeight: 'bold',
            textTransform: 'capitalize',
            backgroundColor: color, 
            color: fontColor, 
            maxWidth: '100%',
            lineHeight: '2.5em'
        };  
    }

    // SMS status styling
    getSmsStatusStyle(status) {
        // SMS status colors - matching bar chart
        const colors = {
            'Delivered': '#c6e1c6', // Green
            'Failed': '#eba3a3', // Red
            'Pending': '#b3d9ff', // Blue
            'Rejected': '#f8dda7', // Yellow
            unknown: '#777', // Grey
        };

        const fontColors = {
            'Delivered': '#2c4700', // Green
            'Failed': '#570000', // Red
            'Pending': '#003d66', // Blue
            'Rejected': '#573b00', // Yellow/amber
            unknown: '#454545', // Grey
        }

        const color = colors[status] || colors.unknown;
        const fontColor = fontColors[status] || fontColors.unknown;

        return {
            display: 'inline-block',
            padding: '0 1em',
            borderRadius: '4px',
            // fontWeight: 'bold',
            textTransform: 'capitalize',
            backgroundColor: color, 
            color: fontColor,
            border: `1px solid ${color}`,
            boxShadow: '0 1px 2px rgba(0,0,0,0.05)',
            maxWidth: '100%',
            lineHeight: '2.5em'
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
