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

import './SmsChart.scss';

export class SmsChart extends ReactComponent {
  /**
   * Gets appropriate color for each status type
   */
  getStatusColor(status) {
    const colorMap = {
      Delivered: '#c6e1c6', // Green
      Failed: '#eba3a3', // Red
      Pending: '#b3d9ff', // Blue
      Rejected: '#f8dda7', // Yellow
      unknown: '#777', // Grey
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
      <div
        style={{
          backgroundColor: '#fff',
          padding: '20px',
          marginBottom: '20px',
          boxShadow: 'rgba(0, 0, 0, 0.1) 0px 0px 0px 1px',
          outline: 'none',
          borderRadius: '7px',
        }}
        className='woocommerce-card woocommerce-analytics__card topsms-woocommerce-card'
      >
        <div className='woocommerce-card__header'>
          <h2 className='woocommerce-card__title'>SMS Status Distribution</h2>
          <div className='woocommerce-card__subtitle'>
            {this.props.dateRange || 'Current period'}
          </div>
        </div>
        <div className='woocommerce-card__body'>
          <div
            style={{ backgroundColor: '#fff' }}
            className='woocommerce-chart-placeholder'
          >
            <ResponsiveContainer width='100%' height={300}>
              {chartData.length > 0 ? (
                <BarChart
                  data={chartData}
                  margin={{ top: 20, right: 30, left: 20, bottom: 5 }}
                >
                  <CartesianGrid vertical={false} strokeDasharray='3 3' />
                  <Bar
                    dataKey='value'
                    fillOpacity={1}
                    // stroke='#333'
                    // strokeWidth={0.5}
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
              marginBottom: '20px',
              flexWrap: 'wrap',
              gap: '15px',
            }}
          >
            {['Delivered', 'Pending', 'Failed', 'Rejected'].map((status) => (
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
