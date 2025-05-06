import { useState } from '@wordpress/element';
import ToggleButton from './ToggleButton';

const SettingsAccordion = ({ settings, setSettings }) => {
    const [open, setOpen] = useState(false);

    return (
        <div className="accordion-container">
            <button className="accordion-toggle" onClick={() => setOpen(!open)}>
                ⚙ Settings
            </button>
            {open && (
                <div className="accordion-content">
                    <ToggleButton label="Enable Feature 1" optionKey="feature1" settings={settings} setSettings={setSettings} />
                    <ToggleButton label="Enable Feature 2" optionKey="feature2" settings={settings} setSettings={setSettings} />
                </div>
            )}
        </div>
    );
};

export default SettingsAccordion;
