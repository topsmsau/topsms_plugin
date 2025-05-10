import { __ } from '@wordpress/i18n';

import Layout from './components/Layout';
import AccordionItemStatus from './automations/AccordionItemStatus';
import AutomationSettingsDetail from './automations/AutomationSettingsDetail';
import ReviewCard from './automations/ReviewCard';

import BannerIcon1 from './icons/AutomationBannerIcon1.svg';
import BannerIcon2 from './icons/AutomationBannerIcon2.svg';
import BannerIcon3 from './icons/AutomationBannerIcon3.svg';


const Automation = () => {
    // Array of WordPress order statuses with their details
    const orderStatuses = [
        {
            key: 'processing',
            title: 'Processing',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#17a34a', 
            defaultTemplate: "Hello [f_name], your order with ID [id] has been shipped and is on its way! 📦\nExpected delivery within 3-5 business days.\nIf you have any questions, feel free to contact us."
        },
        {
            key: 'completed',
            title: 'Completed',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#365aed', 
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        },
        {
            key: 'failed',
            title: 'Failed',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44', 
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        },
        {
            key: 'refunded',
            title: 'Refunded',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#6a6f7a', 
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        }, 
        {
            key: 'pending_payment',
            title: 'Pending Payment',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#f90', 
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        },
        {
            key: 'cancelled',
            title: 'Cancelled',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44',
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        },
        {
            key: 'onhold',
            title: 'On Hold',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44',
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture"
        },
        {
            key: 'draft',
            title: 'Draft',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#17a34a',
            defaultTemplate: "lorem ipsum dolor sit amet condecture lorem ipsum dolor sit amet condecture" 
        }
    ];

    // Review cards data
    const reviewCards = [
        {
            icon: BannerIcon1,
            title: 'Enjoying TopSMS?',
            message: "Don't forget to leave us a review — your feedback helps us grow!", 
            buttonText: 'Leave a review',
        },
        {
            icon: BannerIcon2,
            title: 'Request a feature',
            message: "Don't forget to leave us a review — your feedback helps us grow!", 
            buttonText: 'Leave a review',
        },
        {
            icon: BannerIcon3,
            title: 'Customisation Services',
            message: "Don't forget to leave us a review — your feedback helps us grow!", 
            buttonText: 'Leave a review',
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
                <div className='topsms-automation-status-wrap flex flex-col items-start self-stretch gap-1'>
                    <div className='topsms-accordion-wrap flex flex-col gap-3 p-3 pr-4 w-full'>
                        {/* Map through the orderStatuses array to create an AccordionItemStatus for each */}
                        {orderStatuses.map((status) => (
                        <AccordionItemStatus
                            key={status.key}
                            title={status.title}
                            description={status.description}
                            statusKey={status.key}
                            statusColor={status.color}
                        >
                            <AutomationSettingsDetail 
                                status={status.title} 
                                statusKey={status.key}
                                defaultTemplate={status.defaultTemplate}
                            />
                        </AccordionItemStatus>
                        ))}
                    </div>
                </div>

                {/* Review Cards */}
                <div className="grid grid-cols-3 gap-4 mb-4 pr-4 p-3">
                    {/* <ReviewCard
                        icon=''
                        title='Customisation Services'
                        message="Don't forget to leave us a review — your feedback helps us grow!"
                        buttonText='Leave a review'
                        /> */}
                    {reviewCards.map((card, index) => (
                        <ReviewCard
                            key={index}
                            icon={card.icon}
                            title={card.title}
                            message={card.message}
                            buttonText={card.buttonText}
                            className={'topsms-review-card'}
                        />
                    ))}
                </div>
            </div>
        </Layout>
    );
};

export default Automation;
