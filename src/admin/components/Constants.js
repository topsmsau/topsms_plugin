import { __ } from '@wordpress/i18n';

import BannerIcon1 from './icons/AutomationBannerIcon1.svg';
import BannerIcon2 from './icons/AutomationBannerIcon2.svg';
import BannerIcon3 from './icons/AutomationBannerIcon3.svg';


export const MAX_CHARS_PER_SMS = 160;
export const CONCAT_FIXED_CHARS = 7;
export const COST_PER_SMS = 0.05;

export const DEFAULTREGISTRATIONSTEPS = [
    { name: __('Register', 'topsms') },
    { name: __('Confirm Phone Number', 'topsms') },
    { name: __('Welcome to TopSMS', 'topsms') }
];

// Australian states
export const AUSTRALIAN_STATES = [
    { value: '', label: __('Select a state', 'topsms') },
    { value: 'ACT', label: __('Australian Capital Territory', 'topsms') },
    { value: 'NSW', label: __('New South Wales', 'topsms') },
    { value: 'NT', label: __('Northern Territory', 'topsms') },
    { value: 'QLD', label: __('Queensland', 'topsms') },
    { value: 'SA', label: __('South Australia', 'topsms') },
    { value: 'TAS', label: __('Tasmania', 'topsms') },
    { value: 'VIC', label: __('Victoria', 'topsms') },
    { value: 'WA', label: __('Western Australia', 'topsms') }
];

export const ORDERSTATUSES = [
    {
        key: 'processing',
        title: 'Processing',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#17a34a', 
        defaultTemplate: "Hello [first_name], your order #[order_id] has been shipped and is on its way! Expected delivery within 3-5 business days. If you have any questions, feel free to contact us."
    },
    {
        key: 'completed',
        title: 'Completed',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#365aed', 
        defaultTemplate: "Hello [first_name], your order #[order_id] has been successfully delivered. We hope you enjoy your purchase! Thank you for shopping with us."
    },
    {
        key: 'failed',
        title: 'Failed',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44', 
        defaultTemplate: "Hello [first_name], unfortunately, your order #[order_id] could not be processed due to a payment issue. Please try again or contact us for help."
    },
    {
        key: 'refunded',
        title: 'Refunded',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#6a6f7a', 
        defaultTemplate: "Hello [first_name], your order #[order_id] has been refunded. The amount should reflect in your account shortly. Let us know if you have any questions."
    }, 
    {
        key: 'pending',
        title: 'Pending Payment',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#f90', 
        defaultTemplate: "Hello [first_name], your order #[order_id] is awaiting payment. Please complete the payment to process your order. Contact us if you need assistance."
    },
    {
        key: 'cancelled',
        title: 'Cancelled',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44',
        defaultTemplate: "Hello [first_name], your order #[order_id] has been cancelled. If this was a mistake or you need help placing a new order, feel free to reach out."
    },
    {
        key: 'on-hold',
        title: 'On Hold',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#ff3a44',
        defaultTemplate: "Hello [first_name], your order #[order_id] is currently on hold. We'll notify you as soon as it's updated. Contact us if you need more information."
    },
    {
        key: 'draft',
        title: 'Draft',
        description: 'lorem ipsum dolor sit amet condecture',
        color: '#17a34a',
        defaultTemplate: "" 
    }
];

export const TOPUPOPTIONS = [
    { 
        amount: 32.5, 
        sms: 500,
        discount: null,
        link: 'https://buy.stripe.com/28E9AS4W45WYfCh4FnbQY0a' 
    },
    { 
        amount: 150, 
        sms: 2500,
        discount: '8%',
        link: 'https://buy.stripe.com/4gM7sKbks99a3Tzc7PbQY0b' 
    },
    { 
        amount: 275, 
        sms: 5000,
        discount: '15%',
        link: 'https://buy.stripe.com/dRm00i2NWgBCgGl2xfbQY0c' 
    },
    { 
        amount: 500, 
        sms: 10000,
        discount: '23%',
        link: 'https://buy.stripe.com/fZu28qfAI1GIahX3BjbQY0d' 
    },
    { 
        amount: 2250, 
        sms: 50000,
        discount: '31%',
        link: 'https://buy.stripe.com/28E5kC4W4etucq57RzbQY0e' 
    },
    { 
        amount: 4000, 
        sms: 100000,
        discount: '38%',
        link: 'https://buy.stripe.com/8x23cuagobhi2Pvc7PbQY0f' 
    },
];

