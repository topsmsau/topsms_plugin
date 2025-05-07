import { useState, useRef, useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import StatusToggleControl from './StatusToggleControl';
import VerticalStrokeIcon from './../icons/VerticalStrokeIcon';
import SettingsIcon from './../icons/SettingsIcon';

const AccordionItemStatus = ({ title, description, statusKey, children }) => {
    const [isOpen, setIsOpen] = useState(false);

    const contentRef = useRef(null); // Reference to content div
    const [height, setHeight] = useState("0px"); // Default closed height

    useEffect(() => {
        if (isOpen) {
            setHeight(`${contentRef.current.scrollHeight}px`); // Get actual height
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
        pendingpayment: true,
        cancelled: true,
        onhold: true,
        draft: true,
    });

    // Function to handle toggle changes
    const handleToggleChange = () => {
        setStatuses((prevState) => ({
            ...prevState,
            [statusKey]: !prevState[statusKey], // Toggle only the specific key
        }));
    };

    return (
        <>
            <div className="topsms-status-wrap">
                <span className={`status status-${statusKey}`}></span>
                <div className="status-detail-wrap">
                    <div className="status-detail">
                        <h5>{__(title, 'topsms')}</h5>
                        <span>{__(description, 'topsms')}</span>
                    </div>
                    <div className="status-control">
                        {/* <div className="status-toggle-wrap">
                            <input type="checkbox" id="processing" className="changestatus" value="processing" />
                            <label></label>
                        </div> */}
                        <StatusToggleControl
                            label=""
                            value={statuses[statusKey]}
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
            {/* {isOpen && <div className={`topsms-accordion-body-wrap ${isOpen ? 'open' : ''}`}>{children}</div>} */}
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
