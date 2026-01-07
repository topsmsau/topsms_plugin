import { Card, CardBody, Icon } from '@wordpress/components';

const StatCard = ({ label, value, icon, loading }) => {
    return (
        <Card className="shadow-none bg-[#FAFAFA] border border-black/[0.07]">
            <CardBody>
                <div className="flex items-center justify-between mb-3">
                    <span className="text-lg text-gray-600">{label}</span>
                    <div className="w-10 h-10 bg-blue-300/20 rounded-lg flex items-center justify-center border border-black/[0.07]">
                        <Icon icon={icon} size={20} className="text-gray-600" />
                    </div>
                </div>
                <div className="text-3xl font-semibold text-gray-900">
                    {loading ? (
                        <div className="animate-pulse bg-gray-300 h-9 w-24 rounded"></div>
                    ) : (
                        value
                    )}
                </div>
            </CardBody>
        </Card>
    );
}

export default StatCard;