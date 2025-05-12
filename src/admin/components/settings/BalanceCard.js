import { useState, useEffect } from '@wordpress/element';
import { 
    Card,
    CardBody,
    Flex,
    ToggleControl,
    TextControl
} from '@wordpress/components';
import { __ } from '@wordpress/i18n';

const BalanceCard = () => {
    // State for toggles and surcharge field
    const [settings, setSettings] = useState({
        lowBalanceAlert: {
            key: 'low_balance_alert',
            enabled: true,
            loading: true
        },
        customerConsent: {
            key: 'customer_consent',
            enabled: true,
            loading: true
        },
        smsSurcharge: {
            key: 'sms_surcharge',
            enabled: true,
            loading: true
        }
    });
    
    const [surchargeAmount, setSurchargeAmount] = useState('');
    
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
                // fetchSurchargeAmount()
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
                setIsLoading(false);
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

            // // Check if success 
            // if (!response.ok) {
            //     const errorData = await response.json().catch(() => null);
            //     throw new Error(
            //         errorData?.message || 
            //         `Failed to fetch status settings: ${response.status}`
            //     );
            // }

            const data = await response.json();
            // console.log(`Status settings for ${key}:`, data);

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
                    loading: true
                }
            }));
            
            // Save to database
            saveSetting(setting.key, newValue, stateKey);
        };
    };
    
    // Save a setting to the database
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
            // console.log(`Successfully save status settings for ${key}:`, data);
            
            if (!data.success) {
                throw new Error(data.data?.message || 'Unknown error');
            }
        } catch (error) {
            console.error(`Error saving ${key}:`, error);
            // Revert the toggle if saving failed
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    enabled: !prev[stateKey].enabled
                }
            }));
        } finally {
            // Set loading to false 
            setSettings(prev => ({
                ...prev,
                [stateKey]: {
                    ...prev[stateKey],
                    loading: false
                }
            }));
        }
    };

    return (
        <Card className="mb-6 w-full">
            <CardBody>
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
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded"></div>
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
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded"></div>
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
                                <div className="animate-pulse bg-gray-300 h-5 w-9 rounded"></div>
                            ) : (
                                <ToggleControl
                                    __nextHasNoMarginBottom
                                    label=""
                                    checked={settings.smsSurcharge.enabled}
                                    onChange={handleToggleChange('smsSurcharge')}
                                />
                            )}
                        </Flex>
                        {settings.smsSurcharge.value && (
                            <div className="topsms-input mt-4">
                                {surchargeLoading ? (
                                    <div className="animate-pulse bg-gray-300 h-10 w-full rounded"></div>
                                ) : (
                                    <TextControl
                                        label=""
                                        value={surchargeAmount}
                                        // onChange={handleSurchargeChange}
                                        placeholder={__('$ how much the surcharge is', 'topsms')}
                                    />
                                )}
                            </div>
                        )}
                    </div>
                </Flex>
            </CardBody>
        </Card>
    );
};

export default BalanceCard;