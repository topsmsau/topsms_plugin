import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { ToggleControl } from '@wordpress/components';

import VerticalStrokeIcon from '../icons/VerticalStrokeIcon';
import SettingsIcon from '../icons/SettingsIcon';

const AccordionItemStatus = ({ title, description, statusKey, statusColor, children }) => {
    const [isOpen, setIsOpen] = useState(false);
    const [isLoading, setIsLoading] = useState(true);
    const [isEnabled, setIsEnabled] = useState(true); 

    // Reference to content div
    const contentRef = useRef(null); 
    const [height, setHeight] = useState("0px"); 

    // Open the details - dynamic height render
    useEffect(() => {
        if (isOpen) {
            setHeight(`${contentRef.current.scrollHeight}px`); 
        } else {
            setHeight("0px");
        }
    }, [isOpen]);

    // Initial status settings on load (fetched from db)
    useEffect(() => {
        if (statusKey) {
            fetchStatusSettings();
        }
    }, [statusKey]);

    // Handle toggle changes
    const handleToggleChange = () => {
        const newValue = !isEnabled;

        // Update local state immediately
        setIsEnabled(newValue);

        // Save to database
        saveStatusEnabled(newValue);
    };

    // Fetch current status enabled settings from db
    const fetchStatusSettings = async () => {
        setIsLoading(true);
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setIsLoading(false);
                return;
            }

            // Fetch status settings from backend
            const response = await fetch(`/wp-json/topsms/v1/automations/status/${statusKey}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            // // Check if success 
            // if (!response.ok) {
            //     const errorData = await response.json().catch(() => null);
            //     throw new Error(
            //         errorData?.message || 
            //         `Failed to fetch status settings: ${response.status}`
            //     );
            // }

            const data = await response.json();
            console.log(`Status settings for ${statusKey}:`, data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Get the enabled settings and get it reflected on the frontend
            const enabled = data.data.enabled;
            setIsEnabled(enabled);
            // console.log(`Status ${statusKey} enabled:  ${enabled}`);
        } catch (error) {
            console.error('Error fetching status settings:', error);
        } finally {
            setIsLoading(false);
        }
    };

    // Save status enabled setting to the database
    const saveStatusEnabled = async (isEnabled) => {
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
            };
            // console.log(`Saving ${statusKey} enabled setting:`, sendData);

            // Save status enabled option to backend
            const response = await fetch('/wp-json/topsms/v1/automations/status/save', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                },
                body: JSON.stringify(sendData)
            });

            // // Check if success
            // if (!response.ok) {
            //     const errorData = await response.json().catch(() => null);
            //     throw new Error(
            //         errorData?.message || 
            //         `Failed to save status settings: ${response.status}`
            //     );
            // }

            const data = await response.json();
            console.log('Status enabled setting saved successfully:', data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }
            
            // If status is disabled, close the accordion
            if (!isEnabled) {
                setIsOpen(false);
            }
        } catch (error) {
            console.error('Error saving status enabled setting:', error);
            // Revert the toggle if saving failed
            setIsEnabled(!isEnabled);
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
                        {isLoading ? (
                            <div className="animate-pulse bg-gray-300 h-5 w-9 rounded"></div>
                        ) : (
                            <ToggleControl
                                __nextHasNoMarginBottom
                                label=""
                                checked={isEnabled}
                                onChange={handleToggleChange}
                            />
                        )}
                        <VerticalStrokeIcon />
                        <div
                            className={`open-settings ${isOpen ? 'open' : ''} ${!isEnabled ? 'opacity-50 pointer-events-none' : ''}`} 
                            onClick={() => isEnabled && setIsOpen(!isOpen)}
                        >
                            <SettingsIcon />
                        </div>
                    </div>
                </div>
            </div>

            {/* Accordion Body with Dynamic Height - Only show if enabled */}
            {isEnabled && (
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
            )}
        </>
    );
};

export default AccordionItemStatus;