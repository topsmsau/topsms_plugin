import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';

import VerticalStrokeIcon from '../icons/VerticalStrokeIcon';
import SettingsIcon from '../icons/SettingsIcon';

const AccordionItemStatus = ({ title, description, statusKey, statusColor, children }) => {
    const [isOpen, setIsOpen] = useState(false);

    // Reference to content div
    const contentRef = useRef(null); 
    const [height, setHeight] = useState("0px"); 

    useEffect(() => {
        if (isOpen) {
            setHeight(`${contentRef.current.scrollHeight}px`); 
        } else {
            setHeight("0px");
        }
    }, [isOpen]);

    // State for toggles
    const [statuses, setStatuses] = useState({
        processing: false,
        completed: true,
        failed: true,
        refunded: true,
        pending_payment: true,
        cancelled: true,
        onhold: true,
        draft: true,
    });

    // Initial status settings on load (fetched from db)
    useEffect(() => {
        fetchStatusSettings();
    }, [statusKey]);

    // Handle toggle changes
    const handleToggleChange = () => {
        const newValue = !statuses[statusKey];
        
        // Update local state immediately
        setStatuses(prevState => ({
            ...prevState,
            [statusKey]: newValue
        }));
        
        // Save to database
        saveStatusSettings(newValue);
    };

    // Fetch current status settings from the database
    const fetchStatusSettings = async () => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }

            // Fetch status settings
            const response = await fetch(`/wp-json/topsms/v1/settings/status/${statusKey}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            if (!response.ok) {
                throw new Error(`Failed to fetch status settings: ${response.status}`);
            }

            const data = await response.json();
            
            // Update local state with the fetched data
            if (data && data.enabled !== undefined) {
                setStatuses(prevState => ({
                    ...prevState,
                    [statusKey]: data.enabled
                }));
            }

        } catch (error) {
            console.error('Error fetching status settings:', error);
        }
    };

    // Save status settings to the database
    const saveStatusSettings = async (isEnabled) => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Data to send 
            const sendData = {
                status_key: statusKey,
                    enabled: isEnabled
            }

            // Save status settings
            const response = await fetch('/wp-json/topsms/v1/settings/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            if (!response.ok) {
                const errorData = await response.json().catch(() => null);
                throw new Error(
                    errorData?.message || 
                    `Failed to save status settings: ${response.status}`
                );
            }

            const data = await response.json();
            console.log('Status settings saved successfully:', data);
            
            // If saved status is toggled off, close the accordion
            if (!isEnabled) {
                setIsOpen(false);
            }

        } catch (error) {
            console.error('Error saving status settings:', error);
            
            // Revert the toggle if saving failed
            setStatuses(prevState => ({
                ...prevState,
                [statusKey]: !isEnabled
            }));
        }
    };


    return (
        <>
            <div className="topsms-status-wrap items-center self-stretch bg-white rounded-xl flex gap-3 p-[0.75rem] border border-black/[0.07]">
                <span className={`status status-${statusKey} rounded-full h-8 w-[3px]`} style={{ backgroundColor: statusColor }}></span>
                <div className="status-detail-wrap items-center flex flex-1 gap-2">
                    <div className="status-detail items-start flex flex-1 flex-col gap-[2px] justify-center">
                        <h5 className="text-gray-800 font-bold text-medium">{__(title, 'topsms')}</h5>
                        <span>{__(description, 'topsms')}</span>
                    </div>
                    <div className="status-control items-center flex gap-[12px]">
                        <ToggleControl
                            __nextHasNoMarginBottom
                            label=""
                            checked={statuses[statusKey]}
                            onChange={handleToggleChange}
                        />
                        <VerticalStrokeIcon />
                        <div
                            className={`open-settings ${isOpen ? 'open' : ''}`} 
                            onClick={() => setIsOpen(!isOpen)}
                        >
                            <SettingsIcon />
                        </div>
                    </div>
                </div>
            </div>


            {/* Accordion Body with Dynamic Height */}
            <div
                className="topsms-accordion-body-wrap"
                ref={contentRef}
                style={{
                    maxHeight: height,
                    opacity: isOpen ? 1 : 0,
                    transition: "max-height 0.5s ease-in-out, opacity 0.5s ease-in-out",
                    overflow: "hidden",
                }}
            >
                {children}
            </div>
        </>
    );
};

export default AccordionItemStatus;
