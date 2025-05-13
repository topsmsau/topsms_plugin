import { useState, useEffect } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
    ToggleControl,
    TextControl,
    Notice
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const BalanceCard = ({ onSuccessMessage }) => {
    const [settings, setSettings] = useState({
        lowBalanceAlert: {
            key: 'low_balance_alert',
            enabled: true,
            loading: true,
            saveSuccess: false
        },
        customerConsent: {
            key: 'customer_consent',
            enabled: true,
            loading: true,
            saveSuccess: false
        },
        smsSurcharge: {
            key: 'sms_surcharge',
            enabled: true,
            loading: true,
            saveSuccess: false
        }
    });
    const [surchargeAmount, setSurchargeAmount] = useState('');
    const [surchargeLoading, setSurchargeLoading] = useState(true);
    const [surchargeError, setSurchargeError] = useState('');
    const [surchargeSuccess, setSurchargeSuccess] = useState(false);
    const [sender, setSender] = useState('');
    const [senderLoading, setSenderLoading] = useState(true);
    const [senderError, setSenderError] = useState('');
    const [senderSuccess, setSenderSuccess] = useState(false);
    
    // Notify parent component when a setting is saved successfully
    useEffect(() => {
        // Check if any settings have saveSuccess set to true
        Object.keys(settings).forEach(key => {
            if (settings[key].saveSuccess) {
                // Send success message to parent component
                let message = '';
                if (key === 'lowBalanceAlert') {
                    message = __('Low balance alert setting saved successfully', 'topsms');
                } else if (key === 'customerConsent') {
                    message = __('Customer consent setting saved successfully', 'topsms');
                } else if (key === 'smsSurcharge') {
                    message = __('SMS surcharge setting saved successfully', 'topsms');
                }
                
                if (message && onSuccessMessage) {
                    onSuccessMessage(message);
                }
                
                // Reset local success state after notifying parent
                setTimeout(() => {
                    setSettings(prev => ({
                        ...prev,
                        [key]: {
                            ...prev[key],
                            saveSuccess: false
                        }
                    }));
                }, 100);
            }
        });
    }, [settings, onSuccessMessage]);

    // Notify parent component of surcharge success
    useEffect(() => {
        if (surchargeSuccess) {
            if (onSuccessMessage) {
                onSuccessMessage(__('Surcharge amount saved successfully', 'topsms'));
            }
            
            // Reset local success state
            setTimeout(() => {
                setSurchargeSuccess(false);
            }, 100);
        }
    }, [surchargeSuccess, onSuccessMessage]);

    // Notify parent component of sender success
    useEffect(() => {
        if (senderSuccess) {
            if (onSuccessMessage) {
                onSuccessMessage(__('Sender name saved successfully', 'topsms'));
            }
            
            // Reset local success state
            setTimeout(() => {
                setSenderSuccess(false);
            }, 100);
        }
    }, [senderSuccess, onSuccessMessage]);

    
    // Initial settings on load (fetched from db)
    useEffect(() => {
        fetchSettings();
    }, []);
    
    // Fetch current toggle settings
    const fetchSettings = async () => {
        try {
            await Promise.all([
                fetchSetting('low_balance_alert', 'lowBalanceAlert'),
                fetchSetting('customer_consent', 'customerConsent'),
                fetchSetting('sms_surcharge', 'smsSurcharge'),
                fetchSurchargeAmount(),
                fetchSender()
            ]);
        } catch (error) {
            console.error('Error fetching settings:', error);
        }
    };
    
    // Fetch single setting
    const fetchSetting = async (key, stateKey) => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setSettings(prev => ({
                    ...prev,
                    [stateKey]: {
                        ...prev[stateKey],
                        loading: false
                    }
                }));
                return;
            }

            // Fetch status settings from backend
            const response = await fetch(`/wp-json/topsms/v1/settings/${key}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();
            // console.log(`${key} enabled setting:  ${data.data.enabled}`);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Update the setting state
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    enabled: data.data.enabled, 
                    loading: false
                }
            }));
        } catch (error) {
            console.error('Error fetching general settings:', error);

            // Loading state is set to false even on error
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    loading: false
                }
            }));
        } 
    };
    
    // Fetch surcharge amount
    const fetchSurchargeAmount = async () => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setSurchargeLoading(false);
                return;
            }

            // Fetch surcharge amount from backend
            const response = await fetch('/wp-json/topsms/v1/settings/sms_surcharge_amount', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();
            // console.log(`Fetch sms surcharge amount:  ${data.data.value}`);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Format the value to have 2 decimal places if it has a value
            let value = data.data.value || '';
            if (value) {
                // Parse as a float to handle numeric operations
                const floatValue = parseFloat(value);
                if (!isNaN(floatValue)) {
                    // Convert to string with exactly 2 decimal places
                    value = floatValue.toFixed(2);
                }
            }

            // Update the surcharge amount state
            setSurchargeAmount(data.data.value || '');
        } catch (error) {
            console.error('Error fetching surcharge amount:', error);
        } finally {
            setSurchargeLoading(false);
        }
    };

    // Fetch sender name
    const fetchSender = async () => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setSenderLoading(false);
                return;
            }

            // Fetch sender name from backend
            const response = await fetch('/wp-json/topsms/v1/settings/sender', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();
            // console.log(`Fetch sender name:  ${data.data.value}`);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Update the sender state
            setSender(data.data.value || '');
        } catch (error) {
            console.error('Error fetching sender name:', error);
        } finally {
            setSenderLoading(false);
        }
    }
    
    // Handle toggle change
    const handleToggleChange = (stateKey) => {
        return () => {
            // Get the current setting info
            const setting = settings[stateKey];
            const newValue = !setting.enabled;
            
            // Update local state immediately
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    enabled: newValue,
                    loading: true,
                    saveSuccess: false
                }
            }));
            
            // Save to database
            saveSetting(setting.key, newValue, stateKey);
        };
    };
    
    // Save a setting to backend
    const saveSetting = async (key, enabled, stateKey) => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Data to send
            const sendData = {
                key: key,
                enabled: enabled
            };

            // Save setting to backend
            const response = await fetch('/wp-json/topsms/v1/settings/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            const data = await response.json();
            // console.log(`Save settings ${key}: ${data}`);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }
            
            // Set save success
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    loading: false, 
                    saveSuccess: true
                }
            }));
        } catch (error) {
            console.error(`Error saving ${key}:`, error);
            // Revert the toggle if saving failed
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    enabled: !prev[stateKey].enabled,
                    loading: false
                }
            }));
        }
    };

    // Handle surcharge input change - validate numbers only
    const handleSurchargeChange = (value) => {
        // Only allow numbers, period, and empty string 
        if (value === '' || /^[\d.]+$/.test(value)) {
            setSurchargeAmount(value);
        }
        
        // Clear any error when the user is typing
        if (surchargeError) {
            setSurchargeError('');
        }
    };

    // Save surcharge amount on blur
    const handleSurchargeBlur = async () => {
        try {
            // Validate the input is a valid amount
            if (surchargeAmount && !/^\d+(\.\d{1,2})?$/.test(surchargeAmount)) {
                setSurchargeError(__('Please enter a valid amount', 'topsms'));
                return;
            }
            setSurchargeLoading(true);
            
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Data to send
            const sendData = {
                key: 'sms_surcharge_amount',
                value: surchargeAmount
            };

            // Save surcharge amount to backend
            const response = await fetch('/wp-json/topsms/v1/settings/save-input', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            const data = await response.json();
            // console.log("Save surcharge amount:", data);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }
            // console.log('Surcharge amount saved successfully');
            setSurchargeSuccess(true);
        } catch (error) {
            console.error('Error saving surcharge amount:', error);
            setSurchargeError(__('Failed to save surcharge amount.', 'topsms'));
        } finally {
            setSurchargeLoading(false);
        }
    };

    // Handle sender input change
    const handleSenderChange = (value) => {
        setSender(value);
        
        // Clear any error when the user is typing
        if (senderError) {
            setSenderError('');
        }
    };

    // Save sender on blur
    const handleSenderBlur = async () => {
        try {
            // Validate sender name length (max 11 characters)
            if (sender.length > 11) {
                setSenderError(__('Sender name must be 11 characters or less', 'topsms'));
                return;
            }
            
            setSenderLoading(true);
            
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Data to send
            const sendData = {
                key: 'sender',
                value: sender
            };

            // Save surcharge amount to backend
            const response = await fetch('/wp-json/topsms/v1/settings/save-input', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            const data = await response.json();
            // console.log("Save sender name:", data);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }
            // console.log('Sender name saved successfully');
            setSenderSuccess(true);
        } catch (error) {
            console.error('Error saving sender name:', error);
            setSenderError(__('Failed to save sender name', 'topsms'));
        } finally {
            setSenderLoading(false);
        }
    };

    return (
        <Card className="mb-6 w-full">
            <CardBody>
                {/* Error notices for surcharge and sender are still kept here */}
                {surchargeError && (
                    <Notice status="error" isDismissible={false} className="mb-3">
                        {surchargeError}
                    </Notice>
                )}
                
                {senderError && (
                    <Notice status="error" isDismissible={false} className="mb-3">
                        {senderError}
                    </Notice>
                )}
                
                <Flex direction="column" gap={4}>
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
                            {settings.lowBalanceAlert.loading ? (
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded-full"></div>
                            ) : (
                                <ToggleControl
                                    __nextHasNoMarginBottom
                                    label=""
                                    checked={settings.lowBalanceAlert.enabled}
                                    onChange={handleToggleChange('lowBalanceAlert')}
                                />
                            )}
                        </Flex>
                    </div>

                    <hr className="border-gray-200 mb-4" />

                    {/* Customer Consent */}
                    <div>
                        <Flex align="center" justify="space-between">
                            <div>
                                <h4 className="text-gray-800 font-bold mb-1">
                                    {__('Enable customer consent at the checkout', 'topsms')}
                                </h4>
                                <p className="text-sm text-gray-600">
                                    {__("Let customers opt in to receive SMS updates during checkout", 'topsms')}
                                </p>
                            </div>
                            {settings.customerConsent.loading ? (
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded-full"></div>
                            ) : (
                                <ToggleControl
                                    __nextHasNoMarginBottom
                                    label=""
                                    checked={settings.customerConsent.enabled}
                                    onChange={handleToggleChange('customerConsent')}
                                />
                            )}
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
                                    {__("Add a surcharge to cover SMS costs", 'topsms')}
                                </p>
                            </div>
                            {settings.smsSurcharge.loading ? (
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded-full"></div>
                            ) : (
                                <ToggleControl
                                    __nextHasNoMarginBottom
                                    label=""
                                    checked={settings.smsSurcharge.enabled}
                                    onChange={handleToggleChange('smsSurcharge')}
                                />
                            )}
                        </Flex>
                        {settings.smsSurcharge.enabled && (
                            <div className="topsms-input mt-4">
                                {surchargeLoading ? (
                                    <div className="animate-pulse bg-gray-300 h-10 w-full rounded"></div>
                                ) : (
                                    <div style={{ position: 'relative' }}>
                                        <span className="surcharge-dollar-sign font-bold">$</span>
                                        <TextControl
                                            label=""
                                            value={surchargeAmount}
                                            onChange={handleSurchargeChange}
                                            onBlur={handleSurchargeBlur}
                                            placeholder={__('How much the surcharge is', 'topsms')}
                                            style={{
                                                paddingLeft: '25px'
                                            }}
                                        />
                                    </div>
                                )}
                            </div>
                        )}
                    </div>

                    <hr className="border-gray-200 mb-4" />

                    {/* Sms sender */}
                    <div>
                        <Flex align="center" justify="space-between">
                            <div>
                                <h4 className="text-gray-800 font-bold mb-1">
                                    {__('SMS Sender', 'topsms')}
                                </h4>
                            </div>
                        </Flex>
                        <div className="topsms-input mt-4">
                            {senderLoading ? (
                                <div className="animate-pulse bg-gray-300 h-10 w-full rounded"></div>
                            ) : (
                                <div>
                                    <TextControl
                                        label="SMS Sender Name"
                                        value={sender}
                                        onChange={handleSenderChange}
                                        onBlur={handleSenderBlur}
                                        placeholder={__('Sender Name to be shown in the SMS', 'topsms')}
                                        maxLength={11}
                                    />
                                </div>
                            )}
                        </div>
                    </div>
                </Flex>
            </CardBody>
        </Card>
    );
};

export default BalanceCard;