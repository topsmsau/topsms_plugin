import { useState } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
    // Panel,
    // PanelBody,
    ToggleControl,
    TextControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const BalanceCard = () => {
    // State for toggles
    const [statuses, setStatuses] = useState({
        lowBalanceAlert: false,
        customerConsent: false, 
        smsSurcharge: false
    });

    const [surchargeAmount, setSurchargeAmount] = useState('');

    // Function to handle toggle changes
    const handleToggleChange = (statusKey) => {
        return () => {
            setStatuses((prevState) => ({
                ...prevState,
                [statusKey]: !prevState[statusKey], 
            }));
        };
    };


    return (
        <Card className="mb-6 w-full">
            <CardBody>
                <Flex direction="column" gap={4}>
                    {/* Current Balance */}
                    {/* <div className="p-4 bg-gray-50 rounded-lg">
                        <h3 className="text-gray-700 mb-2">
                            {__('Current balance', 'topsms')}
                        </h3>
                        <div className="text-2xl font-bold mb-1">$0.98</div>
                        <div className="text-sm text-gray-600">
                            {__('Approximately', 'topsms')} <span className="text-blue-500 font-medium">{__('95 SMS', 'topsms')}</span> {__('messages', 'topsms')}
                        </div>
                    </div> */}

                    {/* Low Balance Alert */}
                    <div>
                        <Flex align="center" justify="space-between">
                            <div>
                                <h4 className="text-gray-800 font-bold mb-1">
                                    {__('Enable low balance alerts', 'topsms')}
                                </h4>
                                <p className="text-sm text-gray-600">
                                    {__("We'll notify you when your balance falls below $2.00", 'topsms')}
                                </p>
                            </div>
                            <ToggleControl
                                __nextHasNoMarginBottom
                                label=""
                                checked={statuses.lowBalanceAlert}
                                onChange={handleToggleChange('lowBalanceAlert')}
                            />
                        </Flex>
                    </div>

                    <hr className="border-gray-200 mb-4" />

                    {/* Customer Consent */}
                    <div>
                        <Flex align="center" justify="space-between">
                            <div>
                                <h4 className="text-gray-800 font-bold mb-1">
                                    {__('Enable customer constent at the checkout', 'topsms')}
                                </h4>
                                <p className="text-sm text-gray-600">
                                    {__("Let customers opt in to receive SMS updates during checkout", 'topsms')}
                                </p>
                            </div>
                            <ToggleControl
                                __nextHasNoMarginBottom
                                label=""
                                checked={statuses.customerConsent}
                                onChange={handleToggleChange('customerConsent')}
                            />
                        </Flex>
                    </div>

                    <hr className="border-gray-200 mb-4" />

                    {/* Sms surcharge */}
                    <div>
                        <Flex align="center" justify="space-between">
                            <div>
                                <h4 className="text-gray-800 font-bold mb-1">
                                    {__('Charge customer for the SMS', 'topsms')}
                                </h4>
                                <p className="text-sm text-gray-600">
                                    {__("Let customers opt in to receive SMS updates during checkout", 'topsms')}
                                </p>
                            </div>
                            <ToggleControl
                                __nextHasNoMarginBottom
                                label=""
                                checked={statuses.smsSurcharge}
                                onChange={handleToggleChange('smsSurcharge')}
                            />
                        </Flex>
                        <div className="topsms-input mt-4">
                            <TextControl
                                label=""
                                value={surchargeAmount}
                                onChange={setSurchargeAmount}
                                placeholder={__('$ how much the surcharge is', 'topsms')}
                            />
                        </div>
                    </div>
                </Flex>
            </CardBody>
        </Card>
    );
};

export default BalanceCard;