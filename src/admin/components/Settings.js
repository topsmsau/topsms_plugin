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

import BannerIcon1 from './icons/AutomationBannerIcon1.svg';
import BannerIcon2 from './icons/AutomationBannerIcon2.svg';
import BannerIcon3 from './icons/AutomationBannerIcon3.svg';

const Settings = () => {
    const [selectedAmount, setSelectedAmount] = useState(null);

    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'

    // List of top-up options
    const topUpOptions = [
        { 
            amount: 45, 
            sms: 500,
            discount: null,
            link: 'https://buy.stripe.com/6oE5kZc3c3lI2Aw3cc' 
        },
        { 
            amount: 225, 
            sms: 2500,
            discount: null,
            link: 'https://buy.stripe.com/28ofZD3wG4pMb72eUV' 
        },
        { 
            amount: 400, 
            sms: 5000,
            discount: '11%',
            link: 'https://buy.stripe.com/28oeVz0ku8G2a2Y9AC' 
        },
        { 
            amount: 700, 
            sms: 10000,
            discount: '22%',
            link: 'https://buy.stripe.com/14k7t71oy3lIejeeUX' 
        },
        { 
            amount: 1500, 
            sms: 50000,
            discount: '33%',
            link: 'https://buy.stripe.com/7sI9Bfc3c8G21wsdQU' 
        },
        { 
            amount: 3000, 
            sms: 100000,
            discount: '44%',
            link: 'https://buy.stripe.com/dRm14m88g4SUdu91tbbQY06' 
        },
    ];

    // Review cards data
    const reviewCards = [
        {
            icon: BannerIcon1,
            title: 'Enjoying TopSMS?',
            message: "Don't forget to leave us a review — your feedback helps us grow!", 
            buttonText: 'Leave a review',
            link: 'https://wordpress.org/plugins/topsms/#reviews'
        },
        {
            icon: BannerIcon2,
            title: 'Got ideas for new features?',
            message: "Help shape the future of TopSMS by voting or suggesting new features.", 
            buttonText: 'Request a feature',
            link: 'https://topsms.canny.io/'
        },
        {
            icon: BannerIcon3,
            title: 'Need something tailored to your business?',
            message: "We offer custom development services to make TopSMS work exactly how you need it.", 
            buttonText: 'Customisation services',
            link: 'https://eux.com.au/contact-us/'

        }
    ];
    
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
                                <p variant='muted' className="text-xs text-gray-600 mt-2">
                                    <span className="font-bold">Important: </span>
                                    {__('Please use the same email address and phone number you used when registering your account. This ensures your top-up is correctly linked to your balance. If the details don’t match, the credit may not be applied automatically.', 'topsms')}
                                </p>
                            </div>

                            {/* Top Up Options */}
                            <Flex justify="space-between">
                                {topUpOptions.map(({ amount, sms, discount, link }) => (
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
                {reviewCards.map((card, index) => (
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