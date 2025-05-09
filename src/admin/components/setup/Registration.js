import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button, 
    TextControl,
    Icon
} from '@wordpress/components';
import { useState, memo, useCallback, useEffect } from '@wordpress/element';
import PhoneInput from 'react-phone-input-2';
import 'react-phone-input-2/lib/style.css';

import StepIndicator from './StepIndicator.js';
import RegistrationIcon from '../icons/RegistrationIcon.svg';

// Memoize the CustomInput to prevent unnecessary re-renders
const CustomInput = memo(({ label, value, onChange, error, ...props }) => (
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
        {error && (
            <div className="text-red-500 text-sm mt-1">{error}</div>
        )}
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
        senderName: '',
        streetAddress: '',
        abnAcn: '',
        city: '',
        state: '',
        postcode: '',
    });

    const [errors, setErrors] = useState({});
    const [otpError, setOtpError] = useState(null);
    const [isSending, setIsSending] = useState(false);
    
    // Use useCallback to create stable function references
    const handleChange = useCallback((field, value) => {
        setFormData(prevData => ({
            ...prevData,
            [field]: value
        }));
        
        // Clear error for this field when user types
        if (errors[field]) {
            setErrors(prevErrors => {
                const newErrors = { ...prevErrors };
                delete newErrors[field];
                return newErrors;
            });
        }
    }, [errors]);

    // Form validation function 
    const validateForm = () =>{
        const newErrors = {};

        // Required fields validation
        if (!formData.firstName) newErrors.firstName = __('First name is required', 'topsms');
        if (!formData.lastName) newErrors.lastName = __('Last name is required', 'topsms');
        if (!formData.companyName) newErrors.companyName = __('Company name is required', 'topsms');
        if (!formData.streetAddress) newErrors.streetAddress = __('Street address is required', 'topsms');
        if (!formData.city) newErrors.city = __('City is required', 'topsms');
        if (!formData.state) newErrors.state = __('State is required', 'topsms');

        // Phone number validation
        if (!formData.phoneNumber) {
            newErrors.phoneNumber = __('Phone number is required', 'topsms');
        } else {
            // Remove any non-digit characters 
            const cleanPhoneNumber = formData.phoneNumber.replace(/\D/g, '');

            // Sanitise the 61
            let phoneDigits = cleanPhoneNumber;
            // console.log(cleanPhoneNumber);
            if (cleanPhoneNumber.startsWith('61')) {
                // Remove the country code (61) to check just the actual number
                phoneDigits = cleanPhoneNumber.substring(2);
            }
            
            // Check if it's exactly 9 digits and starts with 4
            if (phoneDigits.length !== 9) {
                newErrors.phoneNumber = __('Phone number must be 9 digits', 'topsms');
            } else if (phoneDigits.charAt(0) !== '4') {
                newErrors.phoneNumber = __('Phone number must start with 4', 'topsms');
            }
        }

        // Email validation
        if (!formData.email) {
            newErrors.email = __('Email is required', 'topsms');
        } else if (!/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$/i.test(formData.email)) {
            newErrors.email = __('Please enter a valid email address', 'topsms');
        }

        // Sender name validation
        if (!formData.senderName) {
            newErrors.senderName = __('Sender name is required', 'topsms');
        } else if (formData.senderName.length > 11){
            newErrors.senderName = __('Sender name must be between 1 and 11 characters.', 'topsms');
        }

        // ABN/ACN validation
        if (!formData.abnAcn) {
            newErrors.abnAcn = __('ABN/ACN is required', 'topsms');
        } else {
            // Remove any non-digit characters
            const cleanAbnAcn = formData.abnAcn.replace(/\D/g, '');
            
            // Check if it's between 9-11 digits
            if (cleanAbnAcn.length < 9 || cleanAbnAcn.length > 11) {
                newErrors.abnAcn = __('ABN/ACN must be between 9-11 digits', 'topsms');
            }
        }

        // Postcode validation
        if (!formData.postcode) {
            newErrors.postcode = __('Postcode is required', 'topsms');
        } else {
            // Remove any non-digit characters
            const cleanPostcode = formData.postcode.replace(/\D/g, '');
            
            // Check if it's exactly 4 digits
            if (cleanPostcode.length !== 4) {
                newErrors.postcode = __('Postcode must be 4 digits', 'topsms');
            }
        }

        // Set the errors
        setErrors(newErrors);

        // Return true if no errors
        return Object.keys(newErrors).length === 0;
    }
    
    // Send otp to the server using rest api
    const sendOTP = async (phoneNumber) => {
        setIsSending(true);
        setOtpError(null);
        
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }
            // Data to send 
            const sendData = {
                phoneNumber: phoneNumber
            }
            // console.log("form data:", formData);
            
            const response = await fetch("/wp-json/topsms/v2/send-otp/", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce,
                },
                body: JSON.stringify(sendData)
            });
            
            if (!response.ok) {
                throw new Error(`Failed to fetch status settings: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }
            
            console.log('OTP sent successfully');
        } catch (err) {
            setOtpError(`Failed to send OTP: ${err.message || 'Unknown error'}`);
            console.error('Error sending OTP:', err);
        } finally {
            setIsSending(false);
        }
    };


    // Validate on submit
    const handleSubmit = useCallback(async (e) => {
        e?.preventDefault();
        
        // Validate the form and send otp 
        // Proceed to the next step if sent successfully
        if (validateForm()) {
            // Only send OTP if form is valid
            try {
                await sendOTP(formData.phoneNumber);
                onComplete('verification', formData);
            } catch (err) {
                console.error('Failed to proceed:', err);
            }
        }
    }, [onComplete, formData, validateForm, formData.phoneNumber]);

    const handleFirstNameChange = useCallback((value) => handleChange('firstName', value), [handleChange]);
    const handleLastNameChange = useCallback((value) => handleChange('lastName', value), [handleChange]);
    const handleCompanyNameChange = useCallback((value) => handleChange('companyName', value), [handleChange]);
    const handlePhoneNumberChange = useCallback((value) => handleChange('phoneNumber', value), [handleChange]);
    const handleEmailChange = useCallback((value) => handleChange('email', value), [handleChange]);
    const handleSenderNameChange = useCallback((value) => handleChange('senderName', value), [handleChange]);
    const handleStreetAddressChange = useCallback((value) => handleChange('streetAddress', value), [handleChange]);
    const handleAbnAcnChange = useCallback((value) => handleChange('abnAcn', value), [handleChange]);
    const handleCityChange = useCallback((value) => handleChange('city', value), [handleChange]);
    const handleStateChange = useCallback((value) => handleChange('state', value), [handleChange]);
    const handlePostcodeChange = useCallback((value) => handleChange('postcode', value), [handleChange]);
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
             <StepIndicator currentStep={1} />
            
            <CardBody className="p-8">
                {/* Form header with icon */}
                <div className="text-center mb-8">
                    <div className="mx-auto mb-4 flex items-center justify-center">
                        <Icon icon={RegistrationIcon} size={32} />
                    </div>
                    <h3 className="text-xl font-bold mb-2">
                        {__('Register', 'topsms')}
                    </h3>

                    <p className="text-gray-600 ">
                        {__('Lorem ipsum dolor sit amet consectetur. Arcu sed aliquam blandit ut magna nullam magna sagittis.', 'topsms')}
                    </p>
                </div>
                
                <form onSubmit={handleSubmit}>
                    {/* Profile details section */}
                    <div className="mb-6">
                        <h3 className="text-lg font-semibold mb-4">
                            {__('Profile details', 'topsms')}
                        </h3>
                        <hr className="border-gray-200 mb-4" />
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <CustomInput
                                key="firstName-field"
                                label={__('First Name', 'topsms')}
                                placeholder={__('Your first name', 'topsms')}
                                value={formData.firstName}
                                onChange={handleFirstNameChange}
                                error={errors.firstName}
                                required
                            />
                            
                            <CustomInput
                                key="lastName-field"
                                label={__('Last Name', 'topsms')}
                                placeholder={__('Your last name', 'topsms')}
                                value={formData.lastName}
                                onChange={handleLastNameChange}
                                error={errors.lastName}
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
                                error={errors.companyName}
                                required
                            />
                            
                            <div className="mb-4">
                                <div className="topsms-label">{__('Phone Number', 'topsms')}</div>
                                <PhoneInput
                                    country={'au'}
                                    value={formData.phoneNumber}
                                    onChange={handlePhoneNumberChange}
                                    placeholder={__('412 345 678', 'topsms')}
                                    containerClass="topsms-phone-container"
                                    inputClass="topsms-phone-input"
                                    onlyCountries={['au']} 
                                    disableDropdown={true}  
                                    countryCodeEditable={false}
                                    required
                                    masks={{au: '... ... ...'}}
                                />
                                {errors.phoneNumber && (
                                    <div className="text-red-500 text-sm mt-1">{errors.phoneNumber}</div>
                                )}
                            </div>
                        </div>
                        
                        <CustomInput
                            key="email-field"
                            label={__('Email', 'topsms')}
                            placeholder={__('your.email@example.com', 'topsms')}
                            type="email"
                            value={formData.email}
                            onChange={handleEmailChange}
                            error={errors.email}
                            required
                        />

                        <CustomInput
                            key="senderName-field"
                            label={__('Sender Name', 'topsms')}
                            placeholder={__('Sender name', 'topsms')}
                            value={formData.senderName}
                            onChange={handleSenderNameChange}
                            error={errors.senderName}
                            required
                        />
                    </div>
                    
                    {/* Business fields section */}
                    <div className="mb-6">
                        <h3 className="text-lg font-semibold mb-4">
                            {__('Business Address', 'topsms')}
                        </h3>
                        <hr className="border-gray-200 mb-4" />
                        
                        <CustomInput
                            key="streetAddress-field"
                            label={__('Street Address', 'topsms')}
                            placeholder={__('Enter your street address', 'topsms')}
                            value={formData.streetAddress}
                            onChange={handleStreetAddressChange}
                            error={errors.streetAddress}
                            required
                        />
                        
                        <CustomInput
                            key="abnAcn-field"
                            label={__('ABN / ACN', 'topsms')}
                            placeholder={__('Enter your ABN or ACN', 'topsms')}
                            value={formData.abnAcn}
                            onChange={handleAbnAcnChange}
                            error={errors.abnAcn}
                            required
                        />
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <CustomInput
                                key="city-field"
                                label={__('City', 'topsms')}
                                placeholder={__('Your city', 'topsms')}
                                value={formData.city}
                                onChange={handleCityChange}
                                error={errors.city}
                                required
                            />
                            
                            <CustomInput
                                key="state-field"
                                label={__('State / Province', 'topsms')}
                                placeholder={__('Your state', 'topsms')}
                                value={formData.state}
                                onChange={handleStateChange}
                                error={errors.state}
                                required
                            />
                        </div>
                        
                        <CustomInput
                            key="postcode-field"
                            label={__('Postcode', 'topsms')}
                            placeholder={__('Your postcode', 'topsms')}
                            value={formData.postcode}
                            onChange={handlePostcodeChange}
                            error={errors.postcode}
                            required
                        />
                    </div>
                </form>

                <div className="mt-8 text-center">
                {otpError && (
                    <div className="text-red-500 text-sm mb-3">{otpError}</div>
                )}

                <Button 
                    primary
                    className={`topsms-button w-full ${isSending ? 'animate-pulse' : ''}`}
                    onClick={handleSubmit}
                    disabled={isSending}
                >
                    {isSending ? __('Registering...', 'topsms') : __('Register', 'topsms')}
                </Button>
                    
                    {/* <div className="mt-4">
                        <Text variant="body.small" className="text-gray-600" color="gray">
                            {__('Already have an account?', 'topsms')} 
                            <a 
                                href="/topsms-login" 
                                className="text-blue-600 hover:text-blue-800 ml-1 font-medium"
                            >
                                {__('Login', 'topsms')}
                            </a>
                        </Text>
                    </div> */}
                </div>
            </CardBody>
        </Card>
    );
};

export default Registration;