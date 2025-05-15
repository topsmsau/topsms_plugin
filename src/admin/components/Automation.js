import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Snackbar } from '@wordpress/components';

import Layout from './components/Layout';
import AccordionItemStatus from './automations/AccordionItemStatus';
import AutomationSettingsDetail from './automations/AutomationSettingsDetail';
import ReviewCard from './automations/ReviewCard';

import BannerIcon1 from './icons/AutomationBannerIcon1.svg';
import BannerIcon2 from './icons/AutomationBannerIcon2.svg';
import BannerIcon3 from './icons/AutomationBannerIcon3.svg';


const Automation = () => {
    // State for snackbar message
    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'

    // Array of WordPress order statuses with their details
    const orderStatuses = [
        {
            key: 'processing',
            title: 'Processing',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#17a34a', 
            defaultTemplate: "Hello [first_name], your order #[order_id] has been shipped and is on its way! Expected delivery within 3-5 business days. If you have any questions, feel free to contact us."
        },
        {
            key: 'completed',
            title: 'Completed',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#365aed', 
            defaultTemplate: "Hello [first_name], your order #[order_id] has been successfully delivered. We hope you enjoy your purchase! Thank you for shopping with us."
        },
        {
            key: 'failed',
            title: 'Failed',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44', 
            defaultTemplate: "Hello [first_name], unfortunately, your order #[order_id] could not be processed due to a payment issue. Please try again or contact us for help."
        },
        {
            key: 'refunded',
            title: 'Refunded',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#6a6f7a', 
            defaultTemplate: "Hello [first_name], your order #[order_id] has been refunded. The amount should reflect in your account shortly. Let us know if you have any questions."
        }, 
        {
            key: 'pending',
            title: 'Pending Payment',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#f90', 
            defaultTemplate: "Hello [first_name], your order #[order_id] is awaiting payment. Please complete the payment to process your order. Contact us if you need assistance."
        },
        {
            key: 'cancelled',
            title: 'Cancelled',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44',
            defaultTemplate: "Hello [first_name], your order #[order_id] has been cancelled. If this was a mistake or you need help placing a new order, feel free to reach out."
        },
        {
            key: 'on-hold',
            title: 'On Hold',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#ff3a44',
            defaultTemplate: "Hello [first_name], your order #[order_id] is currently on hold. We'll notify you as soon as it's updated. Contact us if you need more information."
        },
        {
            key: 'draft',
            title: 'Draft',
            description: 'lorem ipsum dolor sit amet condecture',
            color: '#17a34a',
            defaultTemplate: "" 
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
            title: 'Got ideas for new features?',
            message: "Help shape the future of TopSMS by voting or suggesting new features.", 
            buttonText: 'Request a feature',
        },
        {
            icon: BannerIcon3,
            title: 'Need something tailored to your business?',
            message: "We offer custom development services to make TopSMS work exactly how you need it.", 
            buttonText: 'Customisation services',
        }
    ];

    // Handle success message from components
    const handleSuccessMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('success');
        setShowSnackbar(true);
        
        // Auto-dismiss the success message after 3 seconds
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 3000);
    };
    
    // Handle error message from components
    const handleErrorMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('error');
        setShowSnackbar(true);
        
        // Auto-dismiss the error message after 5 seconds
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 5000);
    };
    
    // Handle dismissing the snackbar
    const handleDismissSnackbar = () => {
        setShowSnackbar(false);
        setSnackbarMessage('');
    };

    return (
        <Layout>
            {/* Snackbar for success/error messages - positioned at bottom left via CSS */}
            {showSnackbar && (
                <Snackbar 
                    onDismiss={handleDismissSnackbar}
                    className={`topsms-snackbar ${snackbarStatus === 'error' ? 'topsms-snackbar-error' : snackbarStatus === 'info' ? 'topsms-snackbar-info' : ''}`}
                >
                    {snackbarMessage}
                </Snackbar>
            )}
                
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
                            onSuccessMessage={handleSuccessMessage}
                            onErrorMessage={handleErrorMessage}
                        >
                            <AutomationSettingsDetail 
                                status={status.title} 
                                statusKey={status.key}
                                defaultTemplate={status.defaultTemplate}
                                onSuccessMessage={handleSuccessMessage}
                                onErrorMessage={handleErrorMessage}
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