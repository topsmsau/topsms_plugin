import { useState } from '@wordpress/element';
import Layout from './components/Layout';
import StatusToggleControl from './inputs/StatusToggleControl';
import PaymentForm from './PaymentForm';
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
              <div className='payment-card-option'>
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  width='73'
                  height='56'
                  viewBox='0 0 73 56'
                  fill='none'
                >
                  <g filter='url(#filter0_dd_3229_3778)'>
                    <rect
                      x='11'
                      y='6'
                      width='51'
                      height='34'
                      rx='6'
                      fill='white'
                    />
                    <rect
                      x='10.875'
                      y='5.875'
                      width='51.25'
                      height='34.25'
                      rx='6.125'
                      stroke='#D6DCE5'
                      stroke-width='0.25'
                    />
                    <path
                      d='M40.4211 15.9242H32.5793V30.0758H40.4211V15.9242Z'
                      fill='#FF5F00'
                    />
                    <path
                      d='M33.0773 23C33.076 21.6371 33.3836 20.2918 33.9766 19.0659C34.5696 17.84 35.4326 16.7657 36.5002 15.9242C35.1781 14.8806 33.5904 14.2316 31.9184 14.0514C30.2464 13.8712 28.5576 14.167 27.0451 14.9051C25.5326 15.6431 24.2574 16.7937 23.3653 18.2251C22.4731 19.6566 22 21.3113 22 23C22 24.6887 22.4731 26.3434 23.3653 27.7749C24.2574 29.2063 25.5326 30.3569 27.0451 31.0949C28.5576 31.833 30.2464 32.1288 31.9184 31.9486C33.5904 31.7684 35.1781 31.1194 36.5002 30.0758C35.4326 29.2343 34.5697 28.16 33.9766 26.9341C33.3836 25.7082 33.076 24.3629 33.0773 23Z'
                      fill='#EB001B'
                    />
                    <path
                      d='M51 23C51.0001 24.6887 50.527 26.3434 49.6349 27.7748C48.7428 29.2063 47.4676 30.3568 45.9552 31.0949C44.4427 31.833 42.754 32.1288 41.082 31.9486C39.41 31.7684 37.8223 31.1194 36.5002 30.0758C37.5669 29.2335 38.4292 28.159 39.0221 26.9333C39.6151 25.7076 39.9232 24.3627 39.9232 23C39.9232 21.6373 39.6151 20.2924 39.0221 19.0667C38.4292 17.841 37.5669 16.7665 36.5002 15.9242C37.8223 14.8806 39.41 14.2316 41.082 14.0514C42.754 13.8712 44.4427 14.167 45.9552 14.9051C47.4676 15.6432 48.7428 16.7937 49.6349 18.2252C50.527 19.6566 51.0001 21.3113 51 23Z'
                      fill='#F79E1B'
                    />
                    <path
                      d='M50.145 28.5769V28.2872H50.2613V28.2282H49.965V28.2872H50.0814V28.5769H50.145ZM50.7202 28.5769V28.2276H50.6294L50.5249 28.4679L50.4204 28.2276H50.3295V28.5769H50.3937V28.3134L50.4916 28.5406H50.5581L50.6561 28.3129V28.5769H50.7202Z'
                      fill='#F79E1B'
                    />
                  </g>
                  <defs>
                    <filter
                      id='filter0_dd_3229_3778'
                      x='0.75'
                      y='0.75'
                      width='71.5'
                      height='54.5'
                      filterUnits='userSpaceOnUse'
                      color-interpolation-filters='sRGB'
                    >
                      <feFlood flood-opacity='0' result='BackgroundImageFix' />
                      <feColorMatrix
                        in='SourceAlpha'
                        type='matrix'
                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
                        result='hardAlpha'
                      />
                      <feOffset dy='0.5' />
                      <feGaussianBlur stdDeviation='2.5' />
                      <feColorMatrix
                        type='matrix'
                        values='0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0.08 0'
                      />
                      <feBlend
                        mode='normal'
                        in2='BackgroundImageFix'
                        result='effect1_dropShadow_3229_3778'
                      />
                      <feColorMatrix
                        in='SourceAlpha'
                        type='matrix'
                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
                        result='hardAlpha'
                      />
                      <feOffset dy='5' />
                      <feGaussianBlur stdDeviation='5' />
                      <feColorMatrix
                        type='matrix'
                        values='0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0.08 0'
                      />
                      <feBlend
                        mode='normal'
                        in2='effect1_dropShadow_3229_3778'
                        result='effect2_dropShadow_3229_3778'
                      />
                      <feBlend
                        mode='normal'
                        in='SourceGraphic'
                        in2='effect2_dropShadow_3229_3778'
                        result='shape'
                      />
                    </filter>
                  </defs>
                </svg>
                <svg
                  xmlns='http://www.w3.org/2000/svg'
                  width='73'
                  height='56'
                  viewBox='0 0 73 56'
                  fill='none'
                >
                  <g filter='url(#filter0_dd_3229_3782)'>
                    <rect
                      x='11'
                      y='6'
                      width='51'
                      height='34'
                      rx='6'
                      fill='white'
                    />
                    <rect
                      x='10.875'
                      y='5.875'
                      width='51.25'
                      height='34.25'
                      rx='6.125'
                      stroke='#D6DCE5'
                      stroke-width='0.25'
                    />
                    <path
                      d='M36.1875 17.4592L33.8087 28.9662H30.9322L33.3114 17.4592H36.1875ZM48.2899 24.8893L49.8043 20.568L50.6757 24.8893H48.2899ZM51.4993 28.9662H54.16L51.8382 17.4592H49.3823C48.8305 17.4592 48.3649 17.7913 48.1576 18.3034L43.842 28.9662H46.8627L47.4625 27.2479H51.153L51.4993 28.9662ZM43.9919 25.2091C44.0042 22.172 39.9333 22.0048 39.9614 20.6481C39.9699 20.2348 40.3503 19.7963 41.1813 19.6837C41.5941 19.628 42.7291 19.5854 44.018 20.1991L44.5226 17.7589C43.8304 17.4989 42.9396 17.2487 41.8317 17.2487C38.9886 17.2487 36.9875 18.8129 36.9706 21.0528C36.9526 22.7092 38.3991 23.6335 39.4891 24.1842C40.6097 24.7484 40.9863 25.1097 40.9813 25.6141C40.974 26.3866 40.0874 26.7267 39.2603 26.7402C37.8138 26.7639 36.9744 26.3363 36.3054 26.0136L35.7841 28.535C36.456 28.8544 37.6966 29.1319 38.9837 29.1461C42.0052 29.1461 43.9821 27.6012 43.9919 25.2091ZM32.0771 17.4592L27.4166 28.9662H24.3754L22.0821 19.7828C21.9428 19.2172 21.8217 19.0103 21.3983 18.7718C20.7074 18.3839 19.5654 18.0193 18.5605 17.7935L18.6292 17.4592H23.5237C24.1474 17.4592 24.7087 17.8889 24.8502 18.6323L26.0616 25.2914L29.0549 17.4592H32.0771Z'
                      fill='#1434CB'
                    />
                  </g>
                  <defs>
                    <filter
                      id='filter0_dd_3229_3782'
                      x='0.75'
                      y='0.75'
                      width='71.5'
                      height='54.5'
                      filterUnits='userSpaceOnUse'
                      color-interpolation-filters='sRGB'
                    >
                      <feFlood flood-opacity='0' result='BackgroundImageFix' />
                      <feColorMatrix
                        in='SourceAlpha'
                        type='matrix'
                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
                        result='hardAlpha'
                      />
                      <feOffset dy='0.5' />
                      <feGaussianBlur stdDeviation='2.5' />
                      <feColorMatrix
                        type='matrix'
                        values='0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0.08 0'
                      />
                      <feBlend
                        mode='normal'
                        in2='BackgroundImageFix'
                        result='effect1_dropShadow_3229_3782'
                      />
                      <feColorMatrix
                        in='SourceAlpha'
                        type='matrix'
                        values='0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 0 127 0'
                        result='hardAlpha'
                      />
                      <feOffset dy='5' />
                      <feGaussianBlur stdDeviation='5' />
                      <feColorMatrix
                        type='matrix'
                        values='0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0 0.717647 0 0 0 0.08 0'
                      />
                      <feBlend
                        mode='normal'
                        in2='effect1_dropShadow_3229_3782'
                        result='effect2_dropShadow_3229_3782'
                      />
                      <feBlend
                        mode='normal'
                        in='SourceGraphic'
                        in2='effect2_dropShadow_3229_3782'
                        result='shape'
                      />
                    </filter>
                  </defs>
                </svg>
              </div>
            </div>
            {/* Show Payment Form when an amount is selected */}
            {isPaymentVisible && (
              <div className='card-details-wrap'>
                <PaymentForm />
              </div>
            )}
          </div>

          {/* Show Submit Button when an amount is selected */}
          {isPaymentVisible && (
            <div className='topup-payment-form-submit'>
              <Button isPrimary onClick={() => console.log('send for payment')}>
                Pay ${selectedAmount}
              </Button>
            </div>
          )}
        </div>
      </div>
    </Layout>
  );
};

export default Settings;
