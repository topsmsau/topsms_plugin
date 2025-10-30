import { __ } from '@wordpress/i18n';
import { memo, useEffect, useState, useCallback } from '@wordpress/element';

import CustomSelect from '../components/CustomSelect.js';
import CustomInput from '../components/CustomInput.js';
import SmsTag from './SmsTag.js';
import { 
    MAX_CHARS_PER_SMS, 
    CONCAT_FIXED_CHARS, 
    SMS_TAGS, 
    MESSAGE_TEMPLATES 
} from '../constants.js';

// Memoize components
const CustomInput_ = memo(CustomInput);
const CustomSelect_ = memo(CustomSelect);

const BulkSmsForm = ({
    formData,
    setFormData,
    setListLoading, 
    characterCount,
    setCharacterCount,
    smsCount,
    setSmsCount,
    errors,
    setCurrentSmsBalance,
    setContactsCount, 
    onErrorMessage
}) => {
    const [lists, setLists] = useState([
        { value: '', label: __('Select a list', 'topsms') }
    ]);
    const [template, setTemplate] = useState('');
    const [senderLoading, setSenderLoading] = useState(true);
    const [listsLoading, setListsLoading] = useState(true);

    const smsTags = Object.values(SMS_TAGS).map(t => t.tag);
    const templateList = MESSAGE_TEMPLATES.map(({ value, label }) => ({ value, label }));

    // Update sms count when character count changes
    useEffect(() => {
        let count;
        if (characterCount <= MAX_CHARS_PER_SMS) {
            count = characterCount > 0 ? 1 : 0;
        } else {
            count = Math.ceil(characterCount / (MAX_CHARS_PER_SMS - CONCAT_FIXED_CHARS));
        }
        setSmsCount(count);
    }, [characterCount, setSmsCount]);

    // Calculate the maximum allowed characters based on current sms count
    const getMaxCharactersForCurrentSms = () => {
        if (smsCount <= 1) {
            return MAX_CHARS_PER_SMS; 
        }
        return smsCount * (MAX_CHARS_PER_SMS - CONCAT_FIXED_CHARS); 
    };
    const maxCharsAllowed = getMaxCharactersForCurrentSms();

    // Generate tag replacements for characters count
    const getTagReplacements = () => {
        return Object.values(SMS_TAGS).reduce((acc, { tag, replacement }) => {
            // If replacement is empty, use the tag itself for counting
            acc[tag] = replacement || tag;
            return acc;
        }, {});
    };

    // Calculate actual character count with tag replacements
    const calculateActualCharacterCount = (message) => {
        let message_ = message;
        
        let tagReplacements = getTagReplacements();
        // Replace all tags with their fixed replacement values
        Object.keys(tagReplacements).forEach(tag => {
            const replacement = tagReplacements[tag];
            message_ = message_.replace(new RegExp(tag.replace(/[[\]]/g, '\\$&'), 'g'), replacement);
        });
        
        // Replace newlines with 'n' 
        message_ = message_.replace(/(\r\n|\r|\n)/g, 'n');
        
        return message_.length;
    };

    // Sanitise the emojis from the text
    const removeEmojis = (text) => {
        return text.replace(
            /([\u203C-\u3299]|\uD83C[\uDC00-\uDFFF]|\uD83D[\uDC00-\uDFFF]|\uD83E[\uDD00-\uDFFF]|[\u200D\uFE0F])/g,
            ''
        );
    };

    // Insert sms template tag to the sms
    const insertTag = (tag) => {
        // Check if tag already exists in the message
        if (formData.smsMessage.includes(tag)) {
            onErrorMessage(__(`Tag ${tag} already exists in the message`, 'topsms'));
            return;
        }

        const newMessage = formData.smsMessage + tag;
        const actualCharCount = calculateActualCharacterCount(newMessage);
        
        setFormData(prev => ({ ...prev, smsMessage: newMessage }));
        setCharacterCount(actualCharCount);
    };

    // Handle campaign name change
    const handleCampaignNameChange = useCallback((value) => {
        setFormData(prev => ({ ...prev, campaignName: value }));
    }, [setFormData]);

    const getList = async (value) => {
        setListLoading(true);
        setFormData(prev => ({ ...prev, list: value }));
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setSenderLoading(false);
                return;
            }

            // Fetch contact list from backend
            const response = await fetch(`/wp-json/topsms/v2/bulksms/lists/${value}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();
            // console.log("list data:", data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Update count state
            const count = data.data.count;
            setContactsCount(count);
        } catch (error) {
            console.error('Error fetching contact list data:', error);
            
            // Notify error
            onErrorMessage(__('Failed to load contact list data. Please refresh and try again.', 'topsms'));
        } finally {
            setListLoading(false);
        }        
    }

    // Handle list change
    const handleListChange = useCallback(async (value) => {
        getList(value);
    }, [setFormData]);

    // Handle template change
    const handleTemplateChange = useCallback((value) => {
        setTemplate(value);

        // Get the selected template message
        const templateData = MESSAGE_TEMPLATES.find(t => t.value === value);
        const templateMessage = templateData?.message || '';
        
        if (templateMessage) {
            const actualCharCount = calculateActualCharacterCount(templateMessage);
            setFormData(prev => ({ ...prev, smsMessage: templateMessage }));
            setCharacterCount(actualCharCount);
        } else {
            setFormData(prev => ({ ...prev, smsMessage: '' }));
            setCharacterCount(0);
        }
    }, [setTemplate, setFormData, setCharacterCount]);

    // Handle message change
    const handleMessageChange = (value) => {
        const cleanText = removeEmojis(value);

        let newText = cleanText;
        const removedTags = [];

        // Remove duplicate tags in the message
        smsTags.forEach(tag => {
            const escapedTag = tag.replace(/[[\]]/g, '\\$&');
            const regex = new RegExp(escapedTag, 'g');
            const matches = newText.match(regex);
            
            if (matches && matches.length > 1) {
                removedTags.push(tag);
                const firstIndex = newText.indexOf(tag);

                // Keep first occurrence and remove all others
                newText = newText.substring(0, firstIndex + tag.length) + 
                    newText.substring(firstIndex + tag.length).replace(regex, '');
            }
        });

        // Show error message if duplicates were removed
        if (removedTags.length > 0) {
            onErrorMessage(__(`Duplicate tags aren't not allowed`, 'topsms'));
        }

        // Calculate actual character count with tag replacements
        const actualCharCount = calculateActualCharacterCount(newText);
        setFormData(prev => ({ ...prev, smsMessage: newText }));
        setCharacterCount(actualCharCount);
    };

    // Handle url change
    const handleUrlChange = useCallback((value) => {
        setFormData(prev => ({ ...prev, url: value }));
    }, [setFormData]);

    // Fetch sender name
    const fetchSender = async () => {
        setSenderLoading(true);
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setSenderLoading(false);
                return;
            }

            // Fetch user data from backend
            const response = await fetch('/wp-json/topsms/v1/user', {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Update the form sender state
            const sender_ = data.data.data.sender;
            setFormData(prev => ({ ...prev, sender: sender_ }));

            // Get the user balance
            const balance_ = data.data.data.balance;
            setCurrentSmsBalance(balance_);
        } catch (error) {
            console.error('Error fetching sender name:', error);
            
            // Notify error
            onErrorMessage(__('Failed to load sender name. Please refresh and try again.', 'topsms'));
        } finally {
            setSenderLoading(false);
        }
    }

    // Fetch all contacts list
    const fetchLists = async () => {
        setListsLoading(true);
        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                setListsLoading(false);
                return;
            }

            // Fetch lists from backend
            const response = await fetch(`/wp-json/topsms/v2/bulksms/lists`, {
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
            // console.log(`lists:`, data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Transform the data to the local list format
            const lists_ = [
                { value: '', label: __('Select a list', 'topsms') },
                ...Object.values(data.data.filters).map(filter => ({
                    value: filter.id || filter.filter_id,
                    label: `${filter.name || filter.filter_name} (${filter.count})`
                }))
            ];
            
            setLists(lists_);
        } catch (error) {
            console.error('Error fetching lists:', error);
            
            // Notify parent of error
            onErrorMessage(__(`Failed to load lists. Please refresh and try again.`, 'topsms'));
        } finally {
            setListsLoading(false);
        }
    }

    // Fetch sender and lists on load (fetched from db)
    useEffect(() => {
        const init = async () => {
            fetchSender(); 
            await fetchLists();

            if (formData.list || !listsLoading) {
                getList(formData.list);
            }
        };
        init();
    }, []);

    // Update character count on initial load if message exists
    useEffect(() => {
        if (formData.smsMessage) {
            const actualCharCount = calculateActualCharacterCount(formData.smsMessage);
            setCharacterCount(actualCharCount);
        }
    }, []);

    return (
        <>
            <CustomInput_
                key="campaign-name-field"
                label={__('Campaign Name', 'topsms')}
                value={formData.campaignName}
                onChange={handleCampaignNameChange}
                error={errors.campaignName}
                required
            />

            {listsLoading ? (
                <div className="mb-4">
                    <div className="topsms-label">{__('To', 'topsms')}</div>
                    <div className="animate-pulse bg-gray-300 h-[46px] w-full rounded"></div>
                </div>
            ) : (
                <CustomSelect_
                    key="list-field"
                    label={__('To', 'topsms')}
                    value={formData.list}
                    options={lists}
                    onChange={handleListChange}
                    error={errors.list}
                    required
                >
                    <p className="text-xs text-gray-600 italic">
                        {__("Contacts who have been subscribed and have phone number", 'topsms')}
                    </p>
                </CustomSelect_>
            )}
            
            <div className="topsms-label">{__('From', 'topsms')}</div>
            {senderLoading ? (
                <div className="mb-4">
                    <div className="animate-pulse bg-gray-300 h-[46px] w-full rounded"></div>
                </div>
            ) : (
                <CustomInput
                    key="sender-field"
                    placeholder={__('Sender Name to be shown in the SMS', 'topsms')}
                    value={formData.sender}
                    disabled={true}
                    maxLength={11}
                    required
                />
            )}

            <CustomSelect_
                key="templates-field"
                label={__('Template', 'topsms')}
                value={template}
                options={templateList}
                onChange={handleTemplateChange}
                required
            />

            {/* Custom Textarea Control */}
            <div className="topsms-label">Message</div>
            <div className="bulksms-textarea-container relative">
                <textarea
                    value={formData.smsMessage}
                    onChange={(e) => handleMessageChange(e.target.value)}
                    className="bulksms-textarea w-full h-32 p-4 pb-12 border border-gray-300 rounded-md"
                    style={{ fontSize: '14px' }}
                />
                
                {/* Character count - position it at the bottom-right of the text area */}
                <div className="absolute bottom-[1rem] right-[1rem] flex items-center gap-[12px] text-sm text-gray-500">
                    {characterCount}/{maxCharsAllowed} 
                    <span className="bg-gray-100 rounded-full text-blue-500 px-3 py-[6px]">
                        {smsCount} {__('SMS', 'topsms')}
                    </span>
                </div>
            </div>

            <div className="bulksms-tags flex items-center flex-wrap mb-4">
                {__('Personalisation', 'topsms')}
                {Object.values(SMS_TAGS).map(({ tag, message }) => (
                    <SmsTag 
                        key={tag}
                        tag={tag}
                        message={message}
                        onClick={insertTag} 
                    />
                ))}
            </div>

            <CustomInput_
                key="url-field"
                placeholder={__('Add URL', 'topsms')}
                value={formData.url}
                onChange={handleUrlChange}
            />
        </>
    );
};

export default BulkSmsForm;