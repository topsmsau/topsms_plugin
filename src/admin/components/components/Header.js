import { __ } from '@wordpress/i18n';
import { 
    Card,
    CardBody,
    Flex,
    FlexItem,
    Icon,
} from '@wordpress/components';

import TopsmsIcon from '../icons/TopsmsLogo.svg';
import BalanceCard from './BalanceCard';

const Header = () => {
    return (
        <Card className="topsms-header mb-4 border-0 shadow-none">
            <CardBody className="topsms-header-card-body p-0">
                <Flex align="center" gap={4}>
                    <FlexItem gap={2}>
                        <Flex>
                            {/* Logo */}
                            <FlexItem>
                                <div className="topsms-logo-container bg-gray-800 rounded-full w-12 h-12 flex items-center justify-center">
                                    <Icon icon={TopsmsIcon} size={32} />
                                </div>
                            </FlexItem>

                            {/* Header Text */}
                            <FlexItem>
                                <h2 className="m-0 text-xl font-semibold">
                                    {__('TopSMS', 'topsms')}
                                </h2>
                                <p variant="muted" className="text-sm text-gray-600">
                                    {__('Configure automated SMS notifications for your customers', 'topsms')}
                                </p>
                            </FlexItem>
                        </Flex>
                    </FlexItem>

                    {/* Balance Info */}
                    <FlexItem>
                        <BalanceCard />
                    </FlexItem>
                </Flex>
            </CardBody>
        </Card>
    );
};

export default Header;