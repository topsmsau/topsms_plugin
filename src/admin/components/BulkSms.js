import { __ } from '@wordpress/i18n';
import { useState, useEffect, useCallback } from '@wordpress/element';
import { 
    Snackbar, 
    Card, 
    CardBody, 
    Button, 
    Spinner
} from '@wordpress/components';

import Layout from './components/Layout';
import BulkSmsForm from './bulksms/BulkSmsForm.js';
import SmsPreview from './bulksms/SmsPreview.js';
import CampaignScheduler from './bulksms/CampaignScheduler.js';
import TestMessage from './bulksms/TestMessage.js';
import CampaignFinalStep from './bulksms/FinalStep.js';
import { COST_PER_SMS } from './Constants.js';

const BulkSms = () => {
    // Get campaign data from the localised script
    const campaignData = window.topsmsNonce?.campaignData || null;
    const [campaignId, setCampaignId] = useState(campaignData?.id || 0);

    const [formData, setFormData] = useState({
        campaignName: campaignData?.campaign_name || '',
        list: campaignData?.list || '',
        sender: campaignData?.sender || '',
        smsMessage: campaignData?.message || '',
        url: campaignData?.url || '',
    });

    const [characterCount, setCharacterCount] = useState(0);
    const [smsCount, setSmsCount] = useState(0);
    const [contactsCount, setContactsCount] = useState(0);
    const [currentSmsBalance, setCurrentSmsBalance] = useState(0);

    // Campaign schedule and datetime
    const [enabledScheduled, setEnabledScheduled] = useState(campaignData?.action === 'schedule' || false);
    const [selectedDate, setSelectedDate] = useState(() => {
        if (campaignData?.campaign_datetime) {
            const dateTime = new Date(campaignData.campaign_datetime);
            return dateTime.toISOString().split('T')[0];
        }
        return null;
    });
    const [selectedTime, setSelectedTime] = useState(() => {
        if (campaignData?.campaign_datetime) {
            const dateTime = new Date(campaignData.campaign_datetime);
            return dateTime.toTimeString().split(' ')[0];
        }
        return '00:00:00';
    });

    const [isSaving, setIsSaving] = useState(false);
    const [errors, setErrors] = useState({});
    const [listLoading, setListLoading] = useState(false);
    const [campaignSuccess, setCampaignSuccess] = useState(false);

    // State for snackbar message
    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'

    // Handle toggle change
    const handleToggleChange = () => {
        const newValue = !enabledScheduled;

        // Update local state immediately
        setEnabledScheduled(newValue);
    };

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

    // Validate campaign form before sending
    const validateForm = () => {
        const newErrors = {};

        // Check if campaign is not empty
        if (!formData.campaignName) {
            newErrors.campaignName = __('Campaign name is required', 'topsms');
            setErrors(newErrors);
            return false;
        }
        
        // Check if list is selected 
        if (!formData.list) {
            newErrors.list = __('List is required', 'topsms');
            setErrors(newErrors);
            return false;
        }

        // Check if message is not empty
        if (!formData.smsMessage || formData.smsMessage.trim() === '') {
            handleErrorMessage(__('Message cannot be empty', 'topsms'));
            return false;
        }

        // Check if unsubscribe tag is present
        if (!formData.smsMessage.includes('[unsubscribe]')) {
            handleErrorMessage(__('The [unsubscribe] tag must be included in the message', 'topsms'));
            return false;
        }
        
        // Check if url is provided but the [url] tag is missing
        if (formData.url && formData.url.trim() !== '' && !formData.smsMessage.includes('[url]')) {
            handleErrorMessage(__('The [url] tag must be included in the message when a URL is provided', 'topsms'));
            return false;
        }

        return true;
    };

    const checkCurrentBalance = async () => {
        // Fetch user current balance
        await fetchCurrentBalance();

        if (currentSmsBalance < totalSmsCount) {
            handleErrorMessage(__("You don't have enough balance, please head over to the settings page to recharge", 'topsms'));
            return false;
        }
        return true;
    }

    // Validate on submit
    const handleSubmit = async (e) => {
        e?.preventDefault();

        // Validate the data
        if (!validateForm()) {
            return;
        }

        const currentTotalSmsCount = smsCount * contactsCount;
        // Check if there's sms to be sent
        if (currentTotalSmsCount < 1) {
            handleErrorMessage(__("You will need at least 1 SMS to send or schedule a campaign", 'topsms'));
            return false;
        }

        // Check if enough sms balance
        const checkBalance = await checkCurrentBalance();
        if (!checkBalance) {
            return;
        }

        // Schedule campaign from backend
        await scheduleCampaign();

    };


    // Fetch user current balance
    const fetchCurrentBalance = async () => {
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }

            // Fetch user data from backend
            const response = await fetch('/wp-json/topsms/v1/user', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Get the user balance
            const balance_ = data.data.data.balance;
            setCurrentSmsBalance(balance_);
        } catch (error) {
            console.error('Error fetching user balance:', error);
            
            // Notify error
            handleErrorMessage(__('Failed to load user balance. Please refresh and try again.', 'topsms'));
        }
    }

    // Calculate total sms count, total cost and remaining sms balance of the campaign
    const totalSmsCount = smsCount * contactsCount;
    const totalCost = (smsCount * contactsCount * COST_PER_SMS).toFixed(2);
    const remainingSmsBalance = currentSmsBalance - totalSmsCount;

    // Schedule campaign / send instantly
    const scheduleCampaign = async () => {
        setIsSaving(true);
        setCampaignSuccess(false);

        try {
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Combine selected selected datetime (if schedule enabled)
            let scheduledDateTime;
            if (enabledScheduled && selectedDate && selectedTime) {
                console.log("selected date:", selectedDate);
                console.log("selected time:", selectedTime);

                scheduledDateTime = `${selectedDate}T${selectedTime}`; 
            } else {
                // Send empty data for datetime if schedule disabled
                scheduledDateTime = ''
            }

            // Campaign data (form data)
            const newData = {
                campaign_name: formData.campaignName || '',
                list: formData.list || '',
                sender: formData.sender || '',
                message: formData.smsMessage || '',
                url: formData.url || ''
            }

            const sendData = {
                is_scheduled: enabledScheduled,
                datetime: scheduledDateTime,
                campaign_data: newData,
                cost: totalSmsCount,
                campaign_id: campaignId || '', 
            };

            const response = await fetch('/wp-json/topsms/v2/bulksms/schedule-campaign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            setCampaignSuccess(true);
        } catch (err) {
            const errorMessage = enabledScheduled 
                ? __('Failed to schedule campaign', 'topsms')
                : __('Failed to send campaign', 'topsms');

            console.error('Error scheduling/sending campaign:', err);
            handleErrorMessage(err.message || errorMessage);
        } finally {
            setIsSaving(false);
        }
    };

    // Clear transient on the backend.
    const clearTransient = useCallback(async (useBeacon = false) => {
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            if (useBeacon) {
                // Use sendBeacon for page unload
                // When a user closes a tab or navigates away, the browser may cancel pending HTTP requests (like fetch or XMLHttpRequest - the requests may not be sent
                // Used sendBeacon():
                // 1. Guarantee the request will be sent even if the page is closing
                // 2. The data is transmitted asynchronously without delaying unload or the next navigation
                // Use Blob as sendBeacon can't send headers
                // To ensure correct content type
                const sent = navigator.sendBeacon(
                    '/wp-json/topsms/v2/bulksms/clear-transient',
                    new Blob(
                        [JSON.stringify({})], 
                        { type: 'application/json' }
                    ) 
                );

                // sendBeacon doesn't return response data, only success/failure boolean
                // console.log('response:', sent ? 'Success' : 'Failed');
            } else {
                // Delete transient on the backend
                const response  = await fetch('/wp-json/topsms/v2/bulksms/clear-transient', {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-WP-Nonce': nonce
                    },
                });

                // // Check if success
                // if (!response.ok) {
                //     const errorData = await response.json().catch(() => null);
                //     throw new Error(
                //         errorData?.message || 
                //         `Failed to save status settings: ${response.status}`
                //     );
                // }

                const data = await response.json();

                if (!data.success) {
                    throw new Error(data.data.message || 'Unknown error');
                }

                // console.log('Transient cleared success:', data.data.message);
            }
        } catch (error) {
            console.error('Error clearing transient:', error);
        }
    }, []);

    // Clear transient when component unmounts
    useEffect(() => {
        return () => {
            // Use normal fetch
            clearTransient(false);
        };
    }, [clearTransient]);

    // Clear transient on page unload/refresh
    useEffect(() => {
        const handleBeforeUnload = () => {
            // Use sendBeacon
            clearTransient(true);
        };

        // Clear before the page unload
        window.addEventListener('beforeunload', handleBeforeUnload);
        return () => {
            window.removeEventListener('beforeunload', handleBeforeUnload);
        };
    }, [clearTransient]);

    const saveDraft = async () => {
        setIsSaving(true);

        try {
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Combine selected datetime (if schedule enabled)
            let scheduledDateTime = '';
            if (enabledScheduled && selectedDate && selectedTime) {
                scheduledDateTime = `${selectedDate}T${selectedTime}`;
            }

            // Campaign data (form data)
            const campaignData = {
                campaign_name: formData.campaignName || '',
                list: formData.list || '',
                sender: formData.sender || '',
                message: formData.smsMessage || '',
                url: formData.url || ''
            };

            const sendData = {
                campaign_id: campaignId, 
                is_scheduled: enabledScheduled,
                datetime: scheduledDateTime,
                campaign_data: campaignData,
            };

            const response = await fetch('/wp-json/topsms/v2/bulksms/save-campaign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Draft saved successfully
            handleSuccessMessage(data.data.message || __('Campaign draft saved successfully', 'topsms'));
            
            // Optionally store the campaign_id for future updates
            // setExistingCampaignId(data.data.campaign_id);
        } catch (err) {
            console.error('Error saving draft:', err);
            handleErrorMessage(`${err.message}. Please try again.` || __('Failed to save campaign draft. Please try again.', 'topsms'));
        } finally {
            setIsSaving(false);
        }
    }

    // useEffect(() => {
    //     if (campaignData && campaignData.message) {
    //         setCharacterCount(campaignData.message.length);
    //     }
    // }, [campaignData]);

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
                        {__('Bulk SMS', 'topsms')}
                    </h2>
                    <p className="text-gray-600">
                        {__('Please enter your camapaign details below', 'topsms')}
                    </p>
                </div>
            </div>

            <div className='page-details'>
                {/* If campaign sent successfully, show final step */}
                {campaignSuccess ? (
                    <CampaignFinalStep 
                        isScheduled={enabledScheduled}
                        scheduledDate={selectedDate}
                        scheduledTime={selectedTime}
                    />
                ) : (
                    <Card className="mb-6 w-full shadow-none">
                        <CardBody>
                            <div className="bulksms-section">
                                <div className="flex flex-wrap -mx-4 items-start">
                                    {/* Left section */}
                                    <div className="w-full lg:w-1/2 px-8 mb-6">
                                        <div className="mb-[44px]">
                                            {/* Form fields */}
                                            <BulkSmsForm 
                                                formData={formData}
                                                setFormData={setFormData}
                                                setListLoading={setListLoading}
                                                characterCount={characterCount}
                                                setCharacterCount={setCharacterCount}
                                                smsCount={smsCount}
                                                setSmsCount={setSmsCount}
                                                errors={errors}
                                                setCurrentSmsBalance={setCurrentSmsBalance}
                                                setContactsCount={setContactsCount}
                                                onErrorMessage={handleErrorMessage}
                                            />
                                        </div>

                                        <hr className="border-gray-200 mb-[44px]" />

                                        <div className='bulksms-schedule-campaign'>
                                            <CampaignScheduler 
                                                enabledScheduled={enabledScheduled}
                                                totalSms={totalSmsCount}
                                                smsCount={smsCount}
                                                characterCount={characterCount}
                                                contactsCount={contactsCount}
                                                costPerSms={COST_PER_SMS}
                                                totalCost={totalCost}
                                                currentSmsBalance={currentSmsBalance}
                                                remainingSmsBalance={remainingSmsBalance}
                                                listLoading={listLoading}
                                                handleToggleChange={handleToggleChange}
                                                onErrorMessage={handleErrorMessage}
                                                selectedDate={selectedDate}
                                                setSelectedDate={setSelectedDate}
                                                selectedTime={selectedTime}
                                                setSelectedTime={setSelectedTime}
                                            />
                                        </div>

                                        {/* Action Buttons */}
                                        <div className="flex justify-end items-center mt-8 gap-[4px]">
                                            <Button 
                                                variant="primary" 
                                                className="bulksms-schedule-button px-6 py-2 bg-blue-500 text-white rounded-full"
                                                onClick={handleSubmit}
                                                disabled={isSaving}
                                            >
                                                {isSaving && <Spinner style={{ margin: '0 8px 0 0' }} />}
                                                {isSaving 
                                                    ? (enabledScheduled ? __('Scheduling...', 'topsms') : __('Sending...', 'topsms'))
                                                    : (enabledScheduled ? __('Schedule Campaign', 'topsms') : __('Send Campaign', 'topsms'))
                                                }
                                            </Button>
                                            <Button 
                                                variant="secondary" 
                                                className="bulksms-save-draft-schedule-button px-6 py-2 border border-gray-300 rounded-full"
                                                onClick={saveDraft}
                                                disabled={isSaving}
                                            >
                                                {__('Save Draft', 'topsms')}
                                            </Button>
                                        </div>
                                    </div>

                                    {/* Right section - preview */}
                                    <div className="bulksms-preview w-full lg:w-1/2 px-8 mb-6 flex justify-center flex-col items-start">
                                        <div className="bg-gray-100 rounded-[20px] pt-8 w-full flex justify-center items-start">
                                            <SmsPreview 
                                                sender={formData.sender}
                                                smsMessage={formData.smsMessage}
                                            />
                                        </div>

                                        <TestMessage 
                                            message={formData.smsMessage} 
                                            sender={formData.sender}
                                            url={formData.url}
                                            onSuccessMessage={handleSuccessMessage}
                                            onErrorMessage={handleErrorMessage}
                                        />
                                    </div>
                                </div>
                            </div>
                        </CardBody>
                    </Card>
                )}
            </div>
        </Layout>
    );
};

export default BulkSms;