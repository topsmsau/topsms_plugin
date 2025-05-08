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

    // Function to handle toggle changes
    const handleToggleChange = () => {
        setStatuses((prevState) => ({
            ...prevState,
            [statusKey]: !prevState[statusKey], 
        }));
    };

    return (
        <>
            <div className="topsms-status-wrap items-center self-stretch bg-white rounded-xl flex gap-3">
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
