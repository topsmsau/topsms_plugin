import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardFooter, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

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
        e.preventDefault();
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
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm">
            {/* Step indicator */}
            <div className="border-b border-gray-200 p-4">
                <div className="flex items-center justify-center">
                    <div className="flex items-center">
                        <div className="bg-green-500 text-white rounded-full w-8 h-8 flex items-center justify-center mr-2">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fillRule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clipRule="evenodd" />
                            </svg>
                        </div>
                        <span className="text-sm mr-2">{__('Register', 'topsms')}</span>
                    </div>
                    
                    <div>
                        <div className="h-px w-6 bg-gray-300"></div>
                    </div>
                    
                    <div className="flex items-center">
                        <div className="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-2">
                            <span>2</span>
                        </div>
                        <span className="text-sm mr-2">{__('Confirm Phone Number', 'topsms')}</span>
                    </div>
                    
                    <div>
                        <div className="h-px w-6 bg-gray-300"></div>
                    </div>
                    
                    <div className="flex items-center">
                        <div className="bg-gray-200 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center mr-2">
                            <span>3</span>
                        </div>
                        <span className="text-sm">{__('Welcome to TopSMS', 'topsms')}</span>
                    </div>
                </div>
            </div>
            
            <CardBody className="p-6">
                {/* Form header with icon */}
                <div className="text-center mb-8">
                    <div className="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M20 4H4C2.9 4 2 4.9 2 6V18C2 19.1 2.9 20 4 20H20C21.1 20 22 19.1 22 18V6C22 4.9 21.1 4 20 4ZM20 18H4V8L12 13L20 8V18ZM12 11L4 6H20L12 11Z" fill="#FF6B00"/>
                        </svg>
                    </div>
                    <h3 className="text-xl font-bold">{__('Verify Your Phone Number', 'topsms')}</h3>
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
                    
                    {/* Resend code link */}
                    <div className="text-center mb-4">
                        <p className="text-gray-500 mb-2">
                            {__("Haven't received the OTP code yet?", 'topsms')}
                        </p>
                        <button
                            type="button"
                            className="text-blue-600 font-medium hover:underline"
                            onClick={handleResendCode}
                            disabled={isResending}
                        >
                            {isResending ? __('Resending...', 'topsms') : __('Resend OTP Code', 'topsms')}
                        </button>
                    </div>
                    
                    {/* Info box */}
                    <div className="bg-blue-50 p-4 rounded-lg border border-blue-100 mb-4">
                        <p className="text-blue-700">
                            {__('All new accounts need to be manually verified before you can send campaigns.', 'topsms')}
                            {' '}
                            {__('Once you have registered, we will call you within 24 hours or Monday if on the weekend.', 'topsms')}
                        </p>
                    </div>
                </form>
            </CardBody>
            
            <CardFooter className="p-6 bg-gray-50 rounded-b-lg border-t border-gray-200">
                <Button 
                    isPrimary
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md"
                    onClick={handleSubmit}
                    disabled={verificationCode.some(digit => !digit)}
                >
                    {__('Next', 'topsms')}
                </Button>
            </CardFooter>
        </Card>
    );
};

export default Verification;