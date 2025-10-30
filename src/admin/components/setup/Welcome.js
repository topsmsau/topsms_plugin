import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button,
    Icon
} from '@wordpress/components';

import StepIndicator from './StepIndicator.js';
import WelcomeIcon from '../icons/WelcomeIcon.svg';

const Welcome = ({ }) => {
    const handleClick = () => {
        // Navigate to the automations page
        window.location.href = '/wp-admin/admin.php?page=topsms-automations';
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
                
                <p className="text-gray-600 mt-8">
                    {__('Our team is reviewing your details.', 'topsms')}
                </p>
                <p className="text-gray-600 mt-2">
                    {__('If you need urgent activation, call 02 9121 6234 to fast-track your verification.', 'topsms')}
                </p>
                <p className="text-gray-600 mb-8 mt-2 italic">
                    {__('(Business hours Mon - Fri, 8 am - 5:30 pm AEST)', 'topsms')}
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