import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody, 
    Button, 
    Flex, 
    FlexItem 
} from '@wordpress/components';
import { useState } from '@wordpress/element';

import CostDetailsModal from './CostDetailsModal';

const SmsBalanceCard = ({ 
    balance, 
    smsCount, 
    characterCount, 
    contactsCount, 
    costPerSms, 
    currentSmsBalance, 
    remainingSmsBalance, 
    totalCost, 
    listLoading
}) => {
    const [showCostDetailsModal, setShowCostDetailsModal] = useState(false);

    return (
        <Card className='bulksms-balance-card m-0 shadow-none'>
            <CardBody className='p-4'>
                <Flex direction="column" align="end" gap={2}>
                    <FlexItem>
                        <p className='text-sm font-medium text-gray-600'>
                            {__('Total', 'topsms')}
                        </p>
                    </FlexItem>
                    <FlexItem>
                        {listLoading ? (
                            <div className="animate-pulse bg-gray-300 h-8 w-32 rounded"></div>
                        ) : (
                            <div className='text-2xl font-bold text-gray-900'>
                                {balance} SMS
                            </div>
                        )}
                    </FlexItem>
                    <FlexItem>
                        <Button
                            variant="link"
                            className="text-blue-500 hover:text-blue-700 text-sm p-0"
                            onClick={() => setShowCostDetailsModal(true)}
                        >
                            {__('View cost details', 'topsms')}
                        </Button>
                    </FlexItem>
                </Flex>

                {showCostDetailsModal && (
                    <CostDetailsModal 
                        smsCount={smsCount}
                        characterCount={characterCount}
                        contactsCount={contactsCount}
                        costPerSms={costPerSms}
                        totalCost={totalCost}
                        currentSmsBalance={currentSmsBalance}
                        remainingSmsBalance={remainingSmsBalance}
                        onClose={() => setShowCostDetailsModal(false)}
                    />
                )}
            </CardBody>
        </Card>
    );
};

export default SmsBalanceCard;