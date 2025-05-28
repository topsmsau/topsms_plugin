import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button, 
    Flex,
    Icon,
    CheckboxControl 
} from '@wordpress/components';
import { useState, useCallback, useEffect } from '@wordpress/element';

import StepIndicator from './StepIndicator.js';
import VerificationIcon from '../icons/VerificationIcon.svg';

const Verification = ({ onComplete, userData }) => {
    const [verificationCode, setVerificationCode] = useState(['', '', '', '', '', '']);
    const [isResending, setIsResending] = useState(false);
    const [error, setError] = useState(null);
    const [countdown, setCountdown] = useState(60);
    const [phoneNumber, setPhoneNumber] = useState(userData.phoneNumber);
    const [isVerifying, setIsVerifying] = useState(false);
    const [isChecked, setIsChecked] = useState(false);

    
    // Handle verification code input change
    const handleCodeChange = (index, value) => {
        if (value.length > 1) {
            value = value.charAt(0);
        }
        
        // Only allow numbers
        if (value && !/^\d+$/.test(value)) {
            return;
        }
        
        const newCode = [...verificationCode];
        newCode[index] = value;
        setVerificationCode(newCode);
        
        // Auto-focus next input
        if (value && index < 5) {
            const nextInput = document.getElementById(`code-input-${index + 1}`);
            if (nextInput) {
                nextInput.focus();
            }
        }
    };
    
    // Handle key down for backspace
    const handleKeyDown = (index, e) => {
        if (e.key === 'Backspace' && !verificationCode[index] && index > 0) {
            const prevInput = document.getElementById(`code-input-${index - 1}`);
            if (prevInput) {
                prevInput.focus();
            }
        }
    };

    // useEffect(() => {
    //     // Only send the OTP if we have a phone number
    //     if (userData.phoneNumber) {
    //         // Format the phone number - start with 4 (remove the country code)
    //         let formattedNumber = userData.phoneNumber;
    //         if (userData.phoneNumber.startsWith('61')) {
    //             formattedNumber = userData.phoneNumber.substring(2);
    //         }
    //         setPhoneNumber(formattedNumber);
    //         // sendOTP(formattedNumber);
    //     }
    // }, [userData.phoneNumber]);

    // Countdown timer to resend otp
    useEffect(() => {
        let timer = null;
        if (countdown > 0) {
            timer = setInterval(() => {
                setCountdown(prevCountdown => prevCountdown - 1);
            }, 1000);
        }
        return () => {
            if (timer) clearInterval(timer);
        };
    }, [countdown]);


    // Send otp to the server using rest api
    const sendOTP = async (number) => {
        setError(null);

        const phoneNumber_ = number || phoneNumber;
        
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }
            // Data to send 
            const sendData = {
                phoneNumber: phoneNumber_
            }
            // console.log("form data:", formData);

            const response = await fetch("/wp-json/topsms/v1/send-otp/", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify(sendData)
            });

            // if (!response.ok) {
            //     const errorData = await response.json().catch(() => null);
            //     throw new Error(
            //         errorData?.message || 
            //         `Failed to send OTP: ${response.status}`
            //     );
            // }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }
            
            // console.log('OTP sent successfully');
        } catch (err) {
            setError(`Failed to send OTP: ${err.message || 'Unknown error'}`);
            console.error('Error sending OTP:', err);
        } 
    };

    // Verify otp 
    const verifyOTP = async (otp) => {
        setIsVerifying(true);
        setError(null);
        
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }

            // Form data 
            const newData = {
                phone_number: phoneNumber,
                otp: otp,
                email: userData.email || '',
                company: userData.companyName || '',
                address: userData.streetAddress || '',
                first_name: userData.firstName || '',
                last_name: userData.lastName || '',
                city: userData.city || '',
                state: userData.state || '',
                postcode: userData.postcode || '',
                abn: userData.abnAcn || '',
                sender: userData.senderName || ''
            };
            // Data to send 
            const sendData = {
                payload: newData
            }
    
            
            const response = await fetch("/wp-json/topsms/v1/verify-otp/", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify(sendData)
            });

            // if (!response.ok) {
            //     const errorData = await response.json().catch(() => null);
            //     throw new Error(
            //         errorData?.message || 
            //         `Failed to verify OTP: ${response.status}`
            //     );
            // }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }
            
            // console.log('OTP verified successfully');
            return true;
        } catch (err) {
            setError(`Failed to verify OTP: ${err.message || 'Unknown error'}`);
            console.error('Error verifying OTP:', err);
            return false;
        } finally {
            setIsVerifying(false);
        }
    }
    
    // Handle form submission
    const handleSubmit = async (e) => {
        e?.preventDefault();
        const code = verificationCode.join('');
        // Check valid code
        if (code.length !== 6) {
            setError('Please enter the complete 6-digit verification code');
            return;
        }

        // console.log('Verification code submitted:', code);

        // Verify the OTP with the backend, go to the next page if successful
        const verified = await verifyOTP(code);
        if (verified) {
            // Move to the next step if verification was successful
            onComplete('welcome');
        } else {
            setVerificationCode(['', '', '', '', '', '']);
            // Focus on the first input field to let the user try again
            const firstInput = document.getElementById('code-input-0');
            if (firstInput) {
                firstInput.focus();
            }
        }
    };
    
    // Handle code resend
    const handleResendCode = () => {
        setIsResending(true);
        
        // Resend otp
        sendOTP()
            .then(() => {
                // Reset verification code
                setVerificationCode(['', '', '', '', '', '']);
                // Set countdown to 60 seconds (1 minute)
                setCountdown(60);
                // console.log('Verification code resent');
            })
            .finally(() => {
                setIsResending(false);
            });
    };
    
    // Format remaining time
    const formatTime = (seconds) => {
        return `${seconds}s`;
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
            <StepIndicator currentStep={2} />
            
            <CardBody className="p-6">
                {/* Form header with icon */}
                <div className="text-center mb-8">
                    <div className="mx-auto mb-4 flex items-center justify-center">
                        <Icon icon={VerificationIcon} size={32} />
                    </div>
                    <h3 className="text-xl font-bold mb-2">
                        {__('Verify Your Phone Number', 'topsms')}
                    </h3>
                    <p className="text-gray-600">
                        {__('We have sent a verification code at number', 'topsms')} {' '}
                        <span className="text-blue-600 font-semibold">{phoneNumber}</span>.
                        <br />
                        {__('You can check the SMS you receive', 'topsms')}
                    </p>
                </div>
                
                <form onSubmit={handleSubmit}>
                    {/* Verification code input */}
                    <div className="flex justify-center gap-2 mb-8">
                        {verificationCode.map((digit, index) => (
                            <input
                                key={index}
                                id={`code-input-${index}`}
                                type="text"
                                maxLength="1"
                                className="w-14 h-14 border border-gray-300 rounded-lg text-center text-xl font-semibold"
                                value={digit}
                                onChange={(e) => handleCodeChange(index, e.target.value)}
                                onKeyDown={(e) => handleKeyDown(index, e)}
                                autoFocus={index === 0}
                            />
                        ))}
                    </div>

                    {/* Error message */}
                    {error && (
                        <div className="text-red-500 text-center mb-4 font-medium">
                            {error}
                        </div>
                    )}
                    
                    {/* Resend code link */}
                    <Flex align="center" className="mb-4">
                        <p className="text-gray-500 mb-2">
                            {__("Haven't received the OTP code yet?", 'topsms')}
                        </p>
                        {countdown > 0 ? (
                            <p className="text-blue-400 font-medium ml-2">
                                {__('Resend code in', 'topsms')} {formatTime(countdown)}
                            </p>
                        ) : (
                            <Button
                                variant="link"
                                className="text-blue-600 font-medium hover:underline"
                                onClick={handleResendCode}
                                disabled={isResending || countdown > 0}
                                isBusy={isResending}
                            >
                                {isResending ? __('Resending...', 'topsms') : __('Resend OTP Code', 'topsms')}
                            </Button>
                        )}
                    </Flex>

                    <CheckboxControl
                        __nextHasNoMarginBottom
                        label={
                                <span>
                                    {__('I have read and agree to the ', 'topsms')}
                                    <a href="https://topsms.com.au/terms-conditions/" target="_blank" className="text-blue-600 hover:underline">Terms and Conditions</a> 
                                    {__(' and ', 'topsms')}
                                    <a href="https://topsms.com.au/privacy-policy/" target="_blank" className="text-blue-600 hover:underline">Privacy Policy</a>
                                    {__('. I understand how my personal data will be used as described in the Privacy Policy. ', 'topsms')}
                                </span>
                            }
                        checked={ isChecked }
                        onChange={ setIsChecked }
                    />

                    <Button 
                        primary
                        className={`topsms-button w-full mt-8 ${isVerifying ? 'animate-pulse' : ''}`}
                        onClick={handleSubmit}
                        disabled={verificationCode.some(digit => !digit) || !isChecked}
                    >
                        {isVerifying ? __('Verifying...', 'topsms') : __('Next', 'topsms')}
                    </Button>
                </form>

                {/* Info box */}
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4 mt-8">
                    <p className="text-blue-700">
                        {__('All new accounts need to be manually verified before you can send campaigns.', 'topsms')}
                        {' '}
                        {__('Once you have registered, we will call you within 24 hours or Monday if on the weekend.', 'topsms')}
                    </p>
                </div>
            </CardBody>
        
        </Card>
    );
};

export default Verification;