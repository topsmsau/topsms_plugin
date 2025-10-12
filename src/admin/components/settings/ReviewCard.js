import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody,
    Button,
    Icon
} from '@wordpress/components';

const ReviewCard = ({
    icon, 
    title, 
    message, 
    buttonText, 
    link, 
    className = ''
}) => {
    return (
        <Card className={`text-center ${className}`}>
            <CardBody className="p-4 flex flex-col items-center">
                {/* Icon */}
                <div className={`mb-2`}>
                    <Icon icon={icon} size={24} />
                </div>
                
                {/* Title */}
                <h3 className="font-bold text-lg mb-2">{title}</h3>
                
                {/* Message */}
                <p className="text-sm text-gray-600 mb-4">{message}</p>
                
                {/* Button */}
                <Button 
                    onClick={() => {
                        // Open the link in a new tab, except for "#" links which should stay on the page
                        if (link && link !== '#') {
                            window.open(link, '_blank');
                        }
                    }}
                    variant="primary"
                    className="w-full justify-center rounded-full bg-blue-500"
                >
                    {buttonText}
                </Button>
            </CardBody>
        </Card>
    );
};

export default ReviewCard;