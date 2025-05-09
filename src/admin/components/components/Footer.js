const Footer = () => {
  return (
    <footer className='topsms-footer-wrap'>
      <div className='topsms-footer-innerwrap flex justify-between'>
        <span className='copy-right-text'>
          ©2025 TopSMS All Right Reserved
        </span>
        <div className='topsms-contact-info flex justify-between gap-[2rem]'>
          <a href='mailto:support@topsms.com.au'>support@topsms.com.au</a>
          <a href='tel:610291216234'>+61 (0) 2 9121 6234</a>
        </div>
      </div>
    </footer>
  );
};

export default Footer;
