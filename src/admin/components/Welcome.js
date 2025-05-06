import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button,
    __experimentalText as Text,
    __experimentalHeading as Heading
} from '@wordpress/components';
import { useState } from '@wordpress/element';

import StepIndicator from './StepIndicator.js';

const Welcome = ({ onComplete }) => {
    const handleClick = () => {
        // Navigate to automations page or next step
        onComplete('automations');
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
            <StepIndicator currentStep={3} />
            
            <CardBody className="p-6 text-center">
                {/* Success icon */}
                <div className="w-20 h-20 bg-gray-100 rounded-full mx-auto mb-6 flex items-center justify-center">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" fill="#4CAF50"/>
                    </svg>
                </div>
                
                <Heading level={2} className="text-2xl font-bold mb-4">
                    {__('Fantastic! Your account has been created and will be reviewed in the next 24 hours.', 'topsms')}
                </Heading>
                
                <Text variant="body.medium" className="text-gray-600 mb-8 mt-8">
                    {__('Lorem ipsum dolor sit amet consectetur. Arcu sed aliquam blandit ut magna nullam magna sagittis.', 'topsms')}
                </Text>
                
                {/* Action button */}
                <Button 
                    primary
                    className="topsms-button w-full mt-8"
                    onClick={handleClick}
                >
                    {__('Go to automations page', 'topsms')}
                </Button>
            </CardBody>
        </Card>
    );
};

export default Welcome;