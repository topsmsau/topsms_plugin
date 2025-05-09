import { useState } from '@wordpress/element';
import { 
    ToggleControl,
    Card,
    CardBody,
    Flex,
    Panel,
    PanelBody
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';


import Layout from './components/Layout';
import TopupBalanceButton from './components/TopupBalanceButton';

const Settings = () => {
    // State for toggles
    const [statuses, setStatuses] = useState({
        lowbalancealert: false,
    });

    // State for active top-up selection and payment form visibility
    const [selectedAmount, setSelectedAmount] = useState(null);
    const [isPaymentVisible, setIsPaymentVisible] = useState(false);

    // List of top-up options
    const topUpOptions = [
        { amount: 45, sms: 500, link: 'https://buy.stripe.com/6oE5kZc3c3lI2Aw3cc' },
        { amount: 225, sms: 2500, link: 'https://buy.stripe.com/28ofZD3wG4pMb72eUV' },
        { amount: 400, sms: 5000, link: 'https://buy.stripe.com/28oeVz0ku8G2a2Y9AC' },
        { amount: 700, sms: 10000, link: 'https://buy.stripe.com/14k7t71oy3lIejeeUX' },
        { amount: 1500, sms: 50000, link: 'https://buy.stripe.com/7sI9Bfc3c8G21wsdQU' },
        { amount: 2500, sms: 100000, link: 'https://buy.stripe.com/bIYaFj6IS2hEeje8wB' },
    ];

    // Function to handle toggle changes
    const handleToggleChange = () => {
        setStatuses((prevState) => ({
            ...prevState,
            lowbalancealert: !prevState.lowbalancealert,
        }));
    };

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
                {/* Balance Section */}
                <Card className="mb-6 w-full">
                    <CardBody>
                        <Flex direction="column" gap={4}>
                            {/* Current Balance */}
                            <div className="p-4 bg-gray-50 rounded-lg">
                                <h3 className="text-gray-700 mb-2">
                                    {__('Current balance', 'topsms')}
                                </h3>
                                <div className="text-2xl font-bold mb-1">$0.98</div>
                                <div className="text-sm text-gray-600">
                                    {__('Approximately', 'topsms')} <span className="text-blue-500 font-medium">{__('95 SMS', 'topsms')}</span> {__('messages', 'topsms')}
                                </div>
                            </div>

                            {/* Low Balance Alert */}
                            <Panel>
                                <PanelBody>
                                    <Flex align="center" justify="space-between">
                                        <div>
                                            <h4 className="text-gray-800 font-bold mb-1">
                                                {__('Low Balance Alert', 'topsms')}
                                            </h4>
                                            <p className="text-sm text-gray-600">
                                                {__("We'll notify you when your balance falls below $2.00", 'topsms')}
                                            </p>
                                        </div>
                                        <ToggleControl
                                            __nextHasNoMarginBottom
                                            label=""
                                            checked={statuses.lowbalancealert}
                                            onChange={handleToggleChange}
                                        />
                                    </Flex>
                                </PanelBody>
                            </Panel>
                        </Flex>
                    </CardBody>
                </Card>

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