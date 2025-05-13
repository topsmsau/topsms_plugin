import { useState, useEffect } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';


import Layout from './components/Layout';
import TopupBalanceButton from './settings/TopupBalanceButton';
import BalanceCard from './settings/BalanceCard';

const Settings = () => {
    const [selectedAmount, setSelectedAmount] = useState(null);

    const [successMessage, setSuccessMessage] = useState('');
    const [showSuccessNotice, setShowSuccessNotice] = useState(false);

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
            amount: 2500, 
            sms: 100000,
            discount: '44%',
            link: 'https://buy.stripe.com/bIYaFj6IS2hEeje8wB' 
        },
    ];
    
    // Handle click event on top-up items
    const handleTopUpClick = (amount, link) => {
        // Update selected amount
        setSelectedAmount(amount);
        window.open(link, '_blank');
    };

    // Handle dismissing the success message
    const handleDismissSuccess = () => {
        setShowSuccessNotice(false);
        setSuccessMessage('');
    };

    // Handle success message from BalanceCard
    const handleSuccessMessage = (message) => {
        setSuccessMessage(message);
        setShowSuccessNotice(true);
        
        // Auto-dismiss the success message after 3 seconds
        setTimeout(() => {
            setShowSuccessNotice(false);
            setSuccessMessage('');
        }, 3000);
    };
    
    // Clear the notice when the component unmounts
    useEffect(() => {
        return () => {
            setShowSuccessNotice(false);
            setSuccessMessage('');
        };
    }, []);

    const getPricePerSms = (smsAmount, smsCount) => {
        return (smsAmount / smsCount);
    }

    return (
        <Layout>
            {/* Global success notice - at the top of the page */}
            {showSuccessNotice && successMessage && (
                <Notice 
                    status="success" 
                    isDismissible={true} 
                    onRemove={handleDismissSuccess}
                    className="mb-4"
                >
                    {successMessage}
                </Notice>
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
                {/* Pass the success message handler to BalanceCard */}
                <BalanceCard onSuccessMessage={handleSuccessMessage} />

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
                                        <span className="text-xl font-bold mb-1">${amount}</span>
                                        <span className="text-sm text-gray-600">{sms} SMS</span>
                                        <span className="text-xs text-gray-600">{getPricePerSms(amount, sms)}c per SMS</span>
                                    </TopupBalanceButton>
                                ))}
                            </Flex>
                        </Flex>
                    </CardBody>
                </Card>
            </div>
        </Layout>
    );
};

export default Settings;