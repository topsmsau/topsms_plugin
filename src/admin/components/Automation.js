import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { Snackbar } from '@wordpress/components';

import Layout from './components/Layout';
import AccordionItemStatus from './automations/AccordionItemStatus';
import AutomationSettingsDetail from './automations/AutomationSettingsDetail';
import { ORDERSTATUSES } from './Constants';

const Automation = () => {
    // State for snackbar message
    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'

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
                        {/* Map through the order statuses array to create an AccordionItemStatus for each */}
                        {ORDERSTATUSES.map((status) => (
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
            </div>
        </Layout>
    );
};

export default Automation;