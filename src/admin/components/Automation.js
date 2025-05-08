import { __ } from '@wordpress/i18n';

import Layout from './components/Layout';
import AccordionItemStatus from './automations/AccordionItemStatus';
import AutomationSettingsDetail from './automations/AutomationSettingsDetail';

const Automation = () => {
  // Array of WordPress order statuses with their details
  const orderStatuses = [
    {
        key: 'processing',
        title: 'Processing',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#17a34a', 
    },
    {
        key: 'completed',
        title: 'Completed',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#365aed', 
    },
    {
        key: 'failed',
        title: 'Failed',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44', 
    },
    {
        key: 'refunded',
        title: 'Refunded',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#6a6f7a', 
    }, 
    {
        key: 'pending_payment',
        title: 'Pending Payment',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#f90', 
    },
    {
        key: 'cancelled',
        title: 'Cancelled',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44',
    },
    {
        key: 'onhold',
        title: 'On Hold',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44',
    },
    {
        key: 'draft',
        title: 'Draft',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#17a34a', 
      }
  ];

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
            <div className='topsms-automation-status-wrap flex flex-col items-start self-stretch gap-2'>
                <div className='topsms-accordion-wrap border border-black/[0.07] flex flex-col gap-4 p-3 pr-4 w-full'>
                    {/* Map through the orderStatuses array to create an AccordionItemStatus for each */}
                    {orderStatuses.map((status) => (
                    <AccordionItemStatus
                        key={status.key}
                        title={status.title}
                        description={status.description}
                        statusKey={status.key}
                        statusColor={status.color}
                    >
                        <AutomationSettingsDetail status={status.title} />
                    </AccordionItemStatus>
                    ))}
                </div>
            </div>
        </div>
    </Layout>
  );
};

export default Automation;
