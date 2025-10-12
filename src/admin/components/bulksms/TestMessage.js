import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { 
    Card, 
    CardHeader, 
    CardBody, 
    Button,
} from '@wordpress/components';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';

const TestMessage = ({ message, sender, url, onSuccessMessage, onErrorMessage }) => {
    const [phoneNumber, setPhoneNumber] = useState('');
    const [isSending, setIsSending] = useState(false);
    const [error, setError] = useState('');

    // Check if phone has digits apart from the country code
    const hasPhoneDigits = () => {
        if (!phoneNumber) return false;
        const cleanPhone = phoneNumber.replace(/\D/g, '');
        // Remove country code 61 if present
        const phoneDigits = cleanPhone.startsWith('61') ? cleanPhone.substring(2) : cleanPhone;
        return phoneDigits.length > 0;
    };

    const isButtonEnabled = message.trim() && hasPhoneDigits();
    
    const handlePhoneChange = (value) => {
        setPhoneNumber(value);
        // Clear error when user types
        if (error) {
            setError('');
        }
    };

    // Validate phone number
    const validatePhoneNumber = (phone) => {
        if (!phone) {
            setError(__('Phone number is required', 'topsms'));
            return false;
        } else {
            // Remove any non-digit characters 
            const cleanPhone = phone.replace(/\D/g, '');

            // Sanitise the 61
            let phoneDigits = cleanPhone;
            // console.log(cleanPhone);
            if (cleanPhone.startsWith('61')) {
                // Remove the country code (61) to check just the actual number
                phoneDigits = cleanPhone.substring(2);
            }
            
            // Check if it's exactly 9 digits and starts with 4
            if (phoneDigits.length !== 9) {
                setError(__('Phone number must be 9 digits', 'topsms'));
                return false;
            } else if (phoneDigits.charAt(0) !== '4') {
                setError(__('Phone number must start with 4', 'topsms'));
                return false;
            }

            return true;
        }
    }

    const testSendMessage = async () => {
        if (!validatePhoneNumber(phoneNumber)) {
            return;
        }

        setError('');
        
        await sendMessage();
    }

    // Send a test message.
    const sendMessage = async () => {
        setIsSending(true);

        try {
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            const payload = {
                phone_number: phoneNumber,
                message: message,
                sender: sender,
                url: url,
            };
            const sendData = {
                payload: payload
            }

            const response = await fetch('/wp-json/topsms/v2/send-sms', {
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

            // Test message sent successfully, display message
            onSuccessMessage(data.data.message || __('Message sent successfully', 'topsms'));
        } catch (err) {
            console.error('Error sending test message:', err);
            onErrorMessage(`${err.message}. Please try again.` || __('Failed to send message. Please try again.', 'topsms'));
        } finally {
            setIsSending(false);
        }
    };

    return (
        <>
            <Card className="test-message-card shadow-none border-0 w-full mx-0 mt-8">
                <CardHeader className="border-b-0 px-0 pb-2">
                    <h3 className="text-xl font-medium mb-0">
                        {__('Test Message', 'topsms')}
                    </h3>
                </CardHeader>
                <CardBody className="pt-2 px-0">
                    <div className="mb-4">
                        <div className="topsms-label mb-2">{__('Phone Number', 'topsms')}</div>
                        <div className="flex gap-2">
                            <div className="flex-1">
                                <PhoneInput
                                    country={'au'}
                                    value={phoneNumber}
                                    onChange={handlePhoneChange}
                                    placeholder={__('412 345 678', 'topsms')}
                                    containerClass="topsms-phone-container"
                                    inputClass="topsms-phone-input"
                                    onlyCountries={['au']} 
                                    disableDropdown={true}  
                                    countryCodeEditable={false}
                                    required
                                    masks={{au: '... ... ...'}}
                                />
                                {error && (
                                    <div className="text-red-500 text-sm mt-1">{error}</div>
                                )}
                            </div>
                            <Button
                                className={`bulksms-test-message-button ${
                                    (isButtonEnabled && !isSending) ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-300'
                                }`}
                                onClick={testSendMessage}
                                disabled={!isButtonEnabled || isSending}
                            >
                                {isSending ? __('Sending...', 'topsms') : __('Test', 'topsms')}
                            </Button>
                        </div>
                    </div>
                </CardBody>
            </Card>
        </>
    );
};

export default TestMessage;