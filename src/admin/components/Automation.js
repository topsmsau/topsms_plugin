import { Button } from '@wordpress/components';
import Layout from './Layout';
import NoContent from './NoContent';
import SMSTextareaControl from './inputs/SMSTextareaControl';
import AccordionItemStatus from './inputs/AccordionItemStatus';

const Automation = () => {
  const noContentUrl =
    topsmsData.pluginUrl + 'public/assets/images/dot-content.svg';

  return (
    <Layout>
      <div className='page-title-detail'>
        <h4>Automation Settings</h4>
        <span>Configure SMS notifications for different order statuses</span>
      </div>
      <div className='page-details'>
        <div className='topsms-automation-status-wrap'>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Processing'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='processing'
            >
              <>
                <hr />
                <div className='topsms-accordion-body'>
                  <div className='settings-breadcrumbs'>
                    <span>Automation Setting</span>
                    <svg
                      xmlns='http://www.w3.org/2000/svg'
                      width='16'
                      height='17'
                      viewBox='0 0 16 17'
                      fill='none'
                    >
                      <path
                        d='M9.88712 8.02667L7.06045 5.20001C6.99847 5.13752 6.92474 5.08792 6.8435 5.05408C6.76226 5.02023 6.67512 5.00281 6.58712 5.00281C6.49911 5.00281 6.41197 5.02023 6.33073 5.05408C6.24949 5.08792 6.17576 5.13752 6.11378 5.20001C5.98962 5.32491 5.91992 5.49388 5.91992 5.67001C5.91992 5.84613 5.98962 6.0151 6.11378 6.14001L8.47378 8.50001L6.11378 10.86C5.98962 10.9849 5.91992 11.1539 5.91992 11.33C5.91992 11.5061 5.98962 11.6751 6.11378 11.8C6.17608 11.8618 6.24995 11.9107 6.33118 11.9439C6.4124 11.977 6.49938 11.9938 6.58712 11.9933C6.67485 11.9938 6.76183 11.977 6.84305 11.9439C6.92428 11.9107 6.99816 11.8618 7.06045 11.8L9.88712 8.97334C9.9496 8.91136 9.9992 8.83763 10.033 8.75639C10.0669 8.67515 10.0843 8.58801 10.0843 8.50001C10.0843 8.412 10.0669 8.32486 10.033 8.24362C9.9992 8.16238 9.9496 8.08865 9.88712 8.02667Z'
                        fill='#525866'
                      />
                    </svg>
                    <span className='current'>Detail</span>
                  </div>
                  <div className='smstemplate-preview-wrap'>
                    <div className='smstemplate-wrap'>
                      <div className='smstemplate-detail'>
                        <div className='template-title'>
                          <h4>SMS Template</h4>
                          <span>
                            Customize the message sent when an order status
                            changes to Failed
                          </span>
                        </div>
                        <div className='template-inputs-wrap'>
                          <div className='template-input'>
                            {/* <textarea className="template-input-field" placeholder="Type your message here...">Hello [f_name], there was an issue processing your order with ID [id]. Please contact us for assistance.</textarea> */}
                            <SMSTextareaControl />
                          </div>
                          <div className='template-prefields-name'>
                            <span>[id]</span>
                            <span>[f_name]</span>
                            <span>[l_name]</span>
                            <span>[order_date]</span>
                            <span>[pickup_date]</span>
                            <span>[pickup_slot]</span>
                          </div>
                        </div>
                      </div>
                      <div className='smstemplate-actions'>
                        <Button variant='secondary' className='reset-btn'>
                          Reset to Default
                        </Button>
                        <Button variant='primary' className='save-settings-btn'>
                          Save Settings
                        </Button>
                        {/* <a href="javascript:;" className="reset-btn">Reset to Default</a>
												<a href="javascript:;" className="save-settings-btn">Save Settings</a> */}
                      </div>
                    </div>
                    <div className='live-preview-wrap'>
                      <div className='live-smstemplate-detail'>
                        <div className='template-title'>
                          <h4>Live Preview</h4>
                          <span>How your message will appear</span>
                        </div>
                        <div className='live-preview-data'>
                          <div className='template-screen'>
                            <img src={noContentUrl} alt='Dot Content' />
                          </div>
                          <div className='smstemplate-data'>
                            <div className='template-content'>
                              <span>
                                Hello [f_name], there was an issue processing
                                your order with ID [id]. Please contact us for
                                assistance.
                              </span>
                            </div>
                            <div className='template-content-count'>
                              <span>Characters: 352</span>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Completed'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='completed'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Failed'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='failed'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Refunded'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='refunded'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Pending Payment'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='pendingpayment'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Cancelled'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='cancelled'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='On Hold'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='onhold'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
          <div className='topsms-accordion-wrap'>
            <AccordionItemStatus
              title='Draft'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='draft'
            >
              <>
                <NoContent />
              </>
            </AccordionItemStatus>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Automation;
