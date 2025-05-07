import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button,
    Icon
} from '@wordpress/components';
import { useState } from '@wordpress/element';

import StepIndicator from './StepIndicator.js';
import WelcomeIcon from './icons/WelcomeIcon.svg';

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
                <div className="mx-auto mb-6 flex items-center justify-center">
                    <Icon icon={WelcomeIcon} size={32} />
                </div>
                
                <h2 className="text-2xl font-bold mb-4">
                    {__('Fantastic! Your account has been created and will be reviewed in the next 24 hours.', 'topsms')}
                </h2>
                
                <p className="text-gray-600 mb-8 mt-8">
                    {__('Lorem ipsum dolor sit amet consectetur. Arcu sed aliquam blandit ut magna nullam magna sagittis.', 'topsms')}
                </p>
                
                {/* Action button */}
                <Button 
                    primary
                    className="topsms-button w-full mt-8"
                    onClick={handleClick}
                >
                    {__('Visit Automations', 'topsms')}
                </Button>
            </CardBody>
        </Card>
    );
};

export default Welcome;