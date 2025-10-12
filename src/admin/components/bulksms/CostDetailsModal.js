import { __ } from '@wordpress/i18n';
import { 
    Flex, 
    Modal 
} from '@wordpress/components';


const CostDetailsModal = ({ 
    smsCount, 
    characterCount, 
    contactsCount, 
    costPerSms, 
    totalCost, 
    currentSmsBalance, 
    remainingSmsBalance, 
    onClose 
}) => {
    return (
        <Modal 
            title={__('Cost Details', 'topsms')} 
            onRequestClose={onClose} 
            className="bulksms-cost-modal mb-6"
        >
            <Flex direction="column" gap={3} className="p-4">
                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('SMS Number:', 'topsms')}</span>
                    <span>{smsCount}</span>
                </Flex>
                
                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('Length of the SMS:', 'topsms')}</span>
                    <span>{characterCount} {__('characters', 'topsms')}</span>
                </Flex>
                
                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('Contacts:', 'topsms')}</span>
                    <span>{contactsCount}</span>
                </Flex>
                
                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('Cost per SMS:', 'topsms')}</span>
                    <span>${costPerSms}</span>
                </Flex>
                
                <Flex justify="space-between" className="py-3 my-4 bg-gray-100 px-3 rounded">
                    <span style={{ fontWeight: 'bold', fontSize: '1.125rem' }}>
                        {__('Total:', 'topsms')}
                    </span>
                    <span style={{ fontWeight: 'bold', fontSize: '1.125rem' }}>
                        ${totalCost}
                    </span>
                </Flex>

                <hr className="mb-4" />

                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('Current Balance:', 'topsms')}</span>
                    <span>{currentSmsBalance} SMS</span>
                </Flex>

                <Flex justify="space-between" className="py-2 border-b">
                    <span className="font-medium">{__('Remaining Balance:', 'topsms')}</span>
                    <span>{remainingSmsBalance} SMS</span>
                </Flex>
            </Flex>
        </Modal>
    );
};

export default CostDetailsModal;