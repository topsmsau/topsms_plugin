import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    CardFooter,
    Button,
    Icon
} from '@wordpress/components';

const PermalinkMessage = () => {
    const handleClick = () => {
        // Open permalink settings page in a new tab
        window.open('/wp-admin/options-permalink.php', '_blank');
    };
    
    return (
        <Card className="max-w-4xl mx-auto bg-white rounded-lg shadow-sm pt-6 pb-0">
            <CardBody className="text-center p-8" style={{ paddingRight: '48px', paddingLeft: '48px'}}>
                
                <h2 className="text-2xl font-bold mb-4">
                    {__('Permalink Configuration Required', 'topsms')}
                </h2>
                
                <p className="text-gray-600 mb-8">
                    {__('This plugin requires "Pretty" Permalink Structure for WordPress REST API access to function properly.', 'topsms')}
                </p>
                
                <p className="text-gray-600 mb-4">
                    {__('Configuring Permalinks for WordPress REST API:', 'topsms')}
                </p>

                <ol className='configure-permalinks-steps text-left mx-auto mb-2 pl-2'>
                    <li className='mb-2'>
                        <strong>{__('Access Permalink Settings: ', 'topsms')}</strong>{__('Navigate to Settings > Permalinks in your WordPress dashboard.', 'topsms')}
                    </li>
                    <li className='mb-2'>
                        <strong>{__('Choose a "Pretty" Permalink Structure: ', 'topsms')}</strong>{__('Select any option other than "Plain." Common choices include "Post name," "Day and name," or a custom structure.', 'topsms')}
                    </li>
                    <li className='mb-2'>
                        <strong>{__('Save Changes: ', 'topsms')}</strong>{__('Click "Save Changes" to apply the new permalink structure.', 'topsms')}
                    </li>
                </ol>
                
                {/* Action button */}
                <Button 
                    primary
                    className="topsms-button w-full mt-8"
                    onClick={handleClick}
                >
                    {__('Configure Permalinks', 'topsms')}
                </Button>
            </CardBody>
        </Card>
    );
}

export default PermalinkMessage;