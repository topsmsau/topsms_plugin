import { useState, useEffect } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
    Snackbar
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

import Layout from './components/Layout';
import TopupBalanceButton from './settings/TopupBalanceButton';
import BalanceCard from './settings/BalanceCard';
import ReviewCard from './settings/ReviewCard';
import { TOPUPOPTIONS, REVIEWCARDS } from './Constants';

const Settings = () => {
    const [selectedAmount, setSelectedAmount] = useState(null);

    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'
    
    // Handle click event on top-up items
    const handleTopUpClick = (amount, link) => {
        // Update selected amount
        setSelectedAmount(amount);
        window.open(link, '_blank');
    };

    // Handle dismissing the snackbar
    const handleDismissSnackbar = () => {
        setShowSnackbar(false);
        setSnackbarMessage('');
    };

    // Handle success message from BalanceCard
    const handleSuccessMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('success');
        setShowSnackbar(true);
        
        // Auto-dismiss the snackbar after 3 seconds
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 3000);
    };
    
    // Handle error message from BalanceCard
    const handleErrorMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('error');
        setShowSnackbar(true);
        
        // Auto-dismiss the snackbar after 5 seconds (errors stay a bit longer)
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 5000);
    };
    
    // Clear the snackbar when the component unmounts
    useEffect(() => {
        return () => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        };
    }, []);

    const getPricePerSms = (smsAmount, smsCount) => {
        return (smsAmount / smsCount).toFixed(2);
    }

    // Format number to have a comma after thousands
    const formatNumber = (num) => {
    return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
    };

    return (
        <Layout>
            {/* Snackbar for messages - positioned at bottom left via CSS */}
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
                        {__('Settings', 'topsms')}
                    </h2>
                    <p className="text-gray-600">
                        {__('View and manage your SMS balance', 'topsms')}
                    </p>
                </div>
            </div>

            <div className='page-details'>
                {/* Pass both success and error message handlers to BalanceCard */}
                <BalanceCard 
                    onSuccessMessage={handleSuccessMessage} 
                    onErrorMessage={handleErrorMessage}
                />

                {/* Top Up Section */}
                <Card className="w-full">
                    <CardBody>
                        <Flex direction="column" gap={4}>
                            {/* Top Up Title */}
                            <div>
                                <h3 className="text-lg font-bold mb-1">
                                    {__('Top Up Your Balance', 'topsms')}
                                </h3>
                                <p className="text-sm text-gray-600">
                                    {__('Select an amount to add to your account', 'topsms')}
                                </p>
                                <p variant='muted' className="text-sm text-amber-600 mt-2">
                                    <span className="font-bold">Important: </span>
                                    {__('Please use the ') }
                                    <span className="font-bold">same email address and phone number you used when registering your account.</span>
                                    {__('This ensures your top-up is correctly linked to your balance. If the details don’t match, the credit may not be applied automatically.', 'topsms')}
                                </p>
                            </div>

                            {/* Top Up Options */}
                            <Flex justify="space-between">
                                {TOPUPOPTIONS.map(({ amount, sms, discount, link }) => (
                                    <TopupBalanceButton
                                        key={amount}
                                        isSelected={selectedAmount === amount}
                                        onClick={() => handleTopUpClick(amount, link)}
                                        discount={discount}
                                        className="w-[15%]"
                                    >
                                        <span className="text-xl font-bold mb-1">${formatNumber(amount)}</span>
                                        <span className="text-sm text-gray-600">{formatNumber(sms)} SMS</span>
                                        <span className="text-xs text-gray-600">{getPricePerSms(amount, sms)}c per SMS</span>
                                    </TopupBalanceButton>
                                ))}
                            </Flex>
                        </Flex>
                    </CardBody>
                </Card>
            </div>

            {/* Review Cards */}
            <div className="grid grid-cols-3 gap-4 mt-4 py-3">
                {/* <ReviewCard
                    icon=''
                    title='Customisation Services'
                    message="Don't forget to leave us a review — your feedback helps us grow!"
                    buttonText='Leave a review'
                    /> */}
                {REVIEWCARDS.map((card, index) => (
                    <ReviewCard
                        key={index}
                        icon={card.icon}
                        title={card.title}
                        link={card.link}
                        message={card.message}
                        buttonText={card.buttonText}
                        className={'topsms-review-card'}
                    />
                ))}
            </div>
        </Layout>
    );
};

export default Settings;