export const REVIEWCARDS = [
    {
        icon: BannerIcon1,
        title: 'Enjoying TopSMS?',
        message: "Don't forget to leave us a review — your feedback helps us grow!", 
        buttonText: 'Leave a review',
        link: 'https://wordpress.org/plugins/topsms/#reviews'
    },
    {
        icon: BannerIcon2,
        title: 'Got ideas for new features?',
        message: "Help shape the future of TopSMS by voting or suggesting new features.", 
        buttonText: 'Request a feature',
        link: 'https://topsms.canny.io/'
    },
    {
        icon: BannerIcon3,
        title: 'Need something tailored to your business?',
        message: "We offer custom development services to make TopSMS work exactly how you need it.", 
        buttonText: 'Customisation services',
        link: 'https://eux.com.au/contact-us/'

    }
];

export const SMS_TAGS = {
    first_name: {
        tag: '[first_name]',
        replacement: 'aabbccd',
        label: 'First Name', 
        message: 'Customer first name (avg. 7 characters)'
    },
    last_name: {
        tag: '[last_name]',
        replacement: 'aabbccd',
        label: 'Last Name', 
        message: 'Customer last name (avg. 7 characters)'
    },
    mobile: {
        tag: '[mobile]',
        replacement: '0412345678', 
        label: 'Mobile', 
        message: 'Customer mobile number (avg. 10 digits)'
    },
    city: {
        tag: '[city]',
        replacement: 'aabbccd', 
        label: 'City', 
        message: 'Customer city (avg. 7 characters)'
    },
    state: {
        tag: '[state]',
        replacement: 'aaa', 
        label: 'State', 
        message: 'Customer state (avg. 3 characters)' 
    },
    postcode: {
        tag: '[postcode]',
        replacement: '2000', 
        label: 'Postcode', 
        message: 'Customer postcode (4 digits)' 
    },
    orders: {
        tag: '[orders]',
        replacement: '10',
        label: 'Orders', 
        message: 'Total order count (avg. 2 digits)' 
    },
    total_spent: {
        tag: '[total_spent]',
        replacement: '250.00',
        label: 'Total Spent', 
        message: 'Total amount spent (e.g. 250.00)' 
    },
    url: {
        tag: '[url]',
        replacement: 'topsms.au/abcd/',
        label: 'URL', 
        message: 'Shorten URL (15 characters)' 
    },
    unsubscribe: {
        tag: '[unsubscribe]',
        replacement: 'unsub.au/abcdef',
        label: 'Unsubscribe', 
        message: 'Unsubscribe link (15 characters)' 
    }
};

export const MESSAGE_TEMPLATES = [
    {
        value: '',
        label: __('Select a template', 'topsms'),
        message: ''
    },
    {
        value: 'template1',
        label: __('Promotional Campaign', 'topsms'),
        message: "Hi [first_name], our new {product_category} range just landed!\nEnjoy {discount}% off until {end_date}.\nShop now [url]\n\nShop Name\n\n[unsubscribe]"
    },
    {
        value: 'template2',
        label: __('Last Minute Offer', 'topsms'),
        message: "Flash Sale! [first_name], get {discount}% off {product_name}  - only {hours_left} hours left.\nDon’t miss it [url]\n\nShop Name\n\n[unsubscribe]"
    },
    {
        value: 'template3',
        label: __('Special Event', 'topsms'),
        message: "Hey [first_name], join us for our {event_name}!\n{event_date} at {location}.\n\nRSVP now: [url]\n\nShop Name\n\n[unsubscribe]"
    }
];

