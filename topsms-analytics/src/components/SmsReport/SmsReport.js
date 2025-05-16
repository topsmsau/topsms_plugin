import { Component as ReactComponent, Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import {
  appendTimestamp,
  getCurrentDates,
  getDateParamsFromQuery,
  isoDateFormat,
} from '@woocommerce/date';
import {
  ChartPlaceholder,
  ReportFilters,
  SummaryList,
  SummaryListPlaceholder,
  SummaryNumber,
  TablePlaceholder,
} from '@woocommerce/components';
import { SmsChart } from '../SmsChart/SmsChart';
import { SmsTable } from '../SmsTable/SmsTable';
import apiFetch from '@wordpress/api-fetch';

export class SmsReport extends ReactComponent {
  constructor(props) {
    super(props);

    const dateQuery = this.createDateQuery(this.props.query);

    this.state = {
      dateQuery: dateQuery,
      data: { loading: true },
      currentPage: 1,
      perPage: 10,
      allData: [], // Store all SMS data here
    };

    this.handleDateChange = this.handleDateChange.bind(this);
    this.handlePageChange = this.handlePageChange.bind(this);
    this.handlePerPageChange = this.handlePerPageChange.bind(this);

    // Schedule data loading after constructor completes
    setTimeout(() => this.loadData(), 0);
  }

  /**
   * Parses a query and returns an object representing a date query that is used to handle date range changes.
   */
  createDateQuery(query) {
    const { period, compare, before, after } = getDateParamsFromQuery(query);
    const { primary: primaryDate, secondary: secondaryDate } =
      getCurrentDates(query);
    return { period, compare, before, after, primaryDate, secondaryDate };
  }

  /**
   * Helper function to determine if a date is within a range
   */
  isDateInRange(dateString, after, before) {
    // Safety check for invalid parameters
    if (!dateString || !after || !before) {
      console.warn('Invalid date parameters in isDateInRange:', {
        dateString,
        after,
        before,
      });
      return true; // Default to showing if we have invalid date parameters
    }

    try {
      const date = new Date(dateString);
      const afterDate = new Date(after);
      const beforeDate = new Date(before);

      if (
        isNaN(date.getTime()) ||
        isNaN(afterDate.getTime()) ||
        isNaN(beforeDate.getTime())
      ) {
        console.warn('Invalid date conversion in isDateInRange');
        return true; // Default to showing if conversion failed
      }

      return date >= afterDate && date <= beforeDate;
    } catch (e) {
      console.error('Error in isDateInRange:', e);
      return true; // Default to showing if there was an error
    }
  }

  /**
   * Load SMS data from API and apply date filters
   * Modified to fetch all data without pagination
   */
  loadData() {
    if (!this.state || !this.state.dateQuery) {
      console.error('Cannot load data: dateQuery not initialized');
      return;
    }

    // Create query string with date parameters only (no pagination)
    let queryParams = '';
    if (this.state.dateQuery && this.state.dateQuery.primaryDate) {
      const { primaryDate } = this.state.dateQuery;

      // Use appendTimestamp to ensure we get the full day range
      const afterDate = encodeURIComponent(
        appendTimestamp(primaryDate.after, 'start')
      );
      const beforeDate = encodeURIComponent(
        appendTimestamp(primaryDate.before, 'end')
      );

      // Request all records by setting a very large per_page value
      // or by excluding pagination parameters if your API supports that
      queryParams = `?after=${afterDate}&before=${beforeDate}&per_page=9999`;
    }

    const apiPath = `/topsms/v1/logs${queryParams}`;
    console.log(`Fetching all data from: ${apiPath}`);

    // Make API request for all data
    apiFetch({
      path: apiPath,
      method: 'GET',
      headers: {
        'Content-Type': 'application/json',
      },
    })
      .then((response) => {
        console.log('API Response:', response);

        // Extract the logs array and data from the response
        let smsData = [];

        if (response && response.logs && Array.isArray(response.logs)) {
          smsData = response.logs;
        } else if (Array.isArray(response)) {
          smsData = response;
        } else {
          console.warn('Unexpected API response format:', response);
        }

        console.log(`Received ${smsData.length} SMS messages from API`);

        // Store all data in state
        this.setState({ allData: smsData }, () => {
          // Process the paginated subset for the current view
          this.updateCurrentPageData();
        });
      })
      .catch((error) => {
        console.error('API Error:', error);
        // Handle error state
        this.setState({
          allData: [],
          data: {
            ...this.prepareData([], 0, 0),
            error: error.message || 'Error loading data',
          },
        });
      });
  }

  /**
   * Updates the current page data based on pagination settings
   * This uses the already fetched allData instead of making new API calls
   */
  updateCurrentPageData() {
    const { currentPage, perPage, allData } = this.state;

    // Calculate total pages
    const totalItems = allData.length;
    const totalPages = Math.ceil(totalItems / perPage);

    // Get the current page subset
    const startIndex = (currentPage - 1) * perPage;
    const endIndex = Math.min(startIndex + perPage, totalItems);
    const currentPageData = allData.slice(startIndex, endIndex);

    // Process the data and update state
    const data = this.prepareData(currentPageData, totalItems, totalPages);
    this.setState({ data: data });
  }

  /**
   * Transforms data to a format suitable for saving into component state.
   */
  prepareData(currentPageData, totalItems = 0, totalPages = 1) {
    // Ensure currentPageData is an array
    if (!Array.isArray(currentPageData)) {
      console.warn('prepareData received non-array data:', currentPageData);
      currentPageData = [];
    }

    // If no data is provided, return empty state with loading false
    if (
      currentPageData.length === 0 &&
      (!this.state.allData || this.state.allData.length === 0)
    ) {
      return {
        messages: [],
        statusCounts: [
          { status: 'Delivered', count: 0, percentage: 0 },
          { status: 'Failed', count: 0, percentage: 0 },
          { status: 'Rejected', count: 0, percentage: 0 },
          { status: 'Pending', count: 0, percentage: 0 },
        ],
        loading: false,
        totals: {
          total_messages: 0,
          delivered_count: 0,
          failed_count: 0,
          pending_count: 0,
          rejected_count: 0,
        },
        pagination: {
          totalItems: 0,
          totalPages: 1,
          currentPage: this.state.currentPage,
          perPage: this.state.perPage,
        },
      };
    }

    // Get the full dataset to calculate accurate totals
    const allData = this.state.allData || currentPageData;

    // Transform API data for the current page if needed
    const processedPageData = currentPageData.map((item) => {
      // Make sure all expected fields are present
      return {
        id: item.id || 0,
        order_id: item.order_id || 0,
        order_status: item.order_status || 'unknown',
        phone: item.phone || '',
        creation_date: item.creation_date || new Date().toISOString(),
        status: item.status || 'unknown',
      };
    });

    // Create data object with paginated messages but totals from all data
    let data = {
      messages: processedPageData,
      // Use ALL data for chart display instead of just current page
      statusCounts: this.getStatusCounts(allData),
      loading: false,
      pagination: {
        totalItems: totalItems,
        totalPages: totalPages,
        currentPage: this.state.currentPage,
        perPage: this.state.perPage,
      },
    };

    // Calculate totals from ALL data, not just current page
    data.totals = {
      total_messages: allData.length,
      delivered_count: this.countByStatus(allData, 'Delivered'),
      failed_count: this.countByStatus(allData, 'Failed'),
      pending_count: this.countByStatus(allData, 'Pending'),
      rejected_count: this.countByStatus(allData, 'Rejected'),
    };

    return data;
  }

  /**
   * Counts messages by status
   */
  countByStatus(messages, status) {
    if (!Array.isArray(messages)) {
      console.warn('countByStatus received non-array:', messages);
      return 0;
    }
    return messages.filter((message) => message && message.status === status)
      .length;
  }

  /**
   * Gets counts for each status type
   */
  getStatusCounts(messages) {
    if (!Array.isArray(messages)) {
      console.warn('getStatusCounts received non-array:', messages);
      return [
        { status: 'Delivered', count: 0, percentage: 0 },
        { status: 'Failed', count: 0, percentage: 0 },
        { status: 'Rejected', count: 0, percentage: 0 },
        { status: 'Pending', count: 0, percentage: 0 },
      ];
    }

    const statusCounts = [];
    const statuses = ['Delivered', 'Failed', 'Rejected', 'Pending'];
    const totalMessages = messages.length || 1; // Avoid division by zero

    statuses.forEach((status) => {
      const count = this.countByStatus(messages, status);
      const percentage = Math.round((count / totalMessages) * 100);

      statusCounts.push({
        status: status,
        count: count,
        percentage: percentage,
      });
    });

    return statusCounts;
  }

  /**
   * When a new date range is selected in the ReportFilters component, reload the data
   */
  handleDateChange(newQuery) {
    const newDateQuery = this.createDateQuery(newQuery);

    // First update the dateQuery state and reset pagination
    this.setState(
      {
        dateQuery: newDateQuery,
        data: { loading: true },
        currentPage: 1, // Reset to first page when date changes
        allData: [], // Clear all data when date changes
      },
      () => {
        // Then load data with the new date range
        this.loadData();
      }
    );
  }

  /**
   * Handle pagination page changes - now just updates view from cached data
   */
  handlePageChange(newPage) {
    this.setState(
      {
        currentPage: newPage,
        data: { ...this.state.data, loading: true },
      },
      () => {
        // Instead of loading from API, just update from existing data
        this.updateCurrentPageData();
      }
    );
  }

  /**
   * Handle per page changes - now just updates view from cached data
   */
  handlePerPageChange(newPerPage) {
    this.setState(
      {
        perPage: newPerPage,
        currentPage: 1, // Reset to first page when items per page changes
        data: { ...this.state.data, loading: true },
      },
      () => {
        // Instead of loading from API, just update from existing data
        this.updateCurrentPageData();
      }
    );
  }

  render() {
    const reportFilters = (
      <ReportFilters
        dateQuery={this.state.dateQuery}
        query={this.props.query}
        path={this.props.path}
        isoDateFormat={isoDateFormat}
        onDateSelect={this.handleDateChange}
      />
    );

    const tableHeaders = [
      {
        key: 'order_id',
        label: __('Order ID', 'wc-admin-sms-reports'),
        isLeftAligned: true,
        isSortable: true,
      },
      {
        key: 'order_status',
        label: __('Order Status', 'wc-admin-sms-reports'),
        isSortable: true,
      },
      {
        key: 'phone',
        label: __('Phone Number', 'wc-admin-sms-reports'),
        isSortable: true,
      },
      {
        key: 'creation_date',
        label: __('Creation Date', 'wc-admin-sms-reports'),
        isSortable: true,
      },
      {
        key: 'status',
        label: __('SMS Status', 'wc-admin-sms-reports'),
        isSortable: true,
      },
    ];

    if (this.state.data.loading) {
      return (
        <Fragment>
          {reportFilters}
          <SummaryListPlaceholder numberOfItems={4} />
          <ChartPlaceholder height={300} />
          <TablePlaceholder
            caption={__('SMS Messages', 'wc-admin-sms-reports')}
            headers={tableHeaders}
          />
        </Fragment>
      );
    } else if (this.state.data.error) {
      // Display error message
      return (
        <Fragment>
          {reportFilters}
          <div
            style={{
              margin: '16px 0',
              padding: '16px',
              background: '#fff',
              border: '1px solid #ddd',
              borderLeft: '4px solid #d63638',
              boxShadow: '0 1px 1px rgba(0,0,0,.04)',
            }}
          >
            <h3 style={{ margin: '0 0 8px 0', color: '#d63638' }}>
              {__('Error Loading Data', 'wc-admin-sms-reports')}
            </h3>
            <p>{this.state.data.error}</p>
            <p>
              {__(
                'Please try again or contact support if the problem persists.',
                'wc-admin-sms-reports'
              )}
            </p>
          </div>
        </Fragment>
      );
    } else {
      const { data, dateQuery } = this.state;

      return (
        <Fragment>
          {reportFilters}
          <SummaryList>
            {() => [
              <SummaryNumber
                key='total'
                value={data.totals.total_messages}
                label={__('Total Messages', 'wc-admin-sms-reports')}
              />,
              <SummaryNumber
                key='Delivered'
                value={data.totals.delivered_count}
                label={__('Delivered', 'wc-admin-sms-reports')}
              />,
              <SummaryNumber
                key='Failed'
                value={data.totals.failed_count}
                label={__('Failed', 'wc-admin-sms-reports')}
              />,
              <SummaryNumber
                key='Rejected'
                value={data.totals.rejected_count}
                label={__('Rejected', 'wc-admin-sms-reports')}
              />,
            ]}
          </SummaryList>
          <SmsChart
            chartData={data.statusCounts}
            dateRange={dateQuery.primaryDate.range}
          />
          <SmsTable
            messageData={data.messages}
            totals={data.totals}
            headers={tableHeaders}
            pagination={data.pagination}
            onPageChange={this.handlePageChange}
            onPerPageChange={this.handlePerPageChange}
          />
        </Fragment>
      );
    }
  }
}
