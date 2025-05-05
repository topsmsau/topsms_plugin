// src/admin/components/Registration.js
import { __ } from '@wordpress/i18n';
import { Card, CardBody, CardFooter, TextControl, CheckboxControl, Button } from '@wordpress/components';
import { useState } from '@wordpress/element';

const Registration = ({ onComplete }) => {
    const [formData, setFormData] = useState({
        firstName: '',
        lastName: '',
        companyName: '',
        phoneNumber: '',
        email: '',
        password: '',
        confirmPassword: '',
        streetAddress: '',
        abnAcn: '',
        city: '',
        state: '',
        postcode: '',
        agreeTerms: false
    });
    
    const [showBusinessFields, setShowBusinessFields] = useState(true);
    
    const handleChange = (field, value) => {
        setFormData({
            ...formData,
            [field]: value
        });
    };
    
    const handleSubmit = (e) => {
        e.preventDefault();
        console.log('Form submitted:', formData);
        // You would typically validate and submit the form data here
        onComplete('verification');
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm">
            {/* Step indicator */}
            <div className="border-b border-gray-200 p-4">
                <div className="flex items-center justify-center">
                    <div className="flex items-center">
                        <div className="bg-blue-600 text-white rounded-full w-8 h-8 flex items-center justify-center mr-2">
                            <span>1</span>
                        </div>
                        <span className="text-sm mr-2">{__('Register', 'topsms')}</span>
                    </div>
                    
                    <div>
                        <div className="h-px w-6 bg-gray-300"></div>
                    </div>
                    
                    <div className="flex items-center">
                        <div className="bg-gray-200 text-gray-600 rounded-full w-8 h-8 flex items-center justify-center mr-2">
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
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 12C14.21 12 16 10.21 16 8C16 5.79 14.21 4 12 4C9.79 4 8 5.79 8 8C8 10.21 9.79 12 12 12ZM12 14C9.33 14 4 15.34 4 18V20H20V18C20 15.34 14.67 14 12 14Z" fill="#FF6B00"/>
                        </svg>
                    </div>
                    <h3 className="text-xl font-bold">{__('Register', 'topsms')}</h3>
                    <p className="text-gray-600">{__('Lorem Ipsum is simply dummy text of the printing and typesetting industry. ', 'topsms')}</p>
                </div>
                
                <form onSubmit={handleSubmit}>
                    {/* Profile details section */}
                    <div className="mb-6">
                        <h4 className="text-md font-semibold mb-4">{__('Profile details', 'topsms')}</h4>
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <TextControl
                                    label={__('First Name', 'topsms')}
                                    placeholder="Your first name"
                                    value={formData.firstName}
                                    onChange={(value) => handleChange('firstName', value)}
                                    required
                                />
                            </div>
                            <div>
                                <TextControl
                                    label={__('Last Name', 'topsms')}
                                    placeholder="Your last name"
                                    value={formData.lastName}
                                    onChange={(value) => handleChange('lastName', value)}
                                    required
                                />
                            </div>
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <TextControl
                                    label={__('Company Name', 'topsms')}
                                    placeholder="Your company name"
                                    value={formData.companyName}
                                    onChange={(value) => handleChange('companyName', value)}
                                />
                            </div>
                            <div>
                                <TextControl
                                    label={__('Phone Number', 'topsms')}
                                    placeholder="(333) 000-0000"
                                    type="tel"
                                    value={formData.phoneNumber}
                                    onChange={(value) => handleChange('phoneNumber', value)}
                                    required
                                />
                            </div>
                        </div>
                        
                        <div className="mb-4">
                            <TextControl
                                label={__('Email', 'topsms')}
                                placeholder="your.email@example.com"
                                type="email"
                                value={formData.email}
                                onChange={(value) => handleChange('email', value)}
                                required
                            />
                        </div>
                        
                        <div className="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <TextControl
                                    label={__('Password', 'topsms')}
                                    placeholder="Enter password"
                                    type="password"
                                    value={formData.password}
                                    onChange={(value) => handleChange('password', value)}
                                    required
                                />
                            </div>
                            <div>
                                <TextControl
                                    label={__('Confirm Password', 'topsms')}
                                    placeholder="Confirm password"
                                    type="password"
                                    value={formData.confirmPassword}
                                    onChange={(value) => handleChange('confirmPassword', value)}
                                    required
                                />
                            </div>
                        </div>
                    </div>
                    
                    {/* Business fields section */}
                    {showBusinessFields && (
                        <div className="mb-6">
                            <h4 className="text-md font-semibold mb-4">{__('Business Address', 'topsms')}</h4>
                            
                            <div className="mb-4">
                                <TextControl
                                    label={__('Street Address', 'topsms')}
                                    placeholder="Enter your street address"
                                    value={formData.streetAddress}
                                    onChange={(value) => handleChange('streetAddress', value)}
                                />
                            </div>
                            
                            <div className="mb-4">
                                <TextControl
                                    label={__('ABN / ACN', 'topsms')}
                                    placeholder="Enter your ABN or ACN"
                                    value={formData.abnAcn}
                                    onChange={(value) => handleChange('abnAcn', value)}
                                />
                            </div>
                            
                            <div className="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <TextControl
                                        label={__('City', 'topsms')}
                                        placeholder="Your city"
                                        value={formData.city}
                                        onChange={(value) => handleChange('city', value)}
                                    />
                                </div>
                                <div>
                                    <TextControl
                                        label={__('State / Province', 'topsms')}
                                        placeholder="Your state"
                                        value={formData.state}
                                        onChange={(value) => handleChange('state', value)}
                                    />
                                </div>
                            </div>
                            
                            <div className="mb-4">
                                <TextControl
                                    label={__('Postcode', 'topsms')}
                                    placeholder="Your postcode"
                                    value={formData.postcode}
                                    onChange={(value) => handleChange('postcode', value)}
                                />
                            </div>
                        </div>
                    )}
                
                </form>
            </CardBody>
            
            <CardFooter className="p-6 bg-gray-50 rounded-b-lg border-t border-gray-200">
                <Button 
                    isPrimary
                    className="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-md"
                    onClick={handleSubmit}
                >
                    {__('Register', 'topsms')}
                </Button>
            </CardFooter>
        </Card>
    );
};

export default Registration;