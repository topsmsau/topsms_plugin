import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    CardFooter,
    Button,
    Icon
} from '@wordpress/components';

import WelcomeIcon from './icons/WelcomeIcon.svg';

const Connected = () => {
    const handleClick = () => {
        // Navigate to the automations page
        window.location.href = '/wp-admin/admin.php?page=topsms-automations';
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm pt-6 pb-0">
            <CardBody className="text-center p-8" style={{ paddingRight: '48px', paddingLeft: '48px'}}>
                <div className="mx-auto mb-4 flex items-center justify-center">
                    <Icon icon={WelcomeIcon} size={32} />
                </div>
                
                <h2 className="text-2xl font-bold mb-4">
                    {__('Your WordPress has been connected with TopSMS!', 'topsms')}
                </h2>
                
                <p className="text-gray-600 mb-8">
                    {__('Now, you can manage and send SMS directly from your WordPress dashboard. All SMS messages, notifications, and campaigns can be easily controlled in one place.', 'topsms')}
                </p>
                
                <p className="text-gray-600 mb-4">
                    {__('Access settings and start sending SMS at WordPress Dashboard → TopSMS.', 'topsms')}
                </p>
                
                <p className="text-gray-600 mb-6">
                    {__('Enjoy this convenience? Make sure to explore all the features of TopSMS on your dashboard now!', 'topsms')}
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

            {/* Copyright in CardFooter */}
            <CardFooter justify="center" className="text-center pt-4 pb-2">
                <p className="text-gray-500 text-sm text-center">
                    {__('©2025 TopSMS All Right Reserved', 'topsms')}
                </p>
            </CardFooter>
        </Card>
    );
}

export default Connected;