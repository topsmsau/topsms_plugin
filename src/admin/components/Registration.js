import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    CardFooter, 
    Button, 
    TextControl,
    SelectControl,
    ToggleControl 
} from '@wordpress/components';
import { useState, memo, useCallback } from '@wordpress/element';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';

import StepIndicator from './StepIndicator.js';

// Memoize the CustomInput to prevent unnecessary re-renders
const CustomInput = memo(({ label, value, onChange, ...props }) => (
    <div className="mb-4">
        <div className="topsms-label">{label}</div>
        <div className="topsms-input">
            <TextControl
                label=""
                value={value}
                onChange={onChange}
                {...props}
            />
        </div>
    </div>
));

// Password input with toggle visibility
const PasswordControl = memo(({ label, placeholder, value, onChange, showPassword, setShowPassword, required = false }) => (
    <div className="mb-4">
        <div className="topsms-label">{label}</div>
        <div className="relative topsms-input password-input">
            <TextControl
                label=""
                type={showPassword ? "text" : "password"}
                placeholder={placeholder}
                value={value}
                onChange={onChange}
                required={required}
            />
            <button 
                type="button" 
                className="absolute inset-y-0 right-0 pr-3 flex items-center"
                onClick={() => setShowPassword(!showPassword)}
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth="2">
                    {showPassword ? (
                        <>
                            <path d="M3 12a9 9 0 0 1 18 0 9 9 0 0 1-18 0z" />
                            <circle cx="12" cy="12" r="3" />
                        </>
                    ) : (
                        <>
                            <path d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0z" />
                            <path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </>
                    )}
                </svg>
            </button>
        </div>
    </div>
));

const Registration = ({ onComplete }) => {
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        companyName: '',
        countryCode: '+1',
        phoneNumber: '',
        email: '',
        password: '',
        confirmPassword: '',
        streetAddress: '',
        abnAcn: '',
        city: '',
        state: '',
        postcode: '',
    });
    
    const [showBusinessFields, setShowBusinessFields] = useState(true);
    const [showPassword, setShowPassword] = useState(false);
    const [showConfirmPassword, setShowConfirmPassword] = useState(false);
    
    // Use useCallback to create stable function references
    const handleChange = useCallback((field, value) => {
        setFormData(prevData => ({
            ...prevData,
            [field]: value
        }));
    }, []);
    
    const handleSubmit = useCallback((e) => {
        e?.preventDefault();
        onComplete('verification');
    }, [onComplete]);

    const handleFirstNameChange = useCallback((value) => handleChange('firstName', value), [handleChange]);
    const handleLastNameChange = useCallback((value) => handleChange('lastName', value), [handleChange]);
    const handleCompanyNameChange = useCallback((value) => handleChange('companyName', value), [handleChange]);
    const handlePhoneNumberChange = useCallback((value) => handleChange('phoneNumber', value), [handleChange]);
    const handleEmailChange = useCallback((value) => handleChange('email', value), [handleChange]);
    const handlePasswordChange = useCallback((value) => handleChange('password', value), [handleChange]);
    const handleConfirmPasswordChange = useCallback((value) => handleChange('confirmPassword', value), [handleChange]);
    const handleStreetAddressChange = useCallback((value) => handleChange('streetAddress', value), [handleChange]);
    const handleAbnAcnChange = useCallback((value) => handleChange('abnAcn', value), [handleChange]);
    const handleCityChange = useCallback((value) => handleChange('city', value), [handleChange]);
    const handleStateChange = useCallback((value) => handleChange('state', value), [handleChange]);
    const handlePostcodeChange = useCallback((value) => handleChange('postcode', value), [handleChange]);

    // Toggle password visibility callbacks
    const togglePasswordVisibility = useCallback(() => {
        setShowPassword(prev => !prev);
    }, []);
    
    const toggleConfirmPasswordVisibility = useCallback(() => {
        setShowConfirmPassword(prev => !prev);
    }, []);
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
             <StepIndicator currentStep={1} />
            
            <CardBody className="p-8">
                {/* Form header with icon */}
                <div className="text-center mb-8">
                    <div className="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-4 flex items-center justify-center">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="#FF6B00"/>
                        </svg>
                    </div>
                    <h3 className="text-xl font-bold">{__('Register', 'topsms')}</h3>
                    <p className="text-gray-600">{__('Lorem ipsum dolor sit amet consectetur. Arcu sed aliquam blandit ut magna nullam magna sagittis.', 'topsms')}</p>
                </div>
                
                <form onSubmit={handleSubmit}>
                    {/* Profile details section */}
                    <div className="mb-6">
                        <h4 className="text-base font-semibold mb-4">{__('Profile details', 'topsms')}</h4>
                        <hr className="border-gray-200 mb-4" />
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <CustomInput
                                key="firstName-field"
                                label={__('First Name', 'topsms')}
                                placeholder={__('Your first name', 'topsms')}
                                value={formData.firstName}
                                onChange={handleFirstNameChange}
                                required
                            />
                            
                            <CustomInput
                                key="lastName-field"
                                label={__('Last Name', 'topsms')}
                                placeholder={__('Your last name', 'topsms')}
                                value={formData.lastName}
                                onChange={handleLastNameChange}
                                required
                            />
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <CustomInput
                                key="companyName-field"
                                label={__('Company Name', 'topsms')}
                                placeholder={__('Your company name', 'topsms')}
                                value={formData.companyName}
                                onChange={handleCompanyNameChange}
                            />
                            
                            <div className="mb-4">
                                <div className="topsms-label">{__('Phone Number', 'topsms')}</div>
                                <PhoneInput
                                    country={'au'}
                                    value={formData.phoneNumber}
                                    onChange={handlePhoneNumberChange}
                                    placeholder={__('0412 345 678', 'topsms')}
                                    containerClass="topsms-phone-container"
                                    inputClass="topsms-phone-input"
                                    buttonClass="topsms-phone-button"
                                />
                            </div>
                        </div>
                        
                        <CustomInput
                            key="email-field"
                            label={__('Email', 'topsms')}
                            placeholder={__('your.email@example.com', 'topsms')}
                            type="email"
                            value={formData.email}
                            onChange={handleEmailChange}
                            required
                        />
                        
                        <div className="grid grid-cols-2 gap-4">
                            <PasswordControl
                                key="password-field"
                                label={__('Password', 'topsms')}
                                placeholder={__('Create your password', 'topsms')}
                                value={formData.password}
                                onChange={handlePasswordChange}
                                showPassword={showPassword}
                                setShowPassword={togglePasswordVisibility}
                                required
                            />
                            <PasswordControl
                                key="confirmPassword-field"
                                label={__('Confirm Password', 'topsms')}
                                placeholder={__('Confirm your password', 'topsms')}
                                value={formData.confirmPassword}
                                onChange={handleConfirmPasswordChange}
                                showPassword={showConfirmPassword}
                                setShowPassword={toggleConfirmPasswordVisibility}
                                required
                            />
                        </div>
                    </div>
                    
                    {/* Business fields section */}
                    <div className="mb-6">
                        <h4 className="text-base font-semibold mb-4">{__('Business Address', 'topsms')}</h4>
                        <hr className="border-gray-200 mb-4" />
                        
                        <CustomInput
                            key="streetAddress-field"
                            label={__('Street Address', 'topsms')}
                            placeholder={__('Enter your street address', 'topsms')}
                            value={formData.streetAddress}
                            onChange={handleStreetAddressChange}
                        />
                        
                        <CustomInput
                            key="abnAcn-field"
                            label={__('ABN / ACN', 'topsms')}
                            placeholder={__('Enter your ABN or ACN', 'topsms')}
                            value={formData.abnAcn}
                            onChange={handleAbnAcnChange}
                        />
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <CustomInput
                                key="city-field"
                                label={__('City', 'topsms')}
                                placeholder={__('Your city', 'topsms')}
                                value={formData.city}
                                onChange={handleCityChange}
                            />
                            
                            <CustomInput
                                key="state-field"
                                label={__('State / Province', 'topsms')}
                                placeholder={__('Your state', 'topsms')}
                                value={formData.state}
                                onChange={handleStateChange}
                            />
                        </div>
                        
                        <CustomInput
                            key="postcode-field"
                            label={__('Postcode', 'topsms')}
                            placeholder={__('Your postcode', 'topsms')}
                            value={formData.postcode}
                            onChange={handlePostcodeChange}
                        />
                    </div>
                </form>

                <div className="mt-6 text-center">
                    <Button 
                        primary
                        className="topsms-button w-full"
                        onClick={handleSubmit}
                    >
                        {__('Register', 'topsms')}
                    </Button>
                    
                    <div className="mt-4 text-gray-600 text-lg">
                        {__('Already have an account?', 'topsms')} 
                        <a 
                            href="/topsms-login" 
                            className="text-blue-600 hover:text-blue-800 ml-1 font-medium"
                        >
                            {__('Login', 'topsms')}
                        </a>
                    </div>
                </div>
            </CardBody>
        </Card>
    );
};

export default Registration;