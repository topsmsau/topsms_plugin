import { __ } from '@wordpress/i18n';
import { Card, CardBody, Flex, Icon } from '@wordpress/components';

import { plus } from '@wordpress/icons';

// Topup button
const TopupButton = ({ icon, children, className = '' }) => {
    // Handle click to redirect to settings page
    const handleClick = () => {
        window.location.href = '/wp-admin/admin.php?page=topsms-settings';
    };

    return (
        <button
            className={`flex items-center px-3 py-1.5 rounded-full text-gray-700 hover:bg-gray-100 bg-transparent border border-gray-200 cursor-pointer whitespace-nowrap transition-colors ${className}`}
            type='button'
            onClick={handleClick}
        >
            {icon && (
                <span className='w-4 h-4 mr-1 flex-shrink-0'>
                    <Icon icon={icon} size={16} />
                </span>
            )}
            <span className='text-sm font-medium text-gray-600'>{children}</span>
        </button>
    );
};

const BalanceCard = ({ balance }) => {
    return (
        <Card className='topsms-balance-card m-0 shadow-none'>
            <CardBody className='p-1 topsms-balance-card-body'>
                <Flex align='center' justify='space-between' gap={16}>
                    <Flex align='flex-start' direction='column' justify='space-between'>
                        <div>
                            <p className='text-gray-600 text-medium font-medium'>
                                {__('Current balance', 'topsms')}
                            </p>
                        </div>
                        <div className='flex items-center'>
                            <p className='font-bold text-base'>{balance}</p>
                        </div>
                    </Flex>

                    <TopupButton icon={plus}>{__('Top up', 'topsms')}</TopupButton>
                </Flex>
            </CardBody>
        </Card>
    );
};

export default BalanceCard;
