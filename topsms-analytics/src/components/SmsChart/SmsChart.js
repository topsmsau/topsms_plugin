import { __ } from '@wordpress/i18n';
import { Component as ReactComponent } from '@wordpress/element';
import {
  Bar,
  BarChart,
  CartesianGrid,
  Cell,
  ResponsiveContainer,
  Tooltip,
  XAxis,
  YAxis,
} from 'recharts';
import { SmsTooltip } from '../SmsTooltip/SmsTooltip';

export class SmsChart extends ReactComponent {
  /**
   * Gets appropriate color for each status type
   */
  getStatusColor(status) {
    const colorMap = {
      delivered: '#4CAF50', // Green
      sent: '#2196F3', // Blue
      pending: '#FFC107', // Yellow/amber
      failed: '#F44336', // Red
      unknown: '#52accc', // Default color
    };

    return colorMap[status] || colorMap.unknown;
  }

  render() {
    const chartData = this.props.chartData.map((item) => ({
      name: item.status.charAt(0).toUpperCase() + item.status.slice(1), // Capitalize first letter
      value: item.count,
      status: item.status,
      percentage: item.percentage,
      fill: this.getStatusColor(item.status), // Add fill color directly to the data
    }));

    return (
      <div className='woocommerce-card woocommerce-analytics__card'>
        <div className='woocommerce-card__header'>
          <h2 className='woocommerce-card__title'>SMS Status Distribution</h2>
          <div className='woocommerce-card__subtitle'>
            {this.props.dateRange || 'Current period'}
          </div>
        </div>
        <div className='woocommerce-card__body'>
          <div className='woocommerce-chart-placeholder'>
            <ResponsiveContainer width='100%' height={300}>
              {chartData.length > 0 ? (
                <BarChart
                  data={chartData}
                  margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                >
                  <CartesianGrid vertical={false} strokeDasharray='3 3' />
                  <Bar
                    dataKey='value'
                    fillOpacity={0.9}
                    stroke='#333'
                    strokeWidth={1}
                    radius={[4, 4, 0, 0]}
                    name='Count'
                  >
                    {/* Use individual cells with specific colors for each bar */}
                    {chartData.map((entry, index) => (
                      <Cell key={`cell-${index}`} fill={entry.fill} />
                    ))}
                  </Bar>
                  <XAxis
                    dataKey='name'
                    tick={{ fill: '#757575' }}
                    tickLine={{ stroke: '#757575' }}
                    axisLine={{ stroke: '#757575' }}
                  />
                  <YAxis
                    allowDecimals={false}
                    domain={[
                      0,
                      (dataMax) => Math.max(5, Math.ceil(dataMax * 1.1)),
                    ]}
                    tick={{ fill: '#757575' }}
                    tickLine={{ stroke: '#757575' }}
                    axisLine={{ stroke: '#757575' }}
                  />
                  <Tooltip
                    cursor={{ fill: 'rgba(0, 0, 0, 0.1)' }}
                    content={({ active, payload, label }) => {
                      return !active ? null : (
                        <SmsTooltip
                          payload={payload}
                          label={label}
                          dateRange={this.props.dateRange}
                        />
                      );
                    }}
                  />
                </BarChart>
              ) : (
                <div className='woocommerce-chart__empty-message'>
                  {__(
                    'No data for the selected date range',
                    'wc-admin-sms-reports'
                  )}
                </div>
              )}
            </ResponsiveContainer>
          </div>

          {/* Add a legend for status colors */}
          <div
            className='woocommerce-chart__legend'
            style={{
              display: 'flex',
              justifyContent: 'center',
              marginTop: '15px',
              flexWrap: 'wrap',
              gap: '15px',
            }}
          >
            {['delivered', 'sent', 'pending', 'failed'].map((status) => (
              <div
                key={status}
                style={{
                  display: 'flex',
                  alignItems: 'center',
                  marginRight: '20px',
                }}
              >
                <span
                  style={{
                    display: 'inline-block',
                    width: '12px',
                    height: '12px',
                    backgroundColor: this.getStatusColor(status),
                    marginRight: '5px',
                  }}
                ></span>
                <span style={{ fontSize: '12px' }}>
                  {status.charAt(0).toUpperCase() + status.slice(1)}
                </span>
              </div>
            ))}
          </div>
        </div>
      </div>
    );
  }
}
