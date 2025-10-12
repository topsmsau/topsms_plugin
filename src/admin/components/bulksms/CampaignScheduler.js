import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';

import CustomDateTimePicker from './CustomDateTimePicker';
import SmsBalanceCard from './SmsBalanceCard';

const CampaignScheduler = ({ 
    enabledScheduled, 
    totalSms, 
    smsCount, 
    characterCount, 
    contactsCount, 
    costPerSms, 
    totalCost, 
    currentSmsBalance, 
    remainingSmsBalance, 
    listLoading,
    handleToggleChange, 
    onErrorMessage , 
    selectedDate, 
    setSelectedDate, 
    selectedTime, 
    setSelectedTime
}) => {
    return (
        <>
            <div className="bulksms-schedule-campaign-toggle items-center flex flex-1 gap-2">
                <div className="topsms-label">{__('Schedule campaign', 'topsms')}</div>
                <ToggleControl
                    __nextHasNoMarginBottom
                    label=""
                    checked={enabledScheduled}
                    onChange={handleToggleChange}
                />
            </div>

            {/* Only show the date time picker if enabled scheduled */}
            {enabledScheduled ? (
                <div className='flex items-center justify-between'>
                    <CustomDateTimePicker 
                        onErrorMessage={onErrorMessage} 
                        selectedDate={selectedDate}
                        setSelectedDate={setSelectedDate}
                        selectedTime={selectedTime}
                        setSelectedTime={setSelectedTime}
                    />
                    <SmsBalanceCard
                        balance={totalSms}
                        smsCount={smsCount}
                        characterCount={characterCount}
                        contactsCount={contactsCount}
                        costPerSms={costPerSms}
                        totalCost={totalCost}
                        currentSmsBalance={currentSmsBalance}
                        remainingSmsBalance={remainingSmsBalance}
                        listLoading={listLoading}
                    />
                </div>
            ) : (
                <div className='flex items-center justify-end'>
                    <SmsBalanceCard
                        balance={totalSms}
                        smsCount={smsCount}
                        characterCount={characterCount}
                        contactsCount={contactsCount}
                        costPerSms={costPerSms}
                        totalCost={totalCost}
                        currentSmsBalance={currentSmsBalance}
                        remainingSmsBalance={remainingSmsBalance}
                        listLoading={listLoading}
                    />
                </div>
            )}
        </>
    );
};

export default CampaignScheduler;
