import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    CardFooter, 
    Button, 
    Flex,
    __experimentalText as Text,
    __experimentalHeading as Heading
} from '@wordpress/components';
import { useState } from '@wordpress/element';

import StepIndicator from './StepIndicator.js';

const Verification = ({ onComplete }) => {
    const [verificationCode, setVerificationCode] = useState(['', '', '', '', '', '']);
    const [isResending, setIsResending] = useState(false);
    const [phoneNumber, setPhoneNumber] = useState('+3249587757'); // This would come from your app state
    
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
    
    // Handle form submission
    const handleSubmit = (e) => {
        e?.preventDefault();
        const code = verificationCode.join('');
        console.log('Verification code submitted:', code);
        // You would typically verify the code with an API here
        onComplete('welcome');
    };
    
    // Handle code resend
    const handleResendCode = () => {
        setIsResending(true);
        
        // Simulate API request
        setTimeout(() => {
            // Reset verification code
            setVerificationCode(['', '', '', '', '', '']);
            setIsResending(false);
            console.log('Verification code resent');
        }, 1500);
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
            <StepIndicator currentStep={1} />
            
            <CardBody className="p-6">
                {/* Form header with icon */}
                <div className="text-center mb-8">
                    <div className="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V8L12 13L20 8V18ZM12 11L4 6H20L12 11Z" fill="#FF6B00"/>
                        </svg>
                    </div>
                    <Heading level={3} className="text-xl font-bold mb-2">
                        {__('Verify Your Phone Number', 'topsms')}
                    </Heading>
                    <Text variant="body.medium" className="text-gray-600 text-lg">
                        {__('We have sent a verification code at number', 'topsms')} {' '}
                        <span className="text-blue-600 font-semibold">{phoneNumber}</span>.
                        <br />
                        {__('You can check the SMS you receive', 'topsms')}
                    </Text>
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
                    
                    {/* Resend code link */}
                    <Flex align="center" className="mb-4">
                        <Text variant="body.medium" className="text-gray-500 mb-2" color="gray">
                            {__("Haven't received the OTP code yet?", 'topsms')}
                        </Text>
                        <Button
                            variant="link"
                            className="text-blue-600 font-medium hover:underline"
                            onClick={handleResendCode}
                            disabled={isResending}
                            isBusy={isResending}
                        >
                            {isResending ? __('Resending...', 'topsms') : __('Resend OTP Code', 'topsms')}
                        </Button>
                    </Flex>

                    <Button 
                        primary
                        className="topsms-button w-full mt-8"
                        onClick={handleSubmit}
                        disabled={verificationCode.some(digit => !digit)}
                    >
                        {__('Next', 'topsms')}
                    </Button>
                </form>

                {/* Info box */}
                <div className="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4 mt-8">
                    <Text variant="body.medium" className="text-blue-700 text-lg" color="rgb(37,99,235)">
                        {__('All new accounts need to be manually verified before you can send campaigns.', 'topsms')}
                        {' '}
                        {__('Once you have registered, we will call you within 24 hours or Monday if on the weekend.', 'topsms')}
                    </Text>
                </div>
            </CardBody>
        
        </Card>
    );
};

export default Verification;