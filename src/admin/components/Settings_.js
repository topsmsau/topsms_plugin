import { useState } from '@wordpress/element';
import Layout from './components/Layout';
import StatusToggleControl from './inputs/StatusToggleControl';
import { Button } from '@wordpress/components';

const Settings = () => {
  // State for toggles
  const [statuses, setStatuses] = useState({
    lowbalancealert: false,
  });

  // State for active top-up selection and payment form visibility
  const [selectedAmount, setSelectedAmount] = useState(null);
  const [isPaymentVisible, setIsPaymentVisible] = useState(false);

  // List of top-up options
  const topUpOptions = [
    { amount: 45, sms: 500, link: 'https://buy.stripe.com/6oE5kZc3c3lI2Aw3cc' },
    {
      amount: 225,
      sms: 2500,
      link: 'https://buy.stripe.com/28ofZD3wG4pMb72eUV',
    },
    {
      amount: 400,
      sms: 5000,
      link: 'https://buy.stripe.com/28oeVz0ku8G2a2Y9AC',
    },
    {
      amount: 700,
      sms: 10000,
      link: 'https://buy.stripe.com/14k7t71oy3lIejeeUX',
    },
    {
      amount: 1500,
      sms: 50000,
      link: 'https://buy.stripe.com/7sI9Bfc3c8G21wsdQU',
    },
    {
      amount: 2500,
      sms: 100000,
      link: 'https://buy.stripe.com/bIYaFj6IS2hEeje8wB',
    },
  ];

  // Function to handle toggle changes
  const handleToggleChange = (key) => {
    setStatuses((prevState) => ({
      ...prevState,
      [key]: !prevState[key], // Toggle only the specific key
    }));
  };

  // Handle click event on top-up items
  const handleTopUpClick = (amount, link) => {
    setSelectedAmount(amount); // Update selected amount

    window.open(link, '_blank');
    // setIsPaymentVisible(true);  // Show the payment form
  };

  return (
    <Layout>
      <div className='page-title-detail'>
        <h4>Settings</h4>
        <span>View and manage your SMS balance</span>
      </div>
      <div className='page-details'>
        <div className='balance-wrapper'>
          <div className='current-balance-wrap'>
            <h4>Current balance</h4>
            <span className='totalbalance'>$0.98</span>
            <span className='remainmessages'>
              Approximately <span class='highlight-text'>95 SMS</span> messages
              remaining
            </span>
          </div>
          <div className='low-balance-wrap'>
            <div className='low-balance-content'>
              <h4>Low Balance Alert</h4>
              <span>We'll notify you when your balance falls below $2.00</span>
            </div>
            <div className='low-balance-toggle'>
              <StatusToggleControl
                label=''
                value={statuses.lowbalancealert}
                onChange={() => handleToggleChange('lowbalancealert')}
              />
            </div>
          </div>
        </div>
        <div className='topup-balance-wrapper'>
          <div className='topup-amount-wrapper'>
            <div className='topup-amount-title'>
              <h4>Top Up Your Balance</h4>
              <span>Select an amount to add to your account</span>
            </div>
            <div className='topup-amount-list'>
              <div className='topup-amount-items'>
                {topUpOptions.map(({ amount, sms, link }) => (
                  <div
                    key={amount}
                    className={`topup-item ${
                      selectedAmount === amount ? 'active' : ''
                    }`}
                    onClick={() => handleTopUpClick(amount, link)}
                  >
                    <span className='topup-amount'>${amount}</span>
                    <span className='topup-message'>{sms} SMS</span>
                  </div>
                ))}
              </div>
            </div>
          </div>
          <div className='topup-payment-wrapper'>
            <div className='payment-details-wrap'>
              <div className='payment-details-title'>
                <h4>Payment Details</h4>
                <span>
                  <svg
                    xmlns='http://www.w3.org/2000/svg'
                    width='14'
                    height='14'
                    viewBox='0 0 14 14'
                    fill='none'
                  >
                    <path
                      d='M6.59349 12.6087C6.72264 12.684 6.78721 12.7217 6.87834 12.7412C6.94906 12.7564 7.05224 12.7564 7.12297 12.7412C7.2141 12.7217 7.27867 12.684 7.40781 12.6087C8.54417 11.9457 11.6673 9.86326 11.6673 7V4.78333C11.6673 4.15689 11.6673 3.84367 11.5708 3.62133C11.4726 3.39535 11.3748 3.27513 11.1736 3.13303C10.9755 2.99321 10.5873 2.91248 9.81098 2.75101C8.95529 2.57305 8.29815 2.25169 7.69737 1.78693C7.40928 1.56407 7.26524 1.45264 7.15251 1.42225C7.03357 1.39018 6.96773 1.39018 6.84879 1.42225C6.73607 1.45264 6.59202 1.56407 6.30393 1.78694C5.70316 2.25169 5.04602 2.57305 4.19033 2.75101C3.41395 2.91248 3.02577 2.99321 2.82774 3.13303C2.62648 3.27513 2.52869 3.39535 2.43055 3.62133C2.33398 3.84367 2.33398 4.15689 2.33398 4.78333V7C2.33398 9.86326 5.45713 11.9457 6.59349 12.6087Z'
                      stroke='#17A34A'
                      stroke-width='1.4'
                      stroke-linecap='round'
                      stroke-linejoin='round'
                    />
                  </svg>
                  Secure payment via Stripe
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Settings;
