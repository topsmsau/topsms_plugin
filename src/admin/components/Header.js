const Header = () => {
  const logoUrl = topsmsData.pluginUrl + 'public/assets/images/topsms-logo.svg';

  return (
    <header className='topsms-header-wrap'>
      <div className='topsms-header-innerwrap'>
        <div className='topsms-header-logo'>
          <img src={logoUrl} alt='TopSMS Logo' width='48' height='48' />
        </div>
        <div className='topsms-details'>
          <h1>TopSMS</h1>
          <span>Configure automated SMS notifications for your customers</span>
        </div>
        <div className='topsms-balance-wrap'>
          <div className='topsms-balance-details'>
            <div className='topsms-balance'>
              <h4>Current balance</h4>
              <span>$0.98</span>
            </div>
            <div className='topsms-topup'>
              <svg
                xmlns='http://www.w3.org/2000/svg'
                width='16'
                height='16'
                viewBox='0 0 16 16'
                fill='none'
              >
                <path
                  d='M8.66602 3.33333C8.66602 2.96514 8.36754 2.66667 7.99935 2.66667C7.63116 2.66667 7.33268 2.96514 7.33268 3.33333V7.33333H3.33268C2.96449 7.33333 2.66602 7.63181 2.66602 8C2.66602 8.36819 2.96449 8.66667 3.33268 8.66667H7.33268V12.6667C7.33268 13.0349 7.63116 13.3333 7.99935 13.3333C8.36754 13.3333 8.66602 13.0349 8.66602 12.6667V8.66667H12.666C13.0342 8.66667 13.3327 8.36819 13.3327 8C13.3327 7.63181 13.0342 7.33333 12.666 7.33333H8.66602V3.33333Z'
                  fill='#525866'
                />
              </svg>
              <a href='javascript:;' className='topsms-topup-btn'>
                Top up
              </a>
            </div>
          </div>
          <div className='topsms-message-wrap'>
            <span>
              Approximately <span className='highlight-text'>95 SMS</span>{' '}
              messages remaining
            </span>
          </div>
        </div>
      </div>
    </header>
  );
};

export default Header;
