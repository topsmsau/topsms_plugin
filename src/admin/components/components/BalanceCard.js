import { __ } from '@wordpress/i18n';
import { 
    Card,
    CardBody,
    Flex,
    Icon,
} from '@wordpress/components';

import { plus } from '@wordpress/icons';

// Topup button
const TopupButton = ({ icon, children, onClick, className = '' }) => {
    return (
        <button 
            className={`flex items-center px-3 py-1.5 rounded-full text-gray-700 hover:bg-gray-100 bg-transparent border border-gray-200 cursor-pointer whitespace-nowrap transition-colors ${className}`}
            type="button"
            onClick={onClick}
        >
            {icon && (
                <span className="w-4 h-4 mr-1 flex-shrink-0">
                    <Icon icon={icon} size={16} />
                </span>
            )}
            <span className="text-sm font-medium text-gray-600">
                {children}
            </span>
        </button>
    );
};

const BalanceCard = () => {
    return (
        <Card className="topsms-balance-card m-0 shadow-none">
            <CardBody className="p-1 topsms-balance-card-body">
                <Flex align="center" justify="space-between" gap={16}>
                    <Flex align="flex-start" direction="column" justify="space-between">
                        <div>
                            <p className="text-gray-600 text-medium font-medium">
                                {__('Current balance', 'topsms')}
                            </p>
                        </div>
                        <div className="flex items-center">
                            <p className="font-bold text-base">
                                $0.98
                            </p>
                        </div>
                    </Flex>

                    <TopupButton 
                        icon={plus} 
                    >
                        {__('Top up', 'topsms')}
                    </TopupButton>
                </Flex>

                {/* <p variant="muted" className="text-xs text-gray-500">
                    {__('Approximately', 'topsms')} <span className="text-blue-500 font-medium">95 SMS</span> {__('messages remaining', 'topsms')}
                </p> */}
            </CardBody>
        </Card>
    )
};

export default BalanceCard;