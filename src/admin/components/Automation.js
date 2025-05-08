import { __ } from '@wordpress/i18n';

import Layout from './Layout';
import AccordionItemStatus from './inputs/AccordionItemStatus';
import AutomationSettingsDetail from './AutomationSettingsDetail';

const Automation = () => {
//   const noContentUrl =
//     topsmsData.pluginUrl + 'public/assets/images/dot-content.svg';

  return (
    <Layout>
      <div className='px-6 py-4'>
        <div className='mb-6'>
          <h2 className='text-2xl font-bold mb-1'>
            {__('Automation Settings', 'topsms')}
          </h2>
          <p className='text-gray-600'>
            {__(
              'Configure SMS notifications for different order statuses',
              'topsms'
            )}
          </p>
        </div>
      </div>
      <div className='page-details'>
        <div className='topsms-automation-status-wrap flex items-start self-stretch gap-2'>
          <div className='topsms-accordion-wrap border border-black/[0.07] flex flex-col gap-4 p-3 pr-4 w-full'>
            <AccordionItemStatus
              title='Processing'
              description='lorem ipsum dolor sit amet condecture'
              statusKey='processing'
            >
                <AutomationSettingsDetail status="Processing"/>
            </AccordionItemStatus>
          </div>
        </div>
      </div>
    </Layout>
  );
};

export default Automation;
