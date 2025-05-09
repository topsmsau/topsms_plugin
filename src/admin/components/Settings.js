import { useState } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';


import Layout from './components/Layout';
import TopupBalanceButton from './settings/TopupBalanceButton';
import BalanceCard from './settings/BalanceCard';

const Settings = () => {
    // State for active top-up selection and payment form visibility
    const [selectedAmount, setSelectedAmount] = useState(null);

    // List of top-up options
    const topUpOptions = [
        { 
            amount: 45, 
            sms: 500, 
            link: 'https://buy.stripe.com/6oE5kZc3c3lI2Aw3cc' 
        },
        { 
            amount: 225, 
            sms: 2500, 
            link: 'https://buy.stripe.com/28ofZD3wG4pMb72eUV' 
        },
        { 
            amount: 400, 
            sms: 5000, 
            link: 'https://buy.stripe.com/28oeVz0ku8G2a2Y9AC' 
        },
        { 
            amount: 700, 
            sms: 10000, 
            link: 'https://buy.stripe.com/14k7t71oy3lIejeeUX' 
        },
        { 
            amount: 1500, 
            sms: 50000, 
            link: 'https://buy.stripe.com/7sI9Bfc3c8G21wsdQU' 
        },
        { amount: 
            2500, 
            sms: 100000, 
            link: 'https://buy.stripe.com/bIYaFj6IS2hEeje8wB' 
        },
    ];
    
    // Handle click event on top-up items
    const handleTopUpClick = (amount, link) => {
        // Update selected amount
        setSelectedAmount(amount);
        window.open(link, '_blank');
    };

    return (
        <Layout>
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
                <BalanceCard />

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
                            <div className="grid grid-cols-3 gap-4">
                                {topUpOptions.map(({ amount, sms, link }) => (
                                    <TopupBalanceButton
                                        key={amount}
                                        isSelected={selectedAmount === amount}
                                        onClick={() => handleTopUpClick(amount, link)}
                                    >
                                        <span className="text-xl font-bold mb-1">${amount}</span>
                                        <span className="text-sm text-gray-600">{sms} SMS</span>
                                    </TopupBalanceButton>
                                ))}
                            </div>
                        </Flex>
                    </CardBody>
                </Card>
            </div>
        </Layout>
    );
};

export default Settings;