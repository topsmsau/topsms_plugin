import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

import CustomInput from '../components/CustomInput.js';

const CustomDateTimePicker = ({ selectedDate, setSelectedDate, selectedTime, setSelectedTime, onErrorMessage }) => {
    const [minimumDate, setMinimumDate] = useState('');
    const [minimumTime, setMinimumTime] = useState('');

    // Set minimum date to the current date 
    useEffect(() => {
        const today = new Date();
        const year = today.getFullYear();
        const month = String(today.getMonth() + 1).padStart(2, '0');
        const day = String(today.getDate()).padStart(2, '0');
        setMinimumDate(`${year}-${month}-${day}`);
    }, []);

    // Update minimum time when date changes
    useEffect(() => {
        if (selectedDate === minimumDate) {
            // If today is selected, set minimum time to 1 hour from now (+ 1 hour)
            const now = new Date();
            now.setHours(now.getHours() + 1); 
            
            const hours = String(now.getHours()).padStart(2, '0');
            const minutes = String(now.getMinutes()).padStart(2, '0');
            const minimumAllowedTime = `${hours}:${minutes}`;
            setMinimumTime(minimumAllowedTime);
            
            // If selected time is before minimum time, reset it
            if (selectedTime < minimumAllowedTime) {
                setSelectedTime(minimumAllowedTime);
            }
        } else {
            // Future date selected, no time restriction
            setMinimumTime('00:00');
        }
    }, [selectedDate, minimumDate, selectedTime]);

    const handleTimeChange = (newTime) => {
        // Validate time if today is selected
        if (selectedDate === minimumDate && newTime < minimumTime) {
            onErrorMessage(__('Please select a date and time at least 1 hour from now', 'topsms'));
            setSelectedTime(minimumTime);
        } else {
            setSelectedTime(newTime);
        }
    };

    return (
        <div className="flex gap-4 mt-6">
            {/* Date Picker */}
            <div className="flex flex-col gap-3">
                <CustomInput
                    key="date-field"
                    label={__('Date', 'topsms')}
                    type="date"
                    value={selectedDate}
                    onChange={setSelectedDate}
                    min={minimumDate}
                    required
                />
            </div>

            {/* Time Picker */}
            <div className="flex flex-col gap-3">
                <CustomInput
                    key="time-field"
                    label={__('Time', 'topsms')}
                    type="time"
                    value={selectedTime}
                    onChange={handleTimeChange}
                    min={selectedDate === minimumDate ? minimumTime : undefined}
                    step="60"
                    required
                    className={selectedDate === minimumDate ? 'time-restricted' : ''}
                />
            </div>
        </div>
    );
};

export default CustomDateTimePicker;