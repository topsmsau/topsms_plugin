import { useState } from '@wordpress/element';

const ToggleButton = ({ label, optionKey, settings, setSettings }) => {
    const handleToggle = () => {
        setSettings({ ...settings, [optionKey]: !settings[optionKey] });
    };

    return (
        <div className="toggle-container">
            <label>{label}</label>
            <button className={`toggle-btn ${settings[optionKey] ? 'active' : ''}`} onClick={handleToggle}>
                {settings[optionKey] ? 'ON' : 'OFF'}
            </button>
        </div>
    );
};

export default ToggleButton;
