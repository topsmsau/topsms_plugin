import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody,
    Button,
    Icon,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { chevronRight } from '@wordpress/icons';

import TemplateTag from './SmsTemplateTag';

const AutomationSettingsDetail = ({ status, statusKey, defaultTemplate }) => {
    const [smsMessage, setSmsMessage] = useState(defaultTemplate);
    const [characterCount, setCharacterCount] = useState(smsMessage.length);
    // const [activeTab, setActiveTab] = useState('shipping');
    const [isSaving, setIsSaving] = useState(false);
    const [saveSuccess, setSaveSuccess] = useState(false);

    // Fetch saved status template from the db
    useEffect(() => {
        fetchTemplateSettings();
    }, [status]);

    const handleMessageChange = (value) => {
        const cleanText = removeEmojis(value);
        setSmsMessage(cleanText);
        setCharacterCount(cleanText.length);
        setSaveSuccess(false);
    };

    // Insert sms template tag to the template
    const insertTag = (tag) => {
        setSmsMessage(smsMessage + tag);
        setCharacterCount(smsMessage.length + tag.length);
        setSaveSuccess(false);
    };

    const removeEmojis = (text) => {
    return text.replace(
        /([\u203C-\u3299]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|\uD83E[\uDD00-\uDFFF]|[\u200D\uFE0F])/g,
        ''
    );
};

    // const tabs = [
    //     {
    //         name: 'shipping',
    //         title: __('Shipping', 'topsms'),
    //         className: 'automation-tab'
    //     },
    //     {
    //         name: 'pickup',
    //         title: __('Pickup', 'topsms'),
    //         className: 'automation-tab'
    //     }
    // ];

    // Fetch status saved template from db
    const fetchTemplateSettings = async () => {
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }

            // Fetch status template from backend
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
            // console.log(`Status settings for ${statusKey}:`, data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Get the status template and get it reflected on the frontend
            const template = data.data.template;
            // console.log(`Status ${statusKey} template:  ${template}`);

             // Update the template and save default template for reset functionality
            setSmsMessage(template || smsMessage);
            // setDefaultTemplate(template || smsMessage);
            setCharacterCount((template || smsMessage).length);
        } catch (error) {
            console.error('Error fetching status settings:', error);
        } 
    };

    // Save status template to db
    const saveTemplate = async () => {
        setIsSaving(true);
        
        try {
            // Get the nonce from WordPress
            const nonce = window.wpApiSettings?.nonce;
            if (!nonce) {
                throw new Error('WordPress REST API nonce not available');
            }

            // Data to send 
            const sendData = {
                status_key: statusKey,
                template: smsMessage
            };
            console.log(`Saving ${statusKey} template:`, sendData);

            // Save status template to backend
            const response = await fetch('/wp-json/topsms/v1/automations/status/save-template', {
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
            console.log('Status template saved successfully:', data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            setSaveSuccess(true);

            // // Update the default template
            // setDefaultTemplate(smsMessage);

            // Show success message for 3 seconds
            setTimeout(() => {
                setSaveSuccess(false);
            }, 3000);
        } catch (error) {
            console.error('Error saving status template:', error);
            setIsSaving(false);
        } finally {
            setIsSaving(false);
        }
    };

    // Reset template to default
    const resetTemplate = () => {
        setSmsMessage(defaultTemplate);
        setCharacterCount(defaultTemplate.length);
    };

    return (
        <Card className="automation-card">
            <CardBody>
                {/* Navigation */}
                <div className="automation-navigation flex items-center text-gray-500 text-sm mb-6">
                    <span>{__('Automation Setting', 'topsms')}</span>
                    <Icon icon={chevronRight} size={16} className="mx-1 w-5 h-5" />
                    <span className="text-blue-500">{__('Detail', 'topsms')}</span>
                </div>

                {/* Tabs Section */}
                <div className="automation-tabs-container mb-6">
                    {/* <TabPanel
                        className="automation-tabs"
                        activeClass="active-tab bg-blue-100"
                        tabs={tabs}
                        onSelect={(tabName) => setActiveTab(tabName)}
                    > */}
                        {/* {(tab) => ( */}
                            <div className="automation-tab-content">
                                {/* SMS Template Section */}
                                <div className="automation-template">
                                    <div className="flex flex-wrap -mx-4">
                                        <div className="w-full lg:w-1/2 px-4 mb-6">
                                            <h2 className="text-lg font-medium mb-1">{__('SMS Template', 'topsms')}</h2>
                                            <p className="text-gray-500 text-sm mb-4">
                                                {__('Customize the message sent when an order status changes to ', 'topsms')} {status}
                                            </p>
                                            
                                            {/* Custom Textarea Control */}
                                            <div className="automation-textarea-container mb-4">
                                                <textarea
                                                    value={smsMessage}
                                                    onChange={(e) => handleMessageChange(e.target.value)}
                                                    className="automation-textarea w-full h-32 p-4 border border-gray-300 rounded-md"
                                                    style={{ fontFamily: 'monospace', fontSize: '14px' }}
                                                />
                                            </div>

                                            <div className="automation-tags flex flex-wrap mb-4">
                                                <TemplateTag tag="[order_id]" onClick={insertTag} />
                                                <TemplateTag tag="[first_name]" onClick={insertTag} />
                                                <TemplateTag tag="[last_name]" onClick={insertTag} />
                                            </div>

                                            {/* Success Message */}
                                            {saveSuccess && (
                                                <div className="text-blue-500 mb-4">
                                                    {__('Template saved successfully', 'topsms')}
                                                </div>
                                            )}

                                            {/* Action Buttons */}
                                            <div className="automation-actions flex justify-between space-x-4 mt-12">
                                                <Button 
                                                    variant="secondary" 
                                                    className="automation-button-reset px-6 py-2 border border-gray-300 rounded-full"
                                                    onClick={resetTemplate}
                                                >
                                                    {__('Reset to Default', 'topsms')}
                                                </Button>
                                                <Button 
                                                    variant="primary" 
                                                    className="automation-button-save px-6 py-2 bg-blue-500 text-white rounded-full"
                                                    onClick={saveTemplate}
                                                    isBusy={isSaving}
                                                    disabled={isSaving}
                                                >
                                                    {isSaving ? __('Saving...', 'topsms') : __('Save Settings', 'topsms')}
                                                </Button>
                                            </div>
                                        </div>

                                        <div className="w-full lg:w-1/2 px-4 mb-6">
                                            <h2 className="text-lg font-medium mb-1">{__('Live Preview', 'topsms')}</h2>
                                            <p className="text-gray-500 text-sm mb-4">{__('How your message will appear', 'topsms')}</p>
                                            
                                            <div className="automation-preview bg-gray-100 rounded-md p-4 mb-2">
                                                <div className="automation-preview-header flex space-x-1 mb-2">
                                                    <div className="w-3 h-3 rounded-full bg-red-500"></div>
                                                    <div className="w-3 h-3 rounded-full bg-yellow-500"></div>
                                                    <div className="w-3 h-3 rounded-full bg-green-500"></div>
                                                </div>
                                                
                                                <div className="automation-preview-content bg-gray-200 p-4 rounded-md">
                                                    <div className="whitespace-pre-line">{smsMessage}</div>
                                                </div>
                                            </div>
                                            
                                            <div className="automation-character-count text-sm text-gray-500">
                                                {__('Characters:', 'topsms')} {characterCount}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/* )} */}
                    {/* </TabPanel> */}
                </div>

                {/* Footer */}
                {/* <div className="mt-12 pt-4 border-t border-gray-200 text-gray-500 text-sm flex justify-between">
                    <div>© 2025 TopSMS All Right Reserved</div>
                    <div>
                        <a href="mailto:support@topsms.com.au" className="text-gray-500 hover:text-gray-700 mr-4">support@topsms.com.au</a>
                        <span>+61 (0) 2 9121 6234</span>
                    </div>
                </div> */}
            </CardBody>
        </Card>
    );
};

export default AutomationSettingsDetail;