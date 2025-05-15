import { Component as ReactComponent } from '@wordpress/element';

export class SmsTooltip extends ReactComponent {
  /**
   * Gets status color for tooltip
   */
  getStatusColor(status) {
    const statusColors = {
      Delivered: '#4CAF50', // Green
      Sent: '#2196F3', // Blue
      Pending: '#FFC107', // Yellow/amber
      Failed: '#F44336', // Red
    };

    return statusColors[status] || '#52accc'; // Default color
  }

  render() {
    const { payload, label, dateRange } = this.props;

    if (!payload || !payload.length) {
      return null;
    }

    const value = payload[0].value;
    const percentage = payload[0].payload.percentage || 0;

    return (
      <div
        className='d3-chart__tooltip'
        style={{
          background: 'white',
          padding: '10px',
          border: '1px solid #ccc',
          boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
          borderRadius: '4px',
          fontSize: '12px',
        }}
      >
        <h4 style={{ margin: '0 0 8px' }}>{dateRange}</h4>
        <ul
          style={{
            padding: 0,
            margin: 0,
            listStyle: 'none',
          }}
        >
          <li
            className='key-row'
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginBottom: '4px',
            }}
          >
            <div
              className='key-container'
              style={{
                display: 'flex',
                alignItems: 'center',
              }}
            >
              <span
                className='key-color'
                style={{
                  backgroundColor: this.getStatusColor(label),
                  width: '12px',
                  height: '12px',
                  display: 'inline-block',
                  marginRight: '8px',
                  borderRadius: '2px',
                }}
              />
              <span className='key-key'>{label}</span>
            </div>
            <span className='key-value'>{value} messages</span>
          </li>
          <li
            style={{
              display: 'flex',
              justifyContent: 'space-between',
              alignItems: 'center',
              marginTop: '4px',
              fontSize: '11px',
              color: '#666',
            }}
          >
            <span>Percentage:</span>
            <span>{percentage}%</span>
          </li>
        </ul>
      </div>
    );
  }
}
