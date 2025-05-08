import { __ } from '@wordpress/i18n';
import { 
    Card, 
    CardBody,
    Button,
    Icon,
} from '@wordpress/components';
import { useState } from '@wordpress/element';
import { chevronRight } from '@wordpress/icons';

// Template Tag Component
const TemplateTag = ({ tag, onClick }) => (
    <button 
        className="automation-tag-button px-3 py-1 mx-1 my-1 bg-gray-100 rounded-full text-sm text-gray-600"
        onClick={() => onClick && onClick(tag)}
    >
        {tag}
    </button>
);

const AutomationSettingsDetail = ({ status }) => {
    const [smsMessage, setSmsMessage] = useState("Hello [f_name], your order with ID [id] has been shipped and is on its way! 📦\nExpected delivery within 3-5 business days.\nIf you have any questions, feel free to contact us.");
    const [characterCount, setCharacterCount] = useState(smsMessage.length);
    // const [activeTab, setActiveTab] = useState('shipping');

    const handleMessageChange = (value) => {
        setSmsMessage(value);
        setCharacterCount(value.length);
    };

    const insertTag = (tag) => {
        setSmsMessage(smsMessage + tag);
        setCharacterCount(smsMessage.length + tag.length);
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
                                                <TemplateTag tag="[id]" onClick={insertTag} />
                                                <TemplateTag tag="[f_name]" onClick={insertTag} />
                                                <TemplateTag tag="[l_name]" onClick={insertTag} />
                                                <TemplateTag tag="[order_date]" onClick={insertTag} />
                                            </div>

                                            {/* Action Buttons */}
                                            <div className="automation-actions flex justify-between space-x-4 mt-12">
                                                <Button 
                                                    variant="secondary" 
                                                    className="automation-button-reset px-6 py-2 border border-gray-300 rounded-full"
                                                >
                                                    {__('Reset to Default', 'topsms')}
                                                </Button>
                                                <Button 
                                                    variant="primary" 
                                                    className="automation-button-save px-6 py-2 bg-blue-500 text-white rounded-full"
                                                >
                                                    {__('Save Settings', 'topsms')}
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