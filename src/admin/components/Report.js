import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';

import Layout from './components/Layout';
import StatCard from './components/StatCard';
import SmsPreview from './bulksms/SmsPreview.js';

import CampaignCostLogo from './icons/CampaignCostLogo.svg';
import TotalContactsLogo from './icons/TotalContactsLogo.svg';
import ClicksLogo from './icons/ClicksLogo.svg';
import OrdersLogo from './icons/OrdersLogo.svg';
import RevenueLogo from './icons/RevenueLogo.svg';
import ConversionRateLogo from './icons/ConversionRateLogo.svg';
import SmsSentLogo from './icons/SmsSentLogo.svg';
import SmsNotDeliveredLogo from './icons/SmsNotDeliveredLogo.svg';
import SmsDeliveredLogo from './icons/SmsDeliveredLogo.svg';


const Report = () => {
    const [loading, setLoading] = useState(true);
    const [reportData, setReportData] = useState(null);

    // State for snackbar message
    const [showSnackbar, setShowSnackbar] = useState(false);
    const [snackbarMessage, setSnackbarMessage] = useState('');
    const [snackbarStatus, setSnackbarStatus] = useState('success'); // 'success', 'error', 'info'

    // Handle success message from components
    const handleSuccessMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('success');
        setShowSnackbar(true);
        
        // Auto-dismiss the success message after 3 seconds
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 3000);
    };
    
    // Handle error message from components
    const handleErrorMessage = (message) => {
        setSnackbarMessage(message);
        setSnackbarStatus('error');
        setShowSnackbar(true);
        
        // Auto-dismiss the error message after 5 seconds
        setTimeout(() => {
            setShowSnackbar(false);
            setSnackbarMessage('');
        }, 5000);
    };
    
    // Handle dismissing the snackbar
    const handleDismissSnackbar = () => {
        setShowSnackbar(false);
        setSnackbarMessage('');
    };

    // Get campaign id from url params.
    const urlParams = new URLSearchParams(window.location.search);
    const campaignId = urlParams.get('campaign_id');

    useEffect(() => {
        fetchCampaignReport();
    }, [campaignId]);

    const fetchCampaignReport = async () => {
        setLoading(true);

        try {
            // Get the nonce from WordPress
            const nonce = window.topsmsNonce?.nonce;
            if (!nonce) {
                console.error('WordPress REST API nonce not available');
                return;
            }

            // Fetch report data from backend
            const response = await fetch(`/wp-json/topsms/v2/campaign/report/${campaignId}`, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': nonce
                }
            });

            const data = await response.json();
            // console.log("report data:", data);

            if (!data.success) {
                throw new Error(data.data.message || 'Unknown error');
            }

            // Get the report data
            setReportData(data.data);
        } catch (error) {
            console.error('Error fetching report data:', error);
            
            // Notify error
            handleErrorMessage(__('Failed to load report data. Please refresh and try again.', 'topsms'));
        } finally {
            setLoading(false);
        }
    }

    // Get stats to display.
    const getStats = () => {
        if (!reportData || !reportData.summary) {
            return null;
        }

        const summary = reportData.summary;
        const statusCount = summary.status_wise_count || {};
        const clicks = summary.clicks || {};

        const total_sms_count = summary.total_sms_count || 0;
        const smsDelivered = statusCount.delivered || 0;
        const smsNotDelivered = total_sms_count - smsDelivered;

        const utmData = reportData.utm;
        const totalOrders = utmData.total_orders || 0;
        const revenue = utmData.total_revenue || 0;
        const conversionRate = utmData.conversion_rate || 0;

        return {
            campaignCost: parseFloat(reportData.campaign_cost) || 0,
            totalContacts: parseInt(reportData.total_contacts) || 0,
            clicks: parseInt(clicks.total_clicks) || 0,
            orders: totalOrders,
            revenue: revenue,
            conversionRate: conversionRate,
            smsSent: parseInt(total_sms_count) || 0,
            smsNotDelivered: parseInt(smsNotDelivered) || 0,
            smsDelivered: parseInt(smsDelivered) || 0,
        };
    };  
    
    const stats = getStats();

    return (
        <Layout>
            {/* Snackbar for success/error messages - positioned at bottom left via CSS */}
            {showSnackbar && (
                <Snackbar 
                    onDismiss={handleDismissSnackbar}
                    className={`topsms-snackbar ${snackbarStatus === 'error' ? 'topsms-snackbar-error' : snackbarStatus === 'info' ? 'topsms-snackbar-info' : ''}`}
                >
                    {snackbarMessage}
                </Snackbar>
            )}

            <div className='px-6 py-4'>
                <div className='mb-6'>
                    <h2 className='text-2xl font-bold mb-1'>
                        {__('Campaign Report', 'topsms')}
                    </h2>
                    {loading ? (
                        <div className="animate-pulse h-4 bg-gray-300 rounded w-32 mt-2"></div>
                    ) : (
                        <p className="text-gray-600">
                            {reportData?.campaign_name || ''}
                        </p>
                    )}
                </div>
            </div>

            <div className='page-details px-6'>
                <div className="flex flex-wrap -mx-4">
                    {/* Left section - Stats */}
                    <div className="flex-1 px-4 mb-6">
                        <div className="grid grid-cols-2 gap-4">
                            <StatCard 
                                label={__('Campaign Cost', 'topsms')}
                                value={stats ? `$${stats.campaignCost.toFixed(2)} AUD` : ''}
                                icon={CampaignCostLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('Total Contacts', 'topsms')}
                                value={stats ? stats.totalContacts.toLocaleString() : ''}
                                icon={TotalContactsLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('Clicks', 'topsms')}
                                value={stats ? stats.clicks.toLocaleString() : ''}
                                icon={ClicksLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('Orders', 'topsms')}
                                value={stats ? stats.orders.toLocaleString() : ''}
                                icon={OrdersLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('Revenue $', 'topsms')}
                                value={stats ? `$${stats.revenue.toLocaleString()} AUD` : ''}
                                icon={RevenueLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('Conversion Rate %', 'topsms')}
                                value={stats ? `${stats.conversionRate}%` : ''}
                                icon={ConversionRateLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('SMS Sent', 'topsms')}
                                value={stats ? stats.smsSent.toLocaleString() : ''}
                                icon={SmsSentLogo}
                                loading={loading}
                            />
                            <StatCard 
                                label={__('SMS Not Delivered', 'topsms')}
                                value={stats ? stats.smsNotDelivered.toLocaleString() : ''}
                                icon={SmsNotDeliveredLogo}
                                loading={loading}
                            />
                        </div>

                        <div className="col-span-2 mt-4">
                            <StatCard 
                                label={__('SMS Delivered', 'topsms')}
                                value={stats ? stats.smsDelivered.toLocaleString() : ''}
                                icon={SmsDeliveredLogo}
                                loading={loading}
                            />
                        </div>
                    </div>

                    {/* Right section - Preview */}
                    <div className="bg-gray-100 rounded-[20px] flex justify-center p-4 mb-6 xl:min-w-[45%] flex justify-center h-fit">
                        <div className="p-4 w-full flex justify-center items-start">
                            <SmsPreview 
                                sender={reportData?.sender || ''}
                                smsMessage={reportData?.message || ''}
                            />
                        </div>
                    </div>
                </div>
            </div>
        </Layout>
    );
};

export default Report;