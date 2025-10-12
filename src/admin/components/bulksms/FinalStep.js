import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button
} from '@wordpress/components';

import FinalStepIcon from "../icons/FinalStepIcon.png";

const CampaignFinalStep = ({ isScheduled, scheduledDate, scheduledTime }) => {
    const handleClick = () => {
        // Navigate to the campaigns page
        window.location.href = window.location.origin + '/wp-admin/admin.php?page=topsms-campaigns';
    };

    // Format the datetime for display: 2025 - 10 - 10 | Time: 13:00:00
    const formatDateTime = () => {
        if (!scheduledDate || !scheduledTime) return '';
        
        const [year, month, day] = scheduledDate.split('-');
        return `${year}-${month}-${day} | Time: ${scheduledTime}`;
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm p-8">
            <CardBody className="p-6 text-center">
                {/* Success icon */}
                <div className="mx-auto mb-6 flex items-center justify-center">
                    <img 
                        src={FinalStepIcon} 
                        alt={__('Campaign final step', 'topsms')}
                        className="max-w-[300px]"
                    />
                </div>
                
                {isScheduled ? (
                    <h2 className="text-2xl font-bold mb-4">
                        <div>{__('Fantastic! Your campaign has been successfully scheduled on', 'topsms')}</div>
                        <div className="my-2">{formatDateTime()}</div>
                        <div>{__('and will be delivered as planned.', 'topsms')}</div>
                    </h2>
                ) : (
                    <h2 className="text-2xl font-bold mb-4">
                        {__('Fantastic! Your campaign has been sent successfully.', 'topsms')}
                    </h2>
                )}
                
                {/* Action button */}
                <Button 
                    primary
                    className="topsms-button w-full mt-8"
                    onClick={handleClick}
                >
                    {__('Back to Campaigns', 'topsms')}
                </Button>
            </CardBody>
        </Card>
    );
};

export default CampaignFinalStep